<?php

namespace App\Services\Kyc\Contracts;

use App\Models\SupplierApplication;
use App\Services\Kyc\Data\VerificationResult;

interface KycProvider
{
    public function name(): string;

    /**
     * @param  array<string, mixed>  $context
     */
    public function verifyBusiness(SupplierApplication $application, array $context = []): VerificationResult;

    /**
     * @param  array<string, mixed>  $context
     */
    public function verifyIdentity(SupplierApplication $application, array $context = []): VerificationResult;

    /**
     * @param  array<string, mixed>  $context
     */
    public function verifyBank(SupplierApplication $application, array $context = []): VerificationResult;

    /**
     * @param  array<string, mixed>  $context
     */
    public function screen(SupplierApplication $application, array $context = []): VerificationResult;
}
