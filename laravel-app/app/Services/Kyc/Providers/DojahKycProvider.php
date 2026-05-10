<?php

namespace App\Services\Kyc\Providers;

use App\Models\SupplierApplication;
use App\Services\Kyc\Contracts\KycProvider;
use App\Services\Kyc\Data\ProviderResponseNormalizer;
use App\Services\Kyc\Data\VerificationResult;

class DojahKycProvider implements KycProvider
{
    public function __construct(
        private readonly ProviderResponseNormalizer $normalizer,
        private readonly FakeKycProvider $sandbox,
    ) {}

    public function name(): string
    {
        return 'dojah';
    }

    public function verifyBusiness(SupplierApplication $application, array $context = []): VerificationResult
    {
        return $this->normalizeOrSandbox('cac_lookup', $application, $context, 'verifyBusiness');
    }

    public function verifyIdentity(SupplierApplication $application, array $context = []): VerificationResult
    {
        return $this->normalizeOrSandbox('identity', $application, $context, 'verifyIdentity');
    }

    public function verifyBank(SupplierApplication $application, array $context = []): VerificationResult
    {
        return $this->normalizeOrSandbox('bank', $application, $context, 'verifyBank');
    }

    public function screen(SupplierApplication $application, array $context = []): VerificationResult
    {
        return $this->normalizeOrSandbox('aml_pep', $application, $context, 'screen');
    }

    private function normalizeOrSandbox(string $checkType, SupplierApplication $application, array $context, string $method): VerificationResult
    {
        if (is_array($context['provider_response'] ?? null)) {
            return $this->normalizer->fromDojahLike($checkType, $context['provider_response']);
        }

        $result = $this->sandbox->{$method}($application, $context);

        return new VerificationResult($result->checkType, $result->status, $this->name(), $result->reasonCodes, $result->data);
    }
}
