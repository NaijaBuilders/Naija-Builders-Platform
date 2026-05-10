<?php

namespace App\Http\Requests\Mobile\SupplierOnboarding;

use Illuminate\Foundation\Http\FormRequest;

class AdminReviewActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (string) ($this->user()?->role ?? '') === 'admin';
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
