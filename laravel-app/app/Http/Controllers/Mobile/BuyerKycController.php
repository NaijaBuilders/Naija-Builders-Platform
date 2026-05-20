<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Services\BuyerOnboarding\BuyerKycService;
use Illuminate\Http\Request;

class BuyerKycController extends Controller
{
    public function __construct(private readonly BuyerKycService $kyc) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:40'],
            'verified_id_name' => ['nullable', 'string', 'max:150'],
            'id_document_reference' => ['nullable', 'string', 'max:120'],
            'face_match_reference' => ['nullable', 'string', 'max:120'],
            'liveness_reference' => ['nullable', 'string', 'max:120'],
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'selfie' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:8192'],
        ]);

        $verification = $this->kyc->submit($request->user(), $validated, $request);

        return response()->json([
            'message' => match ($verification->status) {
                'verified' => 'ID verified.',
                'reviewing' => 'Your ID is being reviewed.',
                default => 'We could not verify this ID.',
            },
            'verification' => [
                'id' => (int) $verification->id,
                'status' => (string) $verification->status,
                'provider' => (string) $verification->provider,
                'provider_reference' => (string) $verification->provider_reference,
                'verified_id_name' => (string) $verification->verified_id_name,
                'document_type' => (string) $verification->document_type,
                'submitted_at' => optional($verification->submitted_at)->toISOString(),
                'verified_at' => optional($verification->verified_at)->toISOString(),
            ],
        ], 201);
    }
}
