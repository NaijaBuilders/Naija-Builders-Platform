<?php

namespace Tests\Unit;

use App\Services\Kyc\Data\ProviderResponseNormalizer;
use App\Services\Kyc\Data\VerificationResult;
use PHPUnit\Framework\TestCase;

class KycProviderNormalizerTest extends TestCase
{
    public function test_prembly_like_response_is_normalized(): void
    {
        $result = (new ProviderResponseNormalizer)->fromPremblyLike('identity', [
            'status' => false,
            'manual_review' => true,
            'reason_codes' => ['id_document_low_quality'],
            'face_match_score' => 78,
        ]);

        $this->assertSame('identity', $result->checkType);
        $this->assertSame('prembly', $result->provider);
        $this->assertSame(VerificationResult::STATUS_MANUAL_REVIEW, $result->status);
        $this->assertSame(['ID_DOCUMENT_LOW_QUALITY'], $result->reasonCodes);
        $this->assertSame(78, $result->data['face_match_score']);
    }

    public function test_dojah_like_response_is_normalized(): void
    {
        $result = (new ProviderResponseNormalizer)->fromDojahLike('bank', [
            'success' => false,
            'entity' => [
                'verified' => false,
                'reasons' => ['bank_account_unverifiable'],
                'name_match' => 20,
            ],
        ]);

        $this->assertSame('bank', $result->checkType);
        $this->assertSame('dojah', $result->provider);
        $this->assertSame(VerificationResult::STATUS_FAILED, $result->status);
        $this->assertSame(['BANK_ACCOUNT_UNVERIFIABLE'], $result->reasonCodes);
        $this->assertSame(20, $result->data['name_match_score']);
    }
}
