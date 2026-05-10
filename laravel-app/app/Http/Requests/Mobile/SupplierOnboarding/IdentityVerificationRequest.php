<?php

namespace App\Http\Requests\Mobile\SupplierOnboarding;

use Illuminate\Foundation\Http\FormRequest;

class IdentityVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (string) ($this->user()?->role ?? '') === 'supplier';
    }

    public function rules(): array
    {
        return [
            'bvn' => ['nullable', 'digits:11'],
            'nin' => ['nullable', 'digits:11'],
            'id_document_type' => ['nullable', 'string', 'max:40'],
            'selfie' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'id_document_reference' => ['nullable', 'string', 'max:80'],
            'face_match_reference' => ['nullable', 'string', 'max:80'],
            'liveness_reference' => ['nullable', 'string', 'max:80'],
        ];
    }
}
