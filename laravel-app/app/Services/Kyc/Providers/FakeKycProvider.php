<?php

namespace App\Services\Kyc\Providers;

use App\Models\SupplierApplication;
use App\Services\Kyc\Contracts\KycProvider;
use App\Services\Kyc\Data\VerificationResult;
use Carbon\CarbonImmutable;

class FakeKycProvider implements KycProvider
{
    public function name(): string
    {
        return 'fake';
    }

    public function verifyBusiness(SupplierApplication $application, array $context = []): VerificationResult
    {
        $cacNumber = strtoupper((string) ($application->cac_number ?? ''));
        $address = strtoupper((string) ($application->business_address ?? ''));

        if ($cacNumber === '' || str_contains($cacNumber, 'INVALID')) {
            return VerificationResult::failed('cac_lookup', $this->name(), ['CAC_INVALID_OR_UNREGISTERED']);
        }

        if (str_contains($cacNumber, 'NEW')) {
            return VerificationResult::manualReview('cac_lookup', $this->name(), ['CAC_RECENT_REGISTRATION'], [
                'registered_at' => CarbonImmutable::now()->subMonths(2)->toDateString(),
            ]);
        }

        if (str_contains($address, 'UNVERIFIABLE') || str_contains($address, 'WEAK')) {
            return VerificationResult::manualReview('cac_lookup', $this->name(), ['ADDRESS_WEAK_OR_UNVERIFIABLE']);
        }

        return VerificationResult::passed('cac_lookup', $this->name(), [
            'registered_at' => CarbonImmutable::now()->subYears(2)->toDateString(),
        ]);
    }

    public function verifyIdentity(SupplierApplication $application, array $context = []): VerificationResult
    {
        $bvn = preg_replace('/\D+/', '', (string) ($context['bvn'] ?? '')) ?: '';
        $nin = preg_replace('/\D+/', '', (string) ($context['nin'] ?? '')) ?: '';
        $documentReference = strtoupper((string) ($context['id_document_reference'] ?? ''));
        $faceReference = strtoupper((string) ($context['face_match_reference'] ?? ''));
        $livenessReference = strtoupper((string) ($context['liveness_reference'] ?? ''));

        if ($bvn === '' && $nin === '' && ! $application->bvn_hash && ! $application->nin_hash) {
            return VerificationResult::failed('identity', $this->name(), ['IDENTITY_NUMBER_MISSING_OR_INVALID']);
        }

        if (str_starts_with($bvn, '000') || str_starts_with($nin, '000')) {
            return VerificationResult::failed('identity', $this->name(), ['IDENTITY_NUMBER_MISSING_OR_INVALID']);
        }

        if (str_contains($documentReference, 'TAMPERED') || str_contains($documentReference, 'FAKE')) {
            return VerificationResult::failed('identity', $this->name(), ['ID_DOCUMENT_TAMPERED']);
        }

        if (str_contains($livenessReference, 'FAIL')) {
            return VerificationResult::failed('identity', $this->name(), ['LIVENESS_FAILED'], [
                'liveness_passed' => false,
            ]);
        }

        if (str_contains($faceReference, 'LOW')) {
            return VerificationResult::failed('identity', $this->name(), ['FACE_MATCH_BELOW_THRESHOLD'], [
                'face_match_score' => 55,
                'liveness_passed' => true,
            ]);
        }

        if (str_contains($documentReference, 'LOW_QUALITY')) {
            return VerificationResult::manualReview('identity', $this->name(), ['ID_DOCUMENT_LOW_QUALITY'], [
                'face_match_score' => 82,
                'liveness_passed' => true,
            ]);
        }

        return VerificationResult::passed('identity', $this->name(), [
            'face_match_score' => 94,
            'liveness_passed' => true,
        ]);
    }

    public function verifyBank(SupplierApplication $application, array $context = []): VerificationResult
    {
        $accountNumber = preg_replace('/\D+/', '', (string) ($context['account_number'] ?? '')) ?: '';

        if ($accountNumber === '' || str_starts_with($accountNumber, '000')) {
            return VerificationResult::failed('bank', $this->name(), ['BANK_ACCOUNT_UNVERIFIABLE']);
        }

        if (str_starts_with($accountNumber, '999')) {
            return VerificationResult::failed('bank', $this->name(), ['BANK_NAME_MISMATCH_SIGNIFICANT'], [
                'name_match_score' => 35,
            ]);
        }

        if (str_starts_with($accountNumber, '888')) {
            return VerificationResult::manualReview('bank', $this->name(), ['BANK_NAME_MISMATCH_MINOR'], [
                'name_match_score' => 74,
            ]);
        }

        return VerificationResult::passed('bank', $this->name(), [
            'account_name' => $application->business_name ?: $application->account_name,
            'name_match_score' => 93,
        ]);
    }

    public function screen(SupplierApplication $application, array $context = []): VerificationResult
    {
        $screenedName = strtoupper(trim((string) ($application->business_name ?? '')));

        if (str_contains($screenedName, 'AML') || str_contains($screenedName, 'PEP') || str_contains($screenedName, 'SANCTION')) {
            return VerificationResult::failed('aml_pep', $this->name(), ['AML_PEP_FLAG']);
        }

        return VerificationResult::passed('aml_pep', $this->name());
    }
}
