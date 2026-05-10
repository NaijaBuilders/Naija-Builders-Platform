<?php

namespace App\Http\Requests\Mobile\SupplierOnboarding;

use Illuminate\Foundation\Http\FormRequest;

class BusinessDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (string) ($this->user()?->role ?? '') === 'supplier';
    }

    public function rules(): array
    {
        return [
            'cac_number' => ['required', 'string', 'max:40', 'regex:/^(RC|BN|IT)?[A-Za-z0-9\\-\\/]{4,30}$/'],
            'business_name' => ['required', 'string', 'min:2', 'max:191'],
            'business_type' => ['required', 'string', 'max:80'],
            'business_address' => ['required', 'string', 'min:5', 'max:255'],
            'state' => ['required', 'string', 'max:80'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ];
    }
}
