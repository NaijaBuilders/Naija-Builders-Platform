<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BuyerOnboarding\BuyerOtpService;
use App\Services\SupplierOnboarding\SupplierOnboardingService;
use App\Support\NameFormatter;
use App\Support\Security\SensitiveData;
use App\Support\Username;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function __construct(
        private readonly SupplierOnboardingService $onboarding,
        private readonly BuyerOtpService $otp,
    ) {}

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required_without:email', 'string'],
            'email' => ['required_without:login', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim((string) ($request->input('login') ?: $request->input('email')));
        $hasUsername = Schema::hasColumn('users', 'username');

        try {
            $user = DB::table('users')
                ->select([
                    'id',
                    'full_name',
                    'email',
                    $hasUsername ? 'username' : DB::raw('null as username'),
                    'phone',
                    'company',
                    'location',
                    'role',
                    'password_hash',
                    'profile_image_path',
                    Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                    Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
                    Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
                    Schema::hasColumn('users', 'offers_services') ? 'offers_services' : DB::raw('0 as offers_services'),
                    Schema::hasColumn('users', 'service_category') ? 'service_category' : DB::raw('null as service_category'),
                    Schema::hasColumn('users', 'email_verified_at') ? 'email_verified_at' : DB::raw('null as email_verified_at'),
                    Schema::hasColumn('users', 'phone_verified_at') ? 'phone_verified_at' : DB::raw('null as phone_verified_at'),
                ])
                ->where(function ($query) use ($identifier, $hasUsername) {
                    $query->where('email', $identifier);
                    if ($hasUsername) {
                        $query->orWhere('username', mb_strtolower($identifier));
                    }
                })
                ->first();
        } catch (QueryException) {
            return response()->json(['message' => 'Database is unavailable.'], 503);
        }

        if (! $user || ! Hash::check($validated['password'], (string) $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        $token = $this->createMobileToken((int) $user->id);

        return response()->json([
            'token' => $token,
            'user' => $this->buildUserPayload($user),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2'],
            'email' => ['required', 'email'],
            'username' => ['nullable', 'string', 'max:30'],
            'phone' => ['required', 'string'],
            'location' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'string'],
            'account_type' => ['nullable', 'string'],
            'company' => ['nullable', 'string'],
            'service_category' => ['nullable', 'string', 'max:80'],
            'service_areas' => ['nullable', 'string', 'max:255'],
            'terms_accepted' => ['required', 'boolean'],
            'privacy_accepted' => ['nullable', 'boolean'],
            'device_fingerprint' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['password'] !== $validated['confirm_password']) {
            return response()->json(['message' => 'Passwords do not match.'], 422);
        }

        if (! $validated['terms_accepted']) {
            return response()->json(['message' => 'Terms must be accepted before registration.'], 422);
        }
        if (array_key_exists('privacy_accepted', $validated) && ! $validated['privacy_accepted']) {
            return response()->json(['message' => 'Privacy agreement must be accepted before registration.'], 422);
        }

        $requestedAccountType = $validated['account_type'] ?? 'builder';
        if ($requestedAccountType === 'buyer') {
            $requestedAccountType = 'builder';
        }
        $offersServices = $this->isServiceProviderIntent((string) $requestedAccountType);
        $accountType = $offersServices ? 'supplier' : $requestedAccountType;
        if (! in_array($accountType, ['builder', 'supplier'], true)) {
            $accountType = 'builder';
            $offersServices = false;
        }

        $emailExists = DB::table('users')->where('email', $validated['email'])->exists();
        if ($emailExists) {
            return response()->json(['message' => 'Email already exists.'], 422);
        }

        $hasUsername = Schema::hasColumn('users', 'username');
        $username = null;
        if ($hasUsername) {
            $usernameInput = Username::normalize($validated['username'] ?? '');
            if ($usernameInput !== '') {
                if (! Username::isValid($usernameInput)) {
                    return response()->json(['message' => 'Username must be 3-30 characters using letters, numbers, dots or underscores.'], 422);
                }
                if (DB::table('users')->where('username', $usernameInput)->exists()) {
                    return response()->json(['message' => 'Username is already taken.'], 422);
                }
                $username = $usernameInput;
            } else {
                $seed = explode('@', $validated['email'])[0] ?: $validated['name'];
                $username = Username::generate($seed, static fn (string $candidate): bool => DB::table('users')->where('username', $candidate)->exists());
            }
        }

        $company = trim((string) ($validated['company'] ?? ''));
        if ($accountType === 'builder' && $company === '') {
            $company = 'Individual Buyer';
        }
        if ($accountType === 'supplier' && $company === '') {
            $company = $validated['name'];
        }

        $hasBusinessCategory = Schema::hasColumn('users', 'business_category');
        $hasBusinessAddress = Schema::hasColumn('users', 'business_address');
        $hasBusinessDescription = Schema::hasColumn('users', 'business_description');
        $hasBankName = Schema::hasColumn('users', 'bank_name');
        $hasAccountNumber = Schema::hasColumn('users', 'account_number');
        $hasKycStatus = Schema::hasColumn('users', 'kyc_status');
        $hasOffersServices = Schema::hasColumn('users', 'offers_services');
        $hasServiceCategory = Schema::hasColumn('users', 'service_category');
        $hasServiceAreas = Schema::hasColumn('users', 'service_areas');

        $insertPayload = [
            'full_name' => NameFormatter::title($validated['name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $company,
            'location' => $validated['location'] ?? '',
            'role' => $accountType,
            'password_hash' => Hash::make($validated['password']),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($hasUsername && $username !== null) {
            $insertPayload['username'] = $username;
        }

        if ($hasBusinessCategory) {
            $insertPayload['business_category'] = $offersServices ? 'Professional Services' : null;
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

        if ($hasOffersServices) {
            $insertPayload['offers_services'] = $offersServices;
        }

        if ($hasServiceCategory) {
            $insertPayload['service_category'] = $offersServices ? ($validated['service_category'] ?? null) : null;
        }

        if ($hasServiceAreas) {
            $insertPayload['service_areas'] = $offersServices ? ($validated['service_areas'] ?? ($validated['location'] ?? '')) : null;
        }

        $ip = (string) $request->ip();
        if (Schema::hasColumn('users', 'registration_ip_hash')) {
            $insertPayload['registration_ip_hash'] = SensitiveData::fingerprint($ip);
            $insertPayload['registration_ip_display'] = SensitiveData::maskIp($ip);
        }

        $deviceFingerprint = (string) ($request->header('X-Device-Fingerprint') ?: ($validated['device_fingerprint'] ?? ''));
        if ($deviceFingerprint !== '' && Schema::hasColumn('users', 'device_fingerprint_hash')) {
            $insertPayload['device_fingerprint_hash'] = SensitiveData::fingerprint($deviceFingerprint);
            $insertPayload['device_fingerprint_display'] = SensitiveData::maskToken($deviceFingerprint);
        }

        $userId = (int) DB::table('users')->insertGetId($insertPayload);
        $userModel = User::query()->findOrFail($userId);
        $otpDelivery = [];
        if (Schema::hasColumn('users', 'email_otp_hash') && Schema::hasColumn('users', 'phone_otp_hash')) {
            $otpDelivery = $this->otp->issueBoth($userModel);
        }

        if ($accountType === 'supplier') {
            $this->onboarding->captureRegistrationSignals($userModel, $request, $validated['phone']);
        }

        $user = DB::table('users')
            ->select([
                'id',
                'full_name',
                'email',
                Schema::hasColumn('users', 'username') ? 'username' : DB::raw('null as username'),
                'phone',
                'company',
                'location',
                'role',
                'profile_image_path',
                Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
                Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
                Schema::hasColumn('users', 'offers_services') ? 'offers_services' : DB::raw('0 as offers_services'),
                Schema::hasColumn('users', 'service_category') ? 'service_category' : DB::raw('null as service_category'),
                Schema::hasColumn('users', 'email_verified_at') ? 'email_verified_at' : DB::raw('null as email_verified_at'),
                Schema::hasColumn('users', 'phone_verified_at') ? 'phone_verified_at' : DB::raw('null as phone_verified_at'),
            ])
            ->where('id', $userId)
            ->first();

        $token = $this->createMobileToken($userId);

        return response()->json([
            'token' => $token,
            'user' => $user ? $this->buildUserPayload($user) : null,
            'otp' => $otpDelivery,
        ], 201);
    }

    public function user(Request $request)
    {
        $userId = (int) $request->user()->id;
        $user = DB::table('users')
            ->select([
                'id',
                'full_name',
                'email',
                Schema::hasColumn('users', 'username') ? 'username' : DB::raw('null as username'),
                'phone',
                'company',
                'location',
                'role',
                'profile_image_path',
                Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
                Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
                Schema::hasColumn('users', 'offers_services') ? 'offers_services' : DB::raw('0 as offers_services'),
                Schema::hasColumn('users', 'service_category') ? 'service_category' : DB::raw('null as service_category'),
                Schema::hasColumn('users', 'email_verified_at') ? 'email_verified_at' : DB::raw('null as email_verified_at'),
                Schema::hasColumn('users', 'phone_verified_at') ? 'phone_verified_at' : DB::raw('null as phone_verified_at'),
            ])
            ->where('id', $userId)
            ->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        return response()->json([
            'user' => $this->buildUserPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    private function createMobileToken(int $userId): string
    {
        $user = User::query()->findOrFail($userId);

        return $user->createToken('mobile')->plainTextToken;
    }

    private function buildUserPayload(object $user): array
    {
        return [
            'id' => (int) $user->id,
            'email' => (string) $user->email,
            'username' => isset($user->username) && $user->username !== null ? (string) $user->username : null,
            'name' => NameFormatter::title((string) ($user->full_name ?? 'User')),
            'role' => (string) ($user->role ?? 'builder'),
            'phone' => (string) ($user->phone ?? ''),
            'company' => (string) ($user->company ?? ''),
            'location' => (string) ($user->location ?? ''),
            'profile_image_path' => (string) ($user->profile_image_path ?? ''),
            'subscription_plan' => (string) ($user->subscription_plan ?? 'standard'),
            'is_verified_badge' => (int) ($user->is_verified_badge ?? 0) === 1,
            'kyc_status' => (string) ($user->kyc_status ?? 'approved'),
            'offers_services' => (int) ($user->offers_services ?? 0) === 1,
            'service_category' => $user->service_category ? (string) $user->service_category : null,
            'email_confirmed' => ! empty($user->email_verified_at),
            'phone_confirmed' => ! empty($user->phone_verified_at),
        ];
    }

    private function isServiceProviderIntent(string $accountType): bool
    {
        return in_array($accountType, ['service', 'services', 'service_provider', 'offer_services'], true);
    }
}
