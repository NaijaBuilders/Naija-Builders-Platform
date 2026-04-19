<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $user = DB::table('users')
                ->select([
                    'id',
                    'full_name',
                    'email',
                    'role',
                    'password_hash',
                    'profile_image_path',
                    Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                    Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
                    Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
                ])
                ->where('email', $validated['email'])
                ->first();
        } catch (QueryException) {
            return response()->json(['message' => 'Database is unavailable.'], 503);
        }

        if (!$user || !Hash::check($validated['password'], (string) $user->password_hash)) {
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
            'phone' => ['required', 'string'],
            'location' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'string'],
            'account_type' => ['nullable', 'string'],
            'company' => ['nullable', 'string'],
            'terms_accepted' => ['required', 'boolean'],
        ]);

        if ($validated['password'] !== $validated['confirm_password']) {
            return response()->json(['message' => 'Passwords do not match.'], 422);
        }

        if (!$validated['terms_accepted']) {
            return response()->json(['message' => 'Terms must be accepted before registration.'], 422);
        }

        $accountType = $validated['account_type'] ?? 'builder';
        if ($accountType === 'buyer') {
            $accountType = 'builder';
        }
        if (!in_array($accountType, ['builder', 'supplier'], true)) {
            $accountType = 'builder';
        }

        $emailExists = DB::table('users')->where('email', $validated['email'])->exists();
        if ($emailExists) {
            return response()->json(['message' => 'Email already exists.'], 422);
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

        $insertPayload = [
            'full_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $company,
            'location' => $validated['location'],
            'role' => $accountType,
            'password_hash' => Hash::make($validated['password']),
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

        $userId = (int) DB::table('users')->insertGetId($insertPayload);
        $user = DB::table('users')
            ->select([
                'id',
                'full_name',
                'email',
                'role',
                'profile_image_path',
                Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
                Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
            ])
            ->where('id', $userId)
            ->first();

        $token = $this->createMobileToken($userId);

        return response()->json([
            'token' => $token,
            'user' => $user ? $this->buildUserPayload($user) : null,
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
                'role',
                'profile_image_path',
                Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
                Schema::hasColumn('users', 'kyc_status') ? DB::raw("COALESCE(kyc_status, 'approved') as kyc_status") : DB::raw("'approved' as kyc_status"),
            ])
            ->where('id', $userId)
            ->first();

        if (!$user) {
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
            'name' => (string) ($user->full_name ?? 'User'),
            'role' => (string) ($user->role ?? 'builder'),
            'profile_image_path' => (string) ($user->profile_image_path ?? ''),
            'subscription_plan' => (string) ($user->subscription_plan ?? 'standard'),
            'is_verified_badge' => (int) ($user->is_verified_badge ?? 0) === 1,
            'kyc_status' => (string) ($user->kyc_status ?? 'approved'),
        ];
    }
}
