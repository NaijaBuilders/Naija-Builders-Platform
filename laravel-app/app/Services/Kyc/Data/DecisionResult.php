<?php

namespace App\Services\Kyc\Data;

class DecisionResult
{
    public const AUTO_APPROVED = 'AUTO_APPROVED';

    public const AUTO_REJECTED = 'AUTO_REJECTED';

    public const MANUAL_REVIEW = 'MANUAL_REVIEW';

    /**
     * @param  array<int, string>  $triggeredChecks
     * @param  array<int, string>  $reasonCodes
     */
    public function __construct(
        public readonly string $decision,
        public readonly string $newStatus,
        public readonly array $triggeredChecks,
        public readonly array $reasonCodes,
    ) {}
}
