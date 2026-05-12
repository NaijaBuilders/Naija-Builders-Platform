<?php

namespace App\Services\Kyc\Contracts;

use App\Services\Kyc\Data\VerificationResult;

interface ProgressiveKycProvider
{
    /**
     * Tier 1 buyer onboarding: passive fraud/payment checks.
     *
     * @param  array<string, mixed>  $context
     */
    public function passiveFraudCheck(array $context = []): VerificationResult;

    /**
     * Shared email/company-email verification for supplier and buyer onboarding.
     *
     * @param  array<string, mixed>  $context
     */
    public function verifyEmail(array $context = []): VerificationResult;

    /**
     * Tier 2 buyer onboarding: ID document, extracted name, selfie/face match, and liveness.
     *
     * @param  array<string, mixed>  $context
     */
    public function verifyDocumentWithFace(array $context = []): VerificationResult;

    /**
     * Tier 3 buyer onboarding: billing/bank name match, with manual review on uncertainty.
     *
     * @param  array<string, mixed>  $context
     */
    public function verifyBillingName(array $context = []): VerificationResult;
}
