<?php

namespace App\Services\BuyerOnboarding;

class VerificationTierService
{
    public function __construct(private readonly BuyerOnboardingSettings $settings) {}

    public function tierForAmount(float $amountNgn): int
    {
        if ($amountNgn <= $this->settings->tier1UpperNgn()) {
            return 1;
        }

        if ($amountNgn <= $this->settings->tier2UpperNgn()) {
            return 2;
        }

        return 3;
    }
}
