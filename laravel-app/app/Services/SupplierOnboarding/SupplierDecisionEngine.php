<?php

namespace App\Services\SupplierOnboarding;

use App\Models\SupplierApplication;
use App\Services\Kyc\Data\DecisionResult;
use App\Services\Kyc\Data\VerificationResult;
use Illuminate\Support\Collection;

class SupplierDecisionEngine
{
    private const HARD_REJECT_CODES = [
        'CAC_INVALID_OR_UNREGISTERED',
        'IDENTITY_NUMBER_MISSING_OR_INVALID',
        'ID_DOCUMENT_TAMPERED',
        'FACE_MATCH_BELOW_THRESHOLD',
        'LIVENESS_FAILED',
        'BANK_ACCOUNT_UNVERIFIABLE',
        'BANK_NAME_MISMATCH_SIGNIFICANT',
        'DUPLICATE_REJECTED_BVN',
        'AML_PEP_FLAG',
    ];

    /**
     * @param  Collection<int, VerificationResult>  $checks
     */
    public function decide(Collection $checks): DecisionResult
    {
        $triggeredChecks = [];
        $reasonCodes = [];

        foreach ($checks as $check) {
            if (! $check->isPassed()) {
                $triggeredChecks[] = $check->checkType;
            }

            foreach ($check->reasonCodes as $reasonCode) {
                $reasonCodes[] = $reasonCode;
            }
        }

        $triggeredChecks = array_values(array_unique($triggeredChecks));
        $reasonCodes = array_values(array_unique($reasonCodes));

        foreach ($reasonCodes as $reasonCode) {
            if (in_array($reasonCode, self::HARD_REJECT_CODES, true)) {
                return new DecisionResult(
                    DecisionResult::AUTO_REJECTED,
                    SupplierApplication::STATUS_REJECTED,
                    $triggeredChecks,
                    $reasonCodes
                );
            }
        }

        $hasManualReview = $checks->contains(fn (VerificationResult $check): bool => $check->isManualReview());

        if ($hasManualReview || count($reasonCodes) > 0) {
            return new DecisionResult(
                DecisionResult::MANUAL_REVIEW,
                SupplierApplication::STATUS_MANUAL_REVIEW,
                $triggeredChecks,
                $reasonCodes
            );
        }

        return new DecisionResult(
            DecisionResult::AUTO_APPROVED,
            SupplierApplication::STATUS_APPROVED,
            [],
            []
        );
    }
}
