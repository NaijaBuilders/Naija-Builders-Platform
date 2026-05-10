<?php

namespace App\Services\Kyc\Data;

class VerificationResult
{
    public const STATUS_PASSED = 'PASSED';

    public const STATUS_FAILED = 'FAILED';

    public const STATUS_MANUAL_REVIEW = 'MANUAL_REVIEW';

    /**
     * @param  array<int, string>  $reasonCodes
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly string $checkType,
        public readonly string $status,
        public readonly string $provider,
        public readonly array $reasonCodes = [],
        public readonly array $data = [],
    ) {}

    public static function passed(string $checkType, string $provider, array $data = []): self
    {
        return new self($checkType, self::STATUS_PASSED, $provider, [], $data);
    }

    public static function failed(string $checkType, string $provider, array $reasonCodes, array $data = []): self
    {
        return new self($checkType, self::STATUS_FAILED, $provider, $reasonCodes, $data);
    }

    public static function manualReview(string $checkType, string $provider, array $reasonCodes, array $data = []): self
    {
        return new self($checkType, self::STATUS_MANUAL_REVIEW, $provider, $reasonCodes, $data);
    }

    public function isPassed(): bool
    {
        return $this->status === self::STATUS_PASSED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isManualReview(): bool
    {
        return $this->status === self::STATUS_MANUAL_REVIEW;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'check_type' => $this->checkType,
            'status' => $this->status,
            'provider' => $this->provider,
            'reason_codes' => $this->reasonCodes,
            'data' => $this->data,
        ];
    }
}
