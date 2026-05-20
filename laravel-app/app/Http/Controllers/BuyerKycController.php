<?php

namespace App\Http\Controllers;

use App\Models\BuyerIdentityVerification;
use App\Models\User;
use App\Services\BuyerOnboarding\BuyerKycService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class BuyerKycController extends Controller
{
    public function show(Request $request)
    {
        $legacyUser = (array) $request->session()->get('legacy_user', []);
        if ((string) ($legacyUser['role'] ?? '') === 'supplier') {
            return redirect('/dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $verification = Schema::hasTable('buyer_identity_verifications')
            ? BuyerIdentityVerification::query()
                ->where('user_id', $currentUserId)
                ->latest('id')
                ->first()
            : null;

        return view('auth.buyer-kyc', [
            'currentUser' => $legacyUser,
            'verification' => $verification,
            'successCode' => (string) $request->query('success', ''),
            'errorCode' => (string) $request->query('error', ''),
            'documentTypes' => config('buyer_onboarding.kyc.document_types', []),
        ]);
    }

    public function submit(Request $request, BuyerKycService $kyc): RedirectResponse
    {
        $legacyUser = (array) $request->session()->get('legacy_user', []);
        if ((string) ($legacyUser['role'] ?? '') === 'supplier') {
            return redirect('/dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $user = User::query()->find($currentUserId);
        if (! $user) {
            return redirect('/login.php');
        }

        $validator = Validator::make($request->all(), [
            'document_type' => ['required', 'string', 'in:nin_slip,international_passport,drivers_licence'],
            'verified_id_name' => ['nullable', 'string', 'max:150'],
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
            'selfie' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:8192'],
        ]);

        if ($validator->fails()) {
            return redirect('/buyer-kyc.php?error=invalid_fields')
                ->withErrors($validator)
                ->withInput($request->except(['id_document', 'selfie']));
        }

        $verification = $kyc->submit($user, $validator->validated(), $request);

        return redirect('/buyer-kyc.php?success='.$verification->status);
    }
}
