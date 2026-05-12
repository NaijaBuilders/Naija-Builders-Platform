<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\EmailVerificationRequest;
use App\Services\Kyc\Contracts\ProgressiveKycProvider;
use App\Services\Kyc\Data\VerificationResult;

class EmailVerificationController extends Controller
{
    public function __construct(private readonly ProgressiveKycProvider $provider) {}

    public function __invoke(EmailVerificationRequest $request)
    {
        $validated = $request->validated();
        $result = $this->provider->verifyEmail([
            'email' => $validated['email'],
            'purpose' => $validated['purpose'] ?? 'onboarding',
        ]);

        return response()->json([
            'message' => $this->message($result),
            'verification' => [
                'status' => $this->publicStatus($result),
                'provider' => $result->provider,
            ],
        ]);
    }

    private function publicStatus(VerificationResult $result): string
    {
        return match ($result->status) {
            VerificationResult::STATUS_PASSED => 'VERIFIED',
            VerificationResult::STATUS_MANUAL_REVIEW => 'REVIEWING',
            default => 'NOT_VERIFIED',
        };
    }

    private function message(VerificationResult $result): string
    {
        return match ($result->status) {
            VerificationResult::STATUS_PASSED => 'Email verification completed.',
            VerificationResult::STATUS_MANUAL_REVIEW => "We're verifying this email.",
            default => 'We could not verify this email at this time.',
        };
    }
}
