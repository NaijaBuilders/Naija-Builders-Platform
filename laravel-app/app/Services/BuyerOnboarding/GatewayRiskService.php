<?php

namespace App\Services\BuyerOnboarding;

class GatewayRiskService
{
    public function __construct(private readonly BuyerOnboardingSettings $settings) {}

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function evaluate(int $tier, array $metadata): array
    {
        $riskLevel = $this->riskLevel((string) ($metadata['gateway_risk_level'] ?? $metadata['risk_level'] ?? 'low'));
        $provider = strtolower((string) ($metadata['provider'] ?? $metadata['payment_provider'] ?? ''));
        $cardCountry = $this->country($metadata['card_country'] ?? null);
        $billingCountry = $this->country($metadata['billing_country'] ?? null);
        $deliveryCountry = $this->country($metadata['delivery_country'] ?? 'NG');
        $expectedCrossBorder = $riskLevel === 'medium'
            && $provider === 'stripe'
            && $deliveryCountry === 'NG'
            && $this->isDiasporaCountry($cardCountry ?: $billingCountry);

        if ($riskLevel === 'high') {
            return [
                'risk_level' => 'high',
                'action' => 'block',
                'trigger_level' => 'hard',
                'reason_codes' => ['GATEWAY_HIGH_RISK'],
                'expected_cross_border' => $expectedCrossBorder,
            ];
        }

        if ($riskLevel === 'medium') {
            $action = match ($tier) {
                3 => 'hold',
                default => 'proceed',
            };

            return [
                'risk_level' => 'medium',
                'action' => $action,
                'trigger_level' => $tier === 1 ? 'passive' : 'medium',
                'reason_codes' => [$expectedCrossBorder ? 'EXPECTED_CROSS_BORDER_MEDIUM_RISK' : 'GATEWAY_MEDIUM_RISK'],
                'expected_cross_border' => $expectedCrossBorder,
            ];
        }

        return [
            'risk_level' => 'low',
            'action' => 'proceed',
            'trigger_level' => 'none',
            'reason_codes' => [],
            'expected_cross_border' => false,
        ];
    }

    private function riskLevel(string $riskLevel): string
    {
        $riskLevel = strtolower(trim($riskLevel));

        return in_array($riskLevel, ['low', 'medium', 'high'], true) ? $riskLevel : 'low';
    }

    private function isDiasporaCountry(?string $country): bool
    {
        if ($country === null || $country === '') {
            return false;
        }

        return in_array($country, ['GB', 'US'], true)
            || in_array($country, $this->settings->euCountries(), true);
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
