<?php

namespace App\Services\BuyerOnboarding;

use App\Models\BuyerFraudEvent;
use App\Models\User;
use App\Support\Security\SensitiveData;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FraudMonitoringService
{
    public function __construct(private readonly BuyerOnboardingSettings $settings) {}

    /**
     * @param  array<string, mixed>  $paymentMetadata
     * @param  array<string, mixed>  $gatewayDecision
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function evaluate(User $buyer, float $amountNgn, int $tier, Request $request, array $paymentMetadata, array $gatewayDecision, array $context = []): array
    {
        $score = 0;
        $signals = [];
        $reasonCodes = [];
        $forcedTriggerLevel = null;

        $deviceFingerprint = (string) ($request->header('X-Device-Fingerprint') ?: $request->input('device_fingerprint', ''));
        $deviceHash = SensitiveData::fingerprint($deviceFingerprint);
        if ($deviceHash) {
            $signals[] = $this->signal('device_fingerprint', 0, $deviceHash, SensitiveData::maskToken($deviceFingerprint), ['source' => 'request']);

            $hasDeviceChange = BuyerFraudEvent::query()
                ->where('user_id', $buyer->id)
                ->where('signal_type', 'device_fingerprint')
                ->whereNotNull('signal_hash')
                ->where('signal_hash', '<>', $deviceHash)
                ->exists();

            if ($hasDeviceChange) {
                $weight = $this->settings->fraudWeight('device_change');
                $score += $weight;
                $signals[] = $this->signal('device_change', $weight, null, null, ['source' => 'request']);
                $reasonCodes[] = 'DEVICE_CHANGE';
            }
        }

        $ip = (string) $request->ip();
        $ipHash = SensitiveData::fingerprint($ip);
        if ($ipHash) {
            $signals[] = $this->signal('ip_address', 0, $ipHash, SensitiveData::maskIp($ip), ['source' => 'request']);

            $hasIpChange = BuyerFraudEvent::query()
                ->where('user_id', $buyer->id)
                ->where('signal_type', 'ip_address')
                ->whereNotNull('signal_hash')
                ->where('signal_hash', '<>', $ipHash)
                ->exists();

            if ($hasIpChange) {
                $weight = $this->settings->fraudWeight('ip_inconsistency');
                $score += $weight;
                $signals[] = $this->signal('ip_inconsistency', $weight, null, null, ['source' => 'request']);
                $reasonCodes[] = 'IP_INCONSISTENCY';
            }
        }

        $recentOrderCount = DB::table('orders')
            ->where('buyer_id', $buyer->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($recentOrderCount >= $this->settings->orderVelocityReviewCount()) {
            $weight = $this->settings->fraudWeight('order_velocity');
            $score += $weight;
            $signals[] = $this->signal('order_velocity', $weight, null, null, [
                'orders_last_24_hours' => $recentOrderCount,
            ]);
            $reasonCodes[] = 'ORDER_VELOCITY';
        }

        $dailyValue = (float) DB::table('orders')
            ->where('buyer_id', $buyer->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('total_amount');
        $dailyValue += $amountNgn;

        if ($dailyValue > $this->settings->dailyCumulativeReviewNgn()) {
            $weight = $this->settings->fraudWeight('daily_cumulative_value');
            $score += $weight;
            $signals[] = $this->signal('daily_cumulative_value', $weight, null, null, [
                'daily_value_ngn' => round($dailyValue, 2),
            ]);
            $reasonCodes[] = 'DAILY_CUMULATIVE_VALUE';
        }

        $previousHighest = (float) DB::table('orders')
            ->where('buyer_id', $buyer->id)
            ->max('total_amount');

        if ($previousHighest > 0 && $amountNgn > ($previousHighest * $this->settings->valueSpikeMultiplier())) {
            $weight = $this->settings->fraudWeight('order_value_spike');
            $score += $weight;
            $signals[] = $this->signal('order_value_spike', $weight, null, null, [
                'previous_highest_ngn' => round($previousHighest, 2),
                'current_value_ngn' => round($amountNgn, 2),
            ]);
            $reasonCodes[] = 'ORDER_VALUE_SPIKE';
        }

        $createdAt = $buyer->created_at ? CarbonImmutable::parse($buyer->created_at) : null;
        if ($createdAt && $createdAt->diffInHours(now()) <= $this->settings->rapidAccountAgeHours()) {
            $weight = $this->settings->fraudWeight('rapid_account_creation');
            $score += $weight;
            $signals[] = $this->signal('rapid_account_creation', $weight, null, null, [
                'account_age_hours' => $createdAt->diffInHours(now()),
            ]);
            $reasonCodes[] = 'RAPID_ACCOUNT_CREATION';
        }

        $gatewayRisk = (string) ($gatewayDecision['risk_level'] ?? 'low');
        if ($gatewayRisk === 'medium') {
            $weight = $this->settings->fraudWeight('gateway_medium');
            $score += $weight;
            $signals[] = $this->signal('gateway_medium_risk', $weight, null, null, [
                'provider' => $paymentMetadata['provider'] ?? $paymentMetadata['payment_provider'] ?? null,
                'expected_cross_border' => (bool) ($gatewayDecision['expected_cross_border'] ?? false),
            ]);
            $reasonCodes = array_merge($reasonCodes, (array) ($gatewayDecision['reason_codes'] ?? ['GATEWAY_MEDIUM_RISK']));
        }

        if ($gatewayRisk === 'high') {
            $weight = $this->settings->fraudWeight('gateway_high');
            $score += $weight;
            $signals[] = $this->signal('gateway_high_risk', $weight, null, null, [
                'provider' => $paymentMetadata['provider'] ?? $paymentMetadata['payment_provider'] ?? null,
            ]);
            $reasonCodes = array_merge($reasonCodes, (array) ($gatewayDecision['reason_codes'] ?? ['GATEWAY_HIGH_RISK']));
            $forcedTriggerLevel = 'hard';
        }

        if (($context['billing_name_match'] ?? null) === false) {
            $weight = $this->settings->fraudWeight('billing_name_mismatch');
            $score += $weight;
            $signals[] = $this->signal('billing_name_mismatch', $weight, null, null, ['source' => 'tier_3_name_match']);
            $reasonCodes[] = 'BILLING_NAME_MISMATCH';
            $forcedTriggerLevel = $this->strongerLevel($forcedTriggerLevel, 'medium');
        }

        $overrideScore = $this->numeric($paymentMetadata['fraud_score'] ?? $paymentMetadata['gateway_fraud_score'] ?? null);
        if ($overrideScore !== null && $overrideScore > 0) {
            $score += $overrideScore;
            $signals[] = $this->signal('gateway_fraud_score', $overrideScore, null, null, ['source' => 'gateway']);
            $reasonCodes[] = 'GATEWAY_FRAUD_SCORE';
        }

        if ((bool) ($context['is_first_transaction'] ?? false)) {
            $signals[] = $this->signal('first_order_monitoring', 0, null, null, ['source' => 'first_transaction_rules']);
            $reasonCodes[] = 'FIRST_ORDER_MONITORING';
        }

        $triggerLevel = $this->triggerLevel($score, $forcedTriggerLevel);

        return [
            'score' => $score,
            'trigger_level' => $triggerLevel,
            'reason_codes' => array_values(array_unique(array_filter($reasonCodes))),
            'signals' => $signals,
        ];
    }

    /**
     * @param  array<string, mixed>  $assessment
     */
    public function record(User $buyer, ?int $orderId, int $tier, array $assessment): void
    {
        foreach ((array) ($assessment['signals'] ?? []) as $signal) {
            if (! is_array($signal)) {
                continue;
            }

            BuyerFraudEvent::query()->create([
                'user_id' => $buyer->id,
                'order_id' => $orderId,
                'tier' => $tier,
                'signal_type' => (string) ($signal['signal_type'] ?? 'unknown'),
                'trigger_level' => (string) ($signal['trigger_level'] ?? $assessment['trigger_level'] ?? 'passive'),
                'score' => (int) ($signal['score'] ?? 0),
                'signal_hash' => $signal['signal_hash'] ?? null,
                'signal_display' => $signal['signal_display'] ?? null,
                'metadata' => $signal['metadata'] ?? [],
                'captured_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function signal(string $type, int $score, ?string $hash, ?string $display, array $metadata): array
    {
        return [
            'signal_type' => $type,
            'score' => max(0, $score),
            'signal_hash' => $hash,
            'signal_display' => $display,
            'metadata' => $metadata,
        ];
    }

    private function triggerLevel(int $score, ?string $forcedTriggerLevel): string
    {
        if ($forcedTriggerLevel === 'hard' || $score >= $this->settings->fraudHardTriggerScore()) {
            return 'hard';
        }

        if ($forcedTriggerLevel === 'medium' || $score >= $this->settings->fraudMediumTriggerScore()) {
            return 'medium';
        }

        return 'passive';
    }

    private function strongerLevel(?string $current, string $candidate): string
    {
        $rank = ['passive' => 1, 'medium' => 2, 'hard' => 3];

        return ($rank[$candidate] ?? 0) > ($rank[$current ?? 'passive'] ?? 0) ? $candidate : ($current ?? $candidate);
    }

    private function numeric(mixed $value): ?int
    {
        return is_numeric($value) ? max(0, (int) $value) : null;
    }
}
