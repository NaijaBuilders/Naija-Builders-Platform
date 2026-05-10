<?php

namespace App\Services\Kyc\Data;

class ProviderResponseNormalizer
{
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
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item): string => strtoupper(trim((string) $item)),
            $value
        )));
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
