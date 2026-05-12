<?php

namespace App\Services\Kyc\Data;

class ProviderResponseNormalizer
{
    /**
     * @param  array<string, mixed>  $response
     */
    public function fromPrembly(string $checkType, array $response): VerificationResult
    {
        $responseCode = $this->stringOrNull($response['response_code'] ?? $this->arrayGet($response, 'data.response_code'));
        $providerStatus = $this->premblyVerificationStatus($response);
        $data = $this->premblySafeData($response);

        if ($responseCode === '02' || $providerStatus === 'PENDING') {
            return VerificationResult::manualReview($checkType, 'prembly', ['PROVIDER_TEMPORARY_FAILURE'], $data);
        }

        if ($responseCode === '03') {
            return VerificationResult::manualReview($checkType, 'prembly', ['PROVIDER_CONFIGURATION_OR_BALANCE'], $data);
        }

        if ($responseCode === '07') {
            return VerificationResult::failed($checkType, 'prembly', ['AML_PEP_FLAG'], $data);
        }

        $bankMismatch = $this->premblyBankMismatch($response);
        if (in_array($checkType, ['bank', 'buyer_tier_3_billing_name'], true) && $bankMismatch !== null) {
            [$status, $reasonCode] = $bankMismatch;

            return $status === VerificationResult::STATUS_FAILED
                ? VerificationResult::failed($checkType, 'prembly', [$reasonCode], $data)
                : VerificationResult::manualReview($checkType, 'prembly', [$reasonCode], $data);
        }

        if (in_array($checkType, ['identity', 'buyer_tier_2_identity'], true) && $this->premblyFaceFailed($response)) {
            return VerificationResult::failed($checkType, 'prembly', ['FACE_MATCH_BELOW_THRESHOLD'], $data);
        }

        if ($checkType === 'email' && $responseCode === '00' && $this->premblyEmailSearchHasMatches($response) === false) {
            return VerificationResult::failed($checkType, 'prembly', ['EMAIL_UNVERIFIABLE'], $data);
        }

        if ($this->premblyPassed($response, $responseCode, $providerStatus)) {
            return VerificationResult::passed($checkType, 'prembly', $data);
        }

        if ($responseCode === '01' || $providerStatus === 'NOT-VERIFIED') {
            return VerificationResult::failed($checkType, 'prembly', [$this->defaultFailureReason($checkType)], $data);
        }

        $reasonCodes = $this->stringList($response['reason_codes'] ?? $response['errors'] ?? []);
        if ((bool) ($response['manual_review'] ?? false)) {
            return VerificationResult::manualReview($checkType, 'prembly', $reasonCodes ?: ['PROVIDER_MANUAL_REVIEW'], $data);
        }

        return VerificationResult::failed($checkType, 'prembly', $reasonCodes ?: ['PROVIDER_CHECK_FAILED'], $data);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function fromPremblyLike(string $checkType, array $response): VerificationResult
    {
        $passed = (bool) ($response['status'] ?? $response['verified'] ?? false);
        $needsReview = (bool) ($response['manual_review'] ?? false);
        $reasonCodes = $this->stringList($response['reason_codes'] ?? $response['errors'] ?? []);
        $data = $this->safeData([
            'reference' => $response['reference'] ?? null,
            'response_code' => $response['response_code'] ?? null,
            'registered_at' => $response['registered_at'] ?? null,
            'face_match_score' => $response['face_match_score'] ?? null,
            'liveness_passed' => $response['liveness_passed'] ?? null,
            'name_match_score' => $response['name_match_score'] ?? null,
        ]);

        if ($passed && ! $needsReview) {
            return VerificationResult::passed($checkType, 'prembly', $data);
        }

        if ($needsReview) {
            return VerificationResult::manualReview($checkType, 'prembly', $reasonCodes ?: ['PROVIDER_MANUAL_REVIEW'], $data);
        }

        return VerificationResult::failed($checkType, 'prembly', $reasonCodes ?: ['PROVIDER_CHECK_FAILED'], $data);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function premblyReference(array $response): ?string
    {
        return $this->stringOrNull(
            $this->arrayGet($response, 'verification.reference')
                ?? $this->arrayGet($response, 'data.reference')
                ?? $this->arrayGet($response, 'data.verification.reference')
                ?? $this->arrayGet($response, 'verification_id')
                ?? $response['reference'] ?? null
        );
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function fromDojahLike(string $checkType, array $response): VerificationResult
    {
        $entity = is_array($response['entity'] ?? null) ? $response['entity'] : [];
        $passed = (bool) ($response['success'] ?? $entity['verified'] ?? false);
        $needsReview = (bool) ($entity['review_required'] ?? $response['review_required'] ?? false);
        $reasonCodes = $this->stringList($entity['reasons'] ?? $response['reasons'] ?? []);
        $data = $this->safeData([
            'reference' => $response['reference_id'] ?? $entity['reference'] ?? null,
            'registered_at' => $entity['registered_at'] ?? null,
            'face_match_score' => $entity['face_match'] ?? null,
            'liveness_passed' => $entity['liveness'] ?? null,
            'name_match_score' => $entity['name_match'] ?? null,
        ]);

        if ($passed && ! $needsReview) {
            return VerificationResult::passed($checkType, 'dojah', $data);
        }

        if ($needsReview) {
            return VerificationResult::manualReview($checkType, 'dojah', $reasonCodes ?: ['PROVIDER_MANUAL_REVIEW'], $data);
        }

        return VerificationResult::failed($checkType, 'dojah', $reasonCodes ?: ['PROVIDER_CHECK_FAILED'], $data);
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $value): array
    {
        if (is_string($value) && $value !== '') {
            $value = strtoupper(trim($value));

            return preg_match('/^[A-Z0-9_:-]+$/', $value) === 1 ? [$value] : [];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item): string => strtoupper(trim((string) $item)),
            $value
        ), fn (string $item): bool => $item !== '' && preg_match('/^[A-Z0-9_:-]+$/', $item) === 1));
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    private function premblySafeData(array $response): array
    {
        $nameMatchScore = $this->score(
            $this->arrayGet($response, 'comparism_data.confidence')
                ?? $this->arrayGet($response, 'data.comparism_data.confidence')
                ?? $response['name_match_score'] ?? null
        );

        $faceMatchScore = $this->score(
            $this->arrayGet($response, 'data.face_data.confidence')
                ?? $this->arrayGet($response, 'face_data.confidence')
                ?? $response['face_match_score'] ?? null
        );

        return $this->safeData([
            'reference' => $this->premblyReference($response),
            'correlation_id' => $this->stringOrNull($response['correlation_id'] ?? null),
            'response_code' => $this->stringOrNull($response['response_code'] ?? $this->arrayGet($response, 'data.response_code')),
            'provider_status' => $this->premblyVerificationStatus($response),
            'registered_at' => $this->stringOrNull(
                $this->arrayGet($response, 'data.registrationDate')
                    ?? $this->arrayGet($response, 'data.registration_date')
                    ?? $this->arrayGet($response, 'cac_data.registration_date')
                    ?? $response['registered_at'] ?? null
            ),
            'face_match_score' => $faceMatchScore,
            'liveness_passed' => $this->boolOrNull(
                $this->arrayGet($response, 'data.liveness_passed')
                    ?? $response['liveness_passed'] ?? null
            ),
            'name_match_score' => $nameMatchScore,
            'account_name' => $this->stringOrNull(
                $this->arrayGet($response, 'account_data.account_name')
                    ?? $this->arrayGet($response, 'data.account_data.account_name')
                    ?? $response['account_name'] ?? null
            ),
            'company_match_count' => $this->premblyCompanyMatchCount($response),
        ]);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function premblyVerificationStatus(array $response): ?string
    {
        $status = $this->stringOrNull(
            $this->arrayGet($response, 'verification.status')
                ?? $this->arrayGet($response, 'data.verification_status')
                ?? $this->arrayGet($response, 'data.verification.status')
                ?? $response['verification_status'] ?? null
        );

        return $status ? strtoupper(str_replace('_', '-', $status)) : null;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function premblyPassed(array $response, ?string $responseCode, ?string $providerStatus): bool
    {
        if ($providerStatus === 'VERIFIED') {
            return true;
        }

        if (in_array($providerStatus, ['NOT-VERIFIED', 'PENDING'], true)) {
            return false;
        }

        $status = $response['status'] ?? null;

        if ($responseCode === '00') {
            return $status !== false;
        }

        return $status === true || $status === 'true' || $status === 1 || $status === '1';
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array{0: string, 1: string}|null
     */
    private function premblyBankMismatch(array $response): ?array
    {
        $comparisonStatus = $this->arrayGet($response, 'comparism_data.status')
            ?? $this->arrayGet($response, 'data.comparism_data.status');

        if ($comparisonStatus === null || $this->boolOrNull($comparisonStatus) === true) {
            return null;
        }

        $score = $this->score(
            $this->arrayGet($response, 'comparism_data.confidence')
                ?? $this->arrayGet($response, 'data.comparism_data.confidence')
        );

        if ($score !== null && $score >= 60) {
            return [VerificationResult::STATUS_MANUAL_REVIEW, 'BANK_NAME_MISMATCH_MINOR'];
        }

        return [VerificationResult::STATUS_FAILED, 'BANK_NAME_MISMATCH_SIGNIFICANT'];
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function premblyFaceFailed(array $response): bool
    {
        $faceStatus = $this->arrayGet($response, 'data.face_data.status')
            ?? $this->arrayGet($response, 'face_data.status');

        return $faceStatus !== null && $this->boolOrNull($faceStatus) === false;
    }

    private function defaultFailureReason(string $checkType): string
    {
        return match ($checkType) {
            'cac_lookup' => 'CAC_INVALID_OR_UNREGISTERED',
            'identity' => 'IDENTITY_NUMBER_MISSING_OR_INVALID',
            'buyer_tier_2_identity' => 'IDENTITY_NUMBER_MISSING_OR_INVALID',
            'bank' => 'BANK_ACCOUNT_UNVERIFIABLE',
            'buyer_tier_3_billing_name' => 'BANK_ACCOUNT_UNVERIFIABLE',
            'aml_pep' => 'AML_PEP_FLAG',
            'email' => 'EMAIL_UNVERIFIABLE',
            default => 'PROVIDER_CHECK_FAILED',
        };
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function premblyEmailSearchHasMatches(array $response): ?bool
    {
        $data = $response['data'] ?? null;

        if (! is_array($data) || ! array_is_list($data)) {
            return null;
        }

        return count($data) > 0;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function premblyCompanyMatchCount(array $response): ?int
    {
        $data = $response['data'] ?? null;

        if (! is_array($data) || ! array_is_list($data)) {
            return null;
        }

        return count($data);
    }

    private function score(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        if ($number <= 1) {
            $number *= 100;
        }

        return (int) max(0, min(100, round($number)));
    }

    private function boolOrNull(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));

            if (in_array($value, ['true', 'yes', 'verified', 'passed', '1'], true)) {
                return true;
            }

            if (in_array($value, ['false', 'no', 'not-verified', 'failed', '0'], true)) {
                return false;
            }
        }

        return null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (is_scalar($value)) {
            $value = trim((string) $value);

            return $value === '' ? null : $value;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function arrayGet(array $data, string $path): mixed
    {
        $current = $data;

        foreach (explode('.', $path) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function safeData(array $data): array
    {
        return array_filter($data, fn ($value): bool => $value !== null && $value !== '');
    }
}
