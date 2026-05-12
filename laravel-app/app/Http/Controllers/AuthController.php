<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SupplierOnboarding\SupplierOnboardingService;
use App\Support\NameFormatter;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('legacy_user_id')) {
            return redirect($this->dashboardPathForRole((string) $request->session()->get('legacy_user.role', '')));
        }

        return view('auth.login', [
            'errorCode' => (string) $request->query('error', ''),
        ]);
    }

    public function showSignup(Request $request)
    {
        if ($request->session()->has('legacy_user_id')) {
            return redirect($this->dashboardPathForRole((string) $request->session()->get('legacy_user.role', '')));
        }

        $requestedType = (string) $request->query('type', 'builder');
        if ($requestedType === 'buyer') {
            $requestedType = 'builder';
        }

        return view('auth.signup', [
            'accountType' => in_array($requestedType, ['builder', 'supplier'], true)
                ? $requestedType
                : 'builder',
            'errorCode' => (string) $request->query('error', ''),
            'termsAccepted' => (bool) $request->session()->get('terms_and_agreement_accepted', false),
        ]);
    }

    public function login(Request $request)
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            return redirect('/login.php?error=missing_credentials');
        }

        try {
            $user = DB::table('users')
                ->select([
                    'id',
                    'full_name',
                    'email',
                    'role',
                    'location',
                    'password_hash',
                    'profile_image_path',
                    Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                    Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
                    Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
                ])
                ->where('email', $email)
                ->first();
        } catch (QueryException) {
            return redirect('/login.php?error=db_unavailable');
        }

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            return redirect('/login.php?error=invalid_credentials');
        }

        $request->session()->regenerate();
        $request->session()->put('legacy_user_id', (int) $user->id);
        $request->session()->put('legacy_user', [
            'id' => (int) $user->id,
            'email' => $user->email,
            'name' => NameFormatter::title((string) $user->full_name),
            'role' => $user->role,
            'location' => (string) ($user->location ?? ''),
            'profile_image_path' => (string) ($user->profile_image_path ?? ''),
            'subscription_plan' => (string) ($user->subscription_plan ?? 'standard'),
            'is_verified_badge' => (int) ($user->is_verified_badge ?? 0) === 1,
            'kyc_status' => (string) ($user->kyc_status ?: 'approved'),
        ]);

        return redirect($this->dashboardPathForRole((string) $user->role));
    }

    public function register(Request $request)
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $phone = trim((string) $request->input('phone', ''));
        $company = trim((string) $request->input('company', ''));
        $location = trim((string) $request->input('location', ''));
        $accountType = (string) $request->input('account_type', 'builder');
        if ($accountType === 'buyer') {
            $accountType = 'builder';
        }
        $signupPath = $accountType === 'supplier' ? '/signup.php?type=supplier' : '/signup.php';
        $signupErrorPath = static function (string $basePath, string $errorCode): string {
            $separator = str_contains($basePath, '?') ? '&' : '?';

            return $basePath.$separator.'error='.$errorCode;
        };
        $hasAcceptedTermsFlow = (bool) $request->session()->get('terms_and_agreement_accepted', false);
        $password = (string) $request->input('password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        if ($name === '' || $email === '' || $phone === '' || $location === '' || $password === '') {
            return redirect($signupErrorPath($signupPath, 'missing_fields'))->withInput();
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect($signupErrorPath($signupPath, 'invalid_email'))->withInput();
        }

        if (! in_array($accountType, ['builder', 'supplier'], true)) {
            $accountType = 'builder';
        }

        if (mb_strlen($password) < 8) {
            return redirect($signupErrorPath($signupPath, 'weak_password'))->withInput();
        }

        if ($password !== $confirmPassword) {
            return redirect($signupErrorPath($signupPath, 'passwords_do_not_match'))->withInput();
        }

        if (! $hasAcceptedTermsFlow) {
            return redirect($signupErrorPath($signupPath, 'terms_not_accepted'))->withInput();
        }

        $existing = DB::table('users')->where('email', $email)->exists();
        if ($existing) {
            return redirect($signupErrorPath($signupPath, 'email_exists'))->withInput();
        }

        if ($accountType === 'builder' && $company === '') {
            $company = 'Individual Buyer';
        }

        $formattedName = NameFormatter::title($name);

        if ($accountType === 'supplier' && $company === '') {
            $company = $formattedName;
        }

        $hasBusinessCategory = Schema::hasColumn('users', 'business_category');
        $hasBusinessAddress = Schema::hasColumn('users', 'business_address');
        $hasBusinessDescription = Schema::hasColumn('users', 'business_description');
        $hasBankName = Schema::hasColumn('users', 'bank_name');
        $hasAccountNumber = Schema::hasColumn('users', 'account_number');
        $hasKycStatus = Schema::hasColumn('users', 'kyc_status');

        $insertPayload = [
            'full_name' => $formattedName,
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'location' => $location,
            'role' => $accountType,
            'password_hash' => Hash::make($password),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($hasBusinessCategory) {
            $insertPayload['business_category'] = null;
        }

        if ($hasBusinessAddress) {
            $insertPayload['business_address'] = null;
        }

        if ($hasBusinessDescription) {
            $insertPayload['business_description'] = null;
        }

        if ($hasBankName) {
            $insertPayload['bank_name'] = null;
        }

        if ($hasAccountNumber) {
            $insertPayload['account_number'] = null;
        }

        if ($hasKycStatus) {
            $insertPayload['kyc_status'] = $accountType === 'supplier' ? 'pending' : 'approved';
        }

        $userId = DB::table('users')->insertGetId($insertPayload);

        $request->session()->regenerate();
        $request->session()->put('legacy_user_id', (int) $userId);
        $request->session()->put('legacy_user', [
            'id' => (int) $userId,
            'email' => $email,
            'name' => $formattedName,
            'company' => $company,
            'role' => $accountType,
            'location' => $location,
            'profile_image_path' => '',
            'subscription_plan' => 'standard',
            'is_verified_badge' => false,
            'kyc_status' => $accountType === 'supplier' ? 'pending' : 'approved',
        ]);
        $request->session()->put('show_onboarding_tour', true);
        $request->session()->forget('terms_and_agreement_accepted');

        if ($accountType === 'supplier') {
            return redirect('/dashboard.php?success=signup');
        }

        return redirect('/subscription.php');
    }

    public function showSupplierKyc(Request $request, SupplierOnboardingService $onboarding)
    {
        if (! $request->session()->has('legacy_user_id')) {
            return redirect('/login.php');
        }

        $legacyUser = (array) $request->session()->get('legacy_user', []);
        if ((string) ($legacyUser['role'] ?? '') !== 'supplier') {
            return redirect('/buyer-dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);

        $user = User::query()->find($currentUserId);
        if (! $user) {
            return redirect('/login.php');
        }

        $application = $onboarding->applicationForUser($user)->refresh();

        $legacyUser['kyc_status'] = (string) ($user->kyc_status ?: $this->legacyKycStatusFromApplication((string) $application->status));
        $request->session()->put('legacy_user', $legacyUser);

        return view('auth.supplier-kyc', [
            'user' => $user,
            'application' => $application,
            'successCode' => (string) $request->query('success', ''),
            'errorCode' => (string) $request->query('error', ''),
        ]);
    }

    public function submitSupplierKyc(Request $request, SupplierOnboardingService $onboarding): RedirectResponse
    {
        if (! $request->session()->has('legacy_user_id')) {
            return redirect('/login.php');
        }

        $legacyUser = (array) $request->session()->get('legacy_user', []);
        if ((string) ($legacyUser['role'] ?? '') !== 'supplier') {
            return redirect('/buyer-dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);
        $user = User::query()->find($currentUserId);
        if (! $user) {
            return redirect('/login.php');
        }

        $validator = Validator::make($request->all(), [
            'cac_number' => ['required', 'string', 'max:40', 'regex:/^(RC|BN|IT)?[A-Za-z0-9\-\/]{4,30}$/'],
            'business_name' => ['required', 'string', 'min:2', 'max:191'],
            'business_type' => ['required', 'string', 'max:80'],
            'business_address' => ['required', 'string', 'min:5', 'max:255'],
            'state' => ['required', 'string', 'max:80'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'bvn' => ['nullable', 'digits:11'],
            'nin' => ['nullable', 'digits:11'],
            'id_document_type' => ['nullable', 'string', 'max:40'],
            'selfie' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'bank_name' => ['required', 'string', 'max:120'],
            'bank_code' => ['required', 'string', 'alpha_num', 'max:30'],
            'account_number' => ['required', 'digits_between:10,12'],
            'account_name' => ['nullable', 'string', 'max:191'],
        ]);

        $safeInput = $request->except(['bvn', 'nin', 'account_number', 'selfie', 'id_document']);
        if ($validator->fails()) {
            return redirect('/supplier-kyc.php?error=invalid_fields')
                ->withErrors($validator)
                ->withInput($safeInput);
        }

        $validated = $validator->validated();
        if (trim((string) ($validated['bvn'] ?? '')) === '' && trim((string) ($validated['nin'] ?? '')) === '') {
            return redirect('/supplier-kyc.php?error=missing_identity')
                ->withInput($safeInput);
        }

        try {
            $onboarding->submitBusinessDetails($user, [
                'cac_number' => $validated['cac_number'],
                'business_name' => $validated['business_name'],
                'business_type' => $validated['business_type'],
                'business_address' => $validated['business_address'],
                'state' => $validated['state'],
                'contact_name' => $validated['contact_name'] ?? $user->full_name,
                'contact_email' => $validated['contact_email'] ?? $user->email,
                'contact_phone' => $validated['contact_phone'] ?? $user->phone,
            ], $request);

            $onboarding->submitIdentity($user, [
                'bvn' => $validated['bvn'] ?? '',
                'nin' => $validated['nin'] ?? '',
                'id_document_type' => $validated['id_document_type'] ?? null,
            ], $request);

            $onboarding->submitBankDetails($user, [
                'bank_name' => $validated['bank_name'],
                'bank_code' => $validated['bank_code'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'] ?? $validated['business_name'],
            ], $request);

            $application = $onboarding->submitForDecision($user, $request);
        } catch (Throwable) {
            return redirect('/supplier-kyc.php?error=submit_failed')
                ->withInput($safeInput);
        }

        $user->refresh();
        $legacyUser['company'] = (string) ($user->company ?? $validated['business_name']);
        $legacyUser['location'] = (string) ($user->location ?? $validated['state']);
        $legacyUser['kyc_status'] = (string) ($user->kyc_status ?: $this->legacyKycStatusFromApplication((string) $application->status));
        $request->session()->put('legacy_user', $legacyUser);

        return redirect('/supplier-kyc.php?success=processed');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/index.php');
    }

    private function dashboardPathForRole(string $role): string
    {
        return $role === 'supplier' ? '/dashboard.php' : '/buyer-dashboard.php';
    }

    private function legacyKycStatusFromApplication(string $status): string
    {
        return match (strtoupper($status)) {
            'APPROVED' => 'approved',
            'REJECTED' => 'rejected',
            'MANUAL_REVIEW' => 'manual_review',
            'MORE_INFO_REQUIRED' => 'more_info_required',
            'VERIFYING' => 'verifying',
            'SUBMITTED' => 'submitted',
            default => 'pending',
        };
    }
}
