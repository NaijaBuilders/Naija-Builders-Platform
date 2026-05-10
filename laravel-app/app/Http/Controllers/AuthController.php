<?php

namespace App\Http\Controllers;

use App\Support\Security\SensitiveData;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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
            'name' => $user->full_name,
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

        if ($accountType === 'supplier' && $company === '') {
            $company = $name;
        }

        $hasBusinessCategory = Schema::hasColumn('users', 'business_category');
        $hasBusinessAddress = Schema::hasColumn('users', 'business_address');
        $hasBusinessDescription = Schema::hasColumn('users', 'business_description');
        $hasBankName = Schema::hasColumn('users', 'bank_name');
        $hasAccountNumber = Schema::hasColumn('users', 'account_number');
        $hasKycStatus = Schema::hasColumn('users', 'kyc_status');

        $insertPayload = [
            'full_name' => $name,
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
            'name' => $name,
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

    public function showSupplierKyc(Request $request)
    {
        if (! $request->session()->has('legacy_user_id')) {
            return redirect('/login.php');
        }

        $legacyUser = (array) $request->session()->get('legacy_user', []);
        if ((string) ($legacyUser['role'] ?? '') !== 'supplier') {
            return redirect('/buyer-dashboard.php');
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);

        $user = DB::table('users')
            ->select([
                'full_name',
                'email',
                'phone',
                'company',
                Schema::hasColumn('users', 'business_category') ? 'business_category' : DB::raw('NULL as business_category'),
                'location',
                Schema::hasColumn('users', 'business_address') ? 'business_address' : DB::raw('NULL as business_address'),
                Schema::hasColumn('users', 'business_description') ? 'business_description' : DB::raw('NULL as business_description'),
                Schema::hasColumn('users', 'bank_name') ? 'bank_name' : DB::raw('NULL as bank_name'),
                Schema::hasColumn('users', 'account_number') ? 'account_number' : DB::raw('NULL as account_number'),
                Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
            ])
            ->where('id', $currentUserId)
            ->first();

        if ($user) {
            $legacyUser['kyc_status'] = (string) ($user->kyc_status ?: 'approved');
            $request->session()->put('legacy_user', $legacyUser);
        }

        return view('auth.supplier-kyc', [
            'user' => $user,
            'successCode' => (string) $request->query('success', ''),
            'errorCode' => (string) $request->query('error', ''),
        ]);
    }

    public function submitSupplierKyc(Request $request): RedirectResponse
    {
        if (! $request->session()->has('legacy_user_id')) {
            return redirect('/login.php');
        }

        $legacyUser = (array) $request->session()->get('legacy_user', []);
        if ((string) ($legacyUser['role'] ?? '') !== 'supplier') {
            return redirect('/buyer-dashboard.php');
        }

        $company = trim((string) $request->input('company', ''));
        $businessCategory = trim((string) $request->input('business_category', ''));
        $location = trim((string) $request->input('location', ''));
        $businessAddress = trim((string) $request->input('business_address', ''));
        $businessDescription = trim((string) $request->input('business_description', ''));
        $bankName = trim((string) $request->input('bank_name', ''));
        $accountNumber = trim((string) $request->input('account_number', ''));

        if ($company === '' || $businessCategory === '' || $location === '' || $businessAddress === '' || $bankName === '' || $accountNumber === '') {
            return redirect('/supplier-kyc.php?error=missing_fields')->withInput();
        }

        $currentUserId = (int) $request->session()->get('legacy_user_id', 0);

        $updatePayload = [
            'company' => $company,
            'location' => $location,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'business_category')) {
            $updatePayload['business_category'] = $businessCategory;
        }

        if (Schema::hasColumn('users', 'business_address')) {
            $updatePayload['business_address'] = $businessAddress;
        }

        if (Schema::hasColumn('users', 'business_description')) {
            $updatePayload['business_description'] = $businessDescription;
        }

        if (Schema::hasColumn('users', 'bank_name')) {
            $updatePayload['bank_name'] = $bankName;
        }

        if (Schema::hasColumn('users', 'account_number')) {
            $updatePayload['account_number'] = SensitiveData::maskDigits($accountNumber);
        }

        if (Schema::hasColumn('users', 'kyc_status')) {
            $updatePayload['kyc_status'] = 'submitted';
        }

        if (Schema::hasColumn('users', 'kyc_submitted_at')) {
            $updatePayload['kyc_submitted_at'] = now();
        }

        DB::table('users')
            ->where('id', $currentUserId)
            ->update($updatePayload);

        $legacyUser['company'] = $company;
        $legacyUser['location'] = $location;
        $legacyUser['kyc_status'] = Schema::hasColumn('users', 'kyc_status') ? 'submitted' : 'approved';
        $request->session()->put('legacy_user', $legacyUser);

        return redirect('/supplier-kyc.php?success=submitted');
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
}
