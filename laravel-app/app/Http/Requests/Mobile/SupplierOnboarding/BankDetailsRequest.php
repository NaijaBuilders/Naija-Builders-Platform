<?php

namespace App\Http\Requests\Mobile\SupplierOnboarding;

use Illuminate\Foundation\Http\FormRequest;

class BankDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (string) ($this->user()?->role ?? '') === 'supplier';
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:120'],
            'bank_code' => ['required', 'string', 'alpha_num', 'max:30'],
            'account_number' => ['required', 'digits_between:10,12'],
            'account_name' => ['nullable', 'string', 'max:191'],
        ];
    }
}
