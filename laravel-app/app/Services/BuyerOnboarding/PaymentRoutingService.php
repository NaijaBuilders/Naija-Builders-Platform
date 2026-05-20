<?php

namespace App\Services\BuyerOnboarding;

class PaymentRoutingService
{
    public function __construct(private readonly BuyerOnboardingSettings $settings) {}

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function route(float $amountNgn, array $metadata): array
    {
        $methodType = $this->methodType((string) ($metadata['payment_method_type'] ?? $metadata['method_type'] ?? 'card'));
        $cardCountry = $this->country($metadata['card_country'] ?? $metadata['billing_card_country'] ?? null);
        $billingCountry = $this->country($metadata['billing_country'] ?? null);
        $country = $cardCountry ?: $billingCountry;

        if ($methodType === 'bank_transfer' || $country === 'NG') {
            return $this->result('paystack', 'NGN', $amountNgn, $methodType, $cardCountry, $billingCountry);
        }

        $stripeCurrency = $this->stripeCurrencyForCountry($country);
        if ($methodType === 'card' && $stripeCurrency !== null) {
            return $this->result('stripe', $stripeCurrency, $amountNgn, $methodType, $cardCountry, $billingCountry);
        }

        return $this->result('paystack', 'NGN', $amountNgn, $methodType, $cardCountry, $billingCountry);
    }

    private function result(string $provider, string $currency, float $amountNgn, string $methodType, ?string $cardCountry, ?string $billingCountry): array
    {
        return [
            'provider' => $provider,
            'currency' => $currency,
            'amount' => round($this->settings->convertFromNgn($amountNgn, $currency), 2),
            'amount_ngn' => round($amountNgn, 2),
            'payment_method_type' => $methodType,
            'card_country' => $cardCountry,
            'billing_country' => $billingCountry,
        ];
    }

    private function stripeCurrencyForCountry(?string $country): ?string
    {
        if ($country === null || $country === '') {
            return null;
        }

        $configured = (array) config('buyer_onboarding.payment.stripe_currencies', []);
        if (isset($configured[$country])) {
            return strtoupper((string) $configured[$country]);
        }

        return in_array($country, $this->settings->euCountries(), true) ? 'EUR' : null;
    }

    private function methodType(string $methodType): string
    {
        $methodType = strtolower(trim($methodType));

        return match ($methodType) {
            'bank', 'bank-transfer', 'bank_transfer', 'transfer' => 'bank_transfer',
            default => 'card',
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
