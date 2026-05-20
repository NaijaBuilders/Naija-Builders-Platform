<?php

namespace App\Services\BuyerOnboarding;

use App\Models\BuyerIdentityVerification;
use App\Models\User;
use App\Services\Kyc\Contracts\ProgressiveKycProvider;
use App\Services\Kyc\Data\VerificationResult;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerKycService
{
    public function __construct(
        private readonly ProgressiveKycProvider $provider,
        private readonly BuyerOnboardingSettings $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(User $buyer, array $data, Request $request): BuyerIdentityVerification
    {
        $documentType = $this->documentType((string) ($data['document_type'] ?? $data['id_document_type'] ?? ''));
        $verification = new BuyerIdentityVerification([
            'user_id' => $buyer->id,
            'provider' => method_exists($this->provider, 'name') ? $this->provider->name() : 'provider',
            'document_type' => $documentType,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        $verification->save();

        if ($request->file('id_document') instanceof UploadedFile) {
            $verification->document_path = $request->file('id_document')->store('buyer-kyc/'.$buyer->id.'/documents', 'local') ?: null;
        }

        if ($request->file('selfie') instanceof UploadedFile) {
            $verification->selfie_path = $request->file('selfie')->store('buyer-kyc/'.$buyer->id.'/selfies', 'local') ?: null;
        }

        $result = $this->provider->verifyDocumentWithFace([
            'id_document_type' => $documentType,
            'id_document_reference' => $data['id_document_reference'] ?? '',
            'face_match_reference' => $data['face_match_reference'] ?? '',
            'liveness_reference' => $data['liveness_reference'] ?? '',
            'doc_image' => $data['doc_image'] ?? null,
            'selfie_image' => $data['selfie_image'] ?? null,
            'doc_country' => 'NGA',
        ]);

        $verification->fill([
            'provider' => $result->provider,
            'provider_reference' => $this->providerReference($result),
            'verified_id_name' => $this->verifiedName($buyer, $data, $result),
            'status' => $this->status($result),
            'face_match_score' => $result->data['face_match_score'] ?? null,
            'liveness_passed' => $result->data['liveness_passed'] ?? null,
            'normalized_result' => $result->toArray(),
            'verified_at' => $result->isPassed() ? now() : null,
        ])->save();

        return $verification->refresh();
    }

    public function latestVerifiedFor(User $buyer): ?BuyerIdentityVerification
    {
        return BuyerIdentityVerification::query()
            ->where('user_id', $buyer->id)
            ->where('status', 'verified')
            ->latest('id')
            ->first();
    }

    private function documentType(string $documentType): string
    {
        $documentType = strtolower(trim(str_replace('-', '_', $documentType)));
        $documentType = match ($documentType) {
            'passport' => 'international_passport',
            'driver_license', 'drivers_license', 'driver_licence' => 'drivers_licence',
            'nin', 'national_id', 'national_id_card' => 'nin_slip',
            default => $documentType,
        };

        if (! in_array($documentType, $this->settings->documentTypes(), true)) {
            throw ValidationException::withMessages([
                'document_type' => 'Use a NIN slip, international passport, or driver licence.',
            ]);
        }

        return $documentType;
    }

    private function providerReference(VerificationResult $result): string
    {
        $reference = $result->data['reference'] ?? $result->data['provider_reference'] ?? null;

        return is_scalar($reference) && trim((string) $reference) !== ''
            ? substr(trim((string) $reference), 0, 191)
            : 'buyer-kyc-'.Str::uuid();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function verifiedName(User $buyer, array $data, VerificationResult $result): string
    {
        $name = $result->data['name'] ?? $result->data['full_name'] ?? $data['verified_id_name'] ?? $data['id_name'] ?? $buyer->full_name;

        return trim((string) $name) ?: (string) $buyer->full_name;
    }

    private function status(VerificationResult $result): string
    {
        if ($result->isPassed()) {
            return 'verified';
        }

        if ($result->isManualReview()) {
            return 'reviewing';
        }

        return 'rejected';
    }
}
