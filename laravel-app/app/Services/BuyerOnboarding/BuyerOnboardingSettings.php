<?php

namespace App\Services\BuyerOnboarding;

class BuyerOnboardingSettings
{
    public function tier1UpperNgn(): float
    {
        return $this->float('buyer_onboarding.tiers.tier_1_upper_ngn', 1000000);
    }

    public function tier2UpperNgn(): float
    {
        return $this->float('buyer_onboarding.tiers.tier_2_upper_ngn', 5000000);
    }

    public function firstOrderMaxNgn(): float
    {
        return $this->float('buyer_onboarding.first_transaction.max_order_ngn', 500000);
    }

    public function firstTransactionWindowHours(): int
    {
        return $this->integer('buyer_onboarding.first_transaction.velocity_window_hours', 24);
    }

    public function firstTransactionMaxOrders(): int
    {
        return $this->integer('buyer_onboarding.first_transaction.max_orders', 2);
    }

    public function fraudHardTriggerScore(): int
    {
        return $this->integer('buyer_onboarding.fraud.hard_trigger_score', 65);
    }

    public function fraudMediumTriggerScore(): int
    {
        return $this->integer('buyer_onboarding.fraud.medium_review_score', 40);
    }

    public function disputeWindowHours(): int
    {
        return $this->integer('buyer_onboarding.dispute_window_hours', 72);
    }

    public function deliveryMinimumPhotos(): int
    {
        return $this->integer('buyer_onboarding.delivery.minimum_photos', 2);
    }

    public function deliveryOtpTtlMinutes(): int
    {
        return $this->integer('buyer_onboarding.delivery.otp_ttl_minutes', 30);
    }

    public function otpTtlMinutes(): int
    {
        return $this->integer('buyer_onboarding.otp.ttl_minutes', 15);
    }

    public function moreInfoDeadlineHours(): int
    {
        return $this->integer('buyer_onboarding.manual_review.more_info_deadline_hours', 24);
    }

    public function valueSpikeMultiplier(): float
    {
        return $this->float('buyer_onboarding.fraud.value_spike_multiplier', 3);
    }

    public function rapidAccountAgeHours(): int
    {
        return $this->integer('buyer_onboarding.fraud.rapid_account_age_hours', 24);
    }

    public function dailyCumulativeReviewNgn(): float
    {
        return $this->float('buyer_onboarding.fraud.daily_cumulative_review_ngn', 5000000);
    }

    public function orderVelocityReviewCount(): int
    {
        return $this->integer('buyer_onboarding.fraud.order_velocity_review_count', 3);
    }

    public function fraudWeight(string $key): int
    {
        return $this->integer('buyer_onboarding.fraud.weights.'.$key, 0);
    }

    /**
     * @return array<int, string>
     */
    public function euCountries(): array
    {
        return array_map(
            fn (mixed $country): string => strtoupper((string) $country),
            (array) config('buyer_onboarding.payment.eu_countries', [])
        );
    }

    /**
     * @return array<int, string>
     */
    public function documentTypes(): array
    {
        return array_map('strval', (array) config('buyer_onboarding.kyc.document_types', []));
    }

    public function ngnPerUnit(string $currency): float
    {
        $currency = strtoupper($currency);

        if ($currency === 'NGN') {
            return 1.0;
        }

        if ($currency === 'GBP') {
            $rate = $this->float('buyer_onboarding.exchange_rates.gbp_to_ngn', 0);
            if ($rate > 0) {
                return $rate;
            }
        }

        $configured = config('currency.ngn_per_unit.'.$currency);

        return is_numeric($configured) ? (float) $configured : 0.0;
    }

    public function convertFromNgn(float $amountNgn, string $currency): float
    {
        $currency = strtoupper($currency);
        if ($currency === 'NGN') {
            return $amountNgn;
        }

        $rate = $this->ngnPerUnit($currency);

        return $rate > 0 ? $amountNgn / $rate : $amountNgn;
    }

    private function integer(string $key, int $fallback): int
    {
        $value = config($key, $fallback);

        return is_numeric($value) ? (int) $value : $fallback;
    }

    private function float(string $key, float $fallback): float
    {
        $value = config($key, $fallback);

        return is_numeric($value) ? (float) $value : $fallback;
    }
}
