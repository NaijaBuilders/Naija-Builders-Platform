<?php

namespace App\Services\BuyerOnboarding;

use App\Models\BuyerReviewAudit;
use App\Models\User;
use App\Services\Kyc\Contracts\ProgressiveKycProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BuyerOrderService
{
    public function __construct(
        private readonly BuyerOnboardingSettings $settings,
        private readonly VerificationTierService $tiers,
        private readonly PaymentRoutingService $payments,
        private readonly GatewayRiskService $gatewayRisk,
        private readonly FraudMonitoringService $fraud,
        private readonly BuyerKycService $kyc,
        private readonly ProgressiveKycProvider $provider,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function place(User $buyer, array $data, Request $request): array
    {
        $buyer = User::query()->findOrFail($buyer->id);
        [$amountNgn, $supplierId, $items] = $this->resolveOrder($data);
        $isFirstTransaction = $this->firstTransactionApplies($buyer);

        if ($isFirstTransaction) {
            $this->enforceFirstTransactionRules($buyer, $amountNgn);
        }

        $tier = $this->tiers->tierForAmount($amountNgn);
        $deliveryCountry = $this->country($data['delivery_country'] ?? 'NG') ?: 'NG';
        $paymentMetadata = (array) ($data['payment'] ?? []);
        $paymentMetadata = array_merge($paymentMetadata, [
            'payment_method_type' => $data['payment_method_type'] ?? $paymentMetadata['payment_method_type'] ?? 'card',
            'billing_country' => $data['billing_country'] ?? $paymentMetadata['billing_country'] ?? null,
            'card_country' => $data['card_country'] ?? $paymentMetadata['card_country'] ?? null,
        ]);
        $paymentRoute = $this->payments->route($amountNgn, $paymentMetadata);
        $gatewayDecision = $this->gatewayRisk->evaluate($tier, array_merge($paymentMetadata, $paymentRoute, [
            'gateway_risk_level' => $data['gateway_risk_level'] ?? $paymentMetadata['gateway_risk_level'] ?? 'low',
            'delivery_country' => $deliveryCountry,
        ]));
        $identity = $this->kyc->latestVerifiedFor($buyer);
        $kycRequired = $tier >= 2 && ! $identity;
        $billingNameMatch = null;
        if ($tier >= 3 && $identity) {
            $billingNameMatch = $this->namesMatch((string) ($data['billing_name'] ?? ''), (string) $identity->verified_id_name);
        }

        $passiveResult = $this->provider->passiveFraudCheck([
            'risk_reference' => $data['risk_reference'] ?? '',
            'buyer_id' => $buyer->id,
            'amount_ngn' => $amountNgn,
        ]);

        $assessment = $this->fraud->evaluate(
            $buyer,
            $amountNgn,
            $tier,
            $request,
            array_merge($paymentMetadata, $paymentRoute, [
                'gateway_risk_level' => $gatewayDecision['risk_level'] ?? 'low',
            ]),
            $gatewayDecision,
            [
                'billing_name_match' => $billingNameMatch,
                'is_first_transaction' => $isFirstTransaction,
            ]
        );

        $reasonCodes = array_values(array_unique(array_merge(
            (array) $assessment['reason_codes'],
            (array) $gatewayDecision['reason_codes'],
            $passiveResult->reasonCodes
        )));

        $effectiveTriggerLevel = $this->strongerLevel(
            (string) $assessment['trigger_level'],
            (string) ($gatewayDecision['trigger_level'] ?? 'none')
        );
        $assessment['trigger_level'] = $effectiveTriggerLevel;

        [$orderStatus, $verificationStatus, $reviewStatus] = $this->decision(
            $tier,
            $kycRequired,
            (string) $gatewayDecision['action'],
            $effectiveTriggerLevel
        );

        if ($kycRequired) {
            $reasonCodes[] = 'BUYER_ID_VERIFICATION_REQUIRED';
        }

        $orderId = DB::transaction(function () use (
            $buyer,
            $supplierId,
            $amountNgn,
            $orderStatus,
            $verificationStatus,
            $reviewStatus,
            $tier,
            $assessment,
            $reasonCodes,
            $paymentRoute,
            $paymentMetadata,
            $gatewayDecision,
            $isFirstTransaction,
            $data,
            $items,
            $deliveryCountry,
            $billingNameMatch
        ): int {
            $now = now();
            $orderId = (int) DB::table('orders')->insertGetId([
                'buyer_id' => $buyer->id,
                'supplier_id' => $supplierId,
                'total_amount' => $amountNgn,
                'order_status' => $orderStatus,
                'verification_tier' => $tier,
                'verification_status' => $verificationStatus,
                'review_status' => $reviewStatus,
                'fraud_score' => (int) $assessment['score'],
                'fraud_trigger_level' => $assessment['trigger_level'] === 'passive' ? null : $assessment['trigger_level'],
                'fraud_triggers' => json_encode($reasonCodes, JSON_THROW_ON_ERROR),
                'payment_provider' => $paymentRoute['provider'],
                'payment_method_type' => $paymentRoute['payment_method_type'],
                'payment_currency' => $paymentRoute['currency'],
                'payment_amount' => $paymentRoute['amount'],
                'gateway_risk_level' => $gatewayDecision['risk_level'],
                'gateway_risk_metadata' => json_encode([
                    'action' => $gatewayDecision['action'],
                    'expected_cross_border' => $gatewayDecision['expected_cross_border'] ?? false,
                    'card_country' => $paymentRoute['card_country'] ?? null,
                    'billing_country' => $paymentRoute['billing_country'] ?? null,
                    'billing_name_match' => $billingNameMatch,
                ], JSON_THROW_ON_ERROR),
                'is_first_transaction' => $isFirstTransaction,
                'monitoring_flag' => $isFirstTransaction || $assessment['trigger_level'] !== 'passive',
                'billing_name' => $data['billing_name'] ?? null,
                'billing_country' => $this->country($paymentMetadata['billing_country'] ?? null),
                'card_country' => $this->country($paymentMetadata['card_country'] ?? null),
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_country' => $deliveryCountry,
                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'recipient_relationship' => $data['recipient_relationship'] ?? null,
                'delivery_status' => 'pending',
                'dispute_status' => 'none',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('orders')->where('id', $orderId)->update([
                'reference' => 'NB-'.$orderId,
            ]);

            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (in_array($verificationStatus, ['manual_review', 'rejected', 'requires_kyc'], true)) {
                BuyerReviewAudit::query()->create([
                    'order_id' => $orderId,
                    'user_id' => $buyer->id,
                    'action' => 'AUTOMATED_DECISION',
                    'previous_status' => null,
                    'new_status' => $verificationStatus,
                    'trigger_level' => $assessment['trigger_level'],
                    'reason_codes' => $reasonCodes,
                    'notes' => 'Automated buyer onboarding decision.',
                ]);
            }

            return $orderId;
        });

        $this->fraud->record($buyer, $orderId, $tier, $assessment);

        return [
            'order_id' => $orderId,
            'reference' => 'NB-'.$orderId,
            'verification_tier' => $tier,
            'verification_status' => $verificationStatus,
            'review_status' => $reviewStatus,
            'fraud_score' => (int) $assessment['score'],
            'fraud_trigger_level' => $assessment['trigger_level'],
            'payment' => $paymentRoute,
            'gateway_risk' => $gatewayDecision,
            'next_action' => $verificationStatus === 'requires_kyc' ? 'submit_kyc' : null,
            'message' => $this->buyerMessage($verificationStatus),
        ];
    }

    public function markCompleted(int $orderId): void
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (! $order) {
            return;
        }

        DB::table('users')
            ->where('id', $order->buyer_id)
            ->whereNull('first_transaction_completed_at')
            ->update([
                'first_successful_order_id' => $orderId,
                'first_transaction_monitoring' => false,
                'first_transaction_completed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: float, 1: int, 2: array<int, array{material_id: int, quantity: int, unit_price: float}>}
     */
    private function resolveOrder(array $data): array
    {
        $itemsInput = (array) ($data['items'] ?? []);
        if ($itemsInput !== []) {
            $materialIds = collect($itemsInput)->pluck('material_id')->map(fn ($id): int => (int) $id)->filter()->values()->all();
            $materials = DB::table('materials')
                ->whereIn('id', $materialIds)
                ->where('status', 'active')
                ->get()
                ->keyBy('id');

            $supplierIds = $materials->pluck('supplier_id')->unique()->values();
            if ($materials->count() !== count($materialIds) || $supplierIds->count() !== 1) {
                throw ValidationException::withMessages([
                    'items' => 'Choose available items from one supplier per order.',
                ]);
            }

            $amount = 0.0;
            $items = [];
            foreach ($itemsInput as $item) {
                $materialId = (int) ($item['material_id'] ?? 0);
                $material = $materials->get($materialId);
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $unitPrice = (float) $material->price;
                $amount += $unitPrice * $quantity;
                $items[] = [
                    'material_id' => $materialId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];
            }

            return [$amount, (int) $supplierIds->first(), $items];
        }

        $amount = (float) ($data['amount_ngn'] ?? $data['total_amount'] ?? 0);
        $supplierId = (int) ($data['supplier_id'] ?? 0);
        if ($amount <= 0 || $supplierId <= 0) {
            throw ValidationException::withMessages([
                'amount_ngn' => 'Provide a valid order amount and supplier.',
            ]);
        }

        return [$amount, $supplierId, []];
    }

    private function enforceFirstTransactionRules(User $buyer, float $amountNgn): void
    {
        if ($amountNgn > $this->settings->firstOrderMaxNgn()) {
            throw ValidationException::withMessages([
                'amount_ngn' => 'Your first order cannot exceed the current first-order limit.',
            ]);
        }

        $windowStart = $buyer->created_at ?: now();
        $windowEndsAt = $windowStart->copy()->addHours($this->settings->firstTransactionWindowHours());
        if (now()->lessThanOrEqualTo($windowEndsAt)) {
            $ordersInWindow = DB::table('orders')
                ->where('buyer_id', $buyer->id)
                ->where('created_at', '>=', $windowStart)
                ->where('created_at', '<=', $windowEndsAt)
                ->count();

            if ($ordersInWindow >= $this->settings->firstTransactionMaxOrders()) {
                throw ValidationException::withMessages([
                    'orders' => 'Please wait before placing another order from this new account.',
                ]);
            }
        }
    }

    private function firstTransactionApplies(User $buyer): bool
    {
        return ! $buyer->first_transaction_completed_at;
    }

    /**
     * @return array{0: string, 1: string, 2: string|null}
     */
    private function decision(int $tier, bool $kycRequired, string $gatewayAction, string $fraudTriggerLevel): array
    {
        if ($gatewayAction === 'block') {
            return ['cancelled', 'rejected', 'blocked'];
        }

        if ($kycRequired) {
            return ['pending', 'requires_kyc', 'awaiting_kyc'];
        }

        if ($gatewayAction === 'hold') {
            return ['pending', 'manual_review', 'manual_review'];
        }

        if ($tier === 3 && in_array($fraudTriggerLevel, ['medium', 'hard'], true)) {
            return ['pending', 'manual_review', 'manual_review'];
        }

        if ($fraudTriggerLevel === 'hard') {
            return ['pending', 'manual_review', 'manual_review'];
        }

        if ($tier === 2 && $fraudTriggerLevel === 'medium') {
            return ['processing', 'approved', 'flagged'];
        }

        return ['processing', 'approved', null];
    }

    private function strongerLevel(string $current, string $candidate): string
    {
        $rank = ['none' => 0, 'passive' => 1, 'medium' => 2, 'hard' => 3];

        return ($rank[$candidate] ?? 0) > ($rank[$current] ?? 0) ? $candidate : $current;
    }

    private function namesMatch(string $billingName, string $verifiedName): bool
    {
        $billingName = $this->normalizeName($billingName);
        $verifiedName = $this->normalizeName($verifiedName);

        return $billingName !== '' && $verifiedName !== '' && $billingName === $verifiedName;
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/[^A-Z]/', '', strtoupper($name)) ?: '';
    }

    private function buyerMessage(string $verificationStatus): string
    {
        return match ($verificationStatus) {
            'requires_kyc' => 'We need a little more information before completing this order.',
            'manual_review' => 'Your order is being reviewed.',
            'rejected' => 'This payment could not be completed.',
            default => 'Payment can continue.',
        };
    }

    private function country(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $country = strtoupper(trim((string) $value));
        if ($country === 'UK') {
            return 'GB';
        }

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : null;
    }
}
