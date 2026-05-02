<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Support\CurrencyManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    private const DEFAULT_APP_SETTINGS = [
        'notifications_email' => true,
        'notifications_sms' => false,
        'notifications_push' => true,
        'language' => 'en',
        'timezone' => 'Africa/Lagos',
        'detected_timezone' => '',
        'currency_mode' => 'auto',
        'currency' => 'NGN',
        'auto_save_drafts' => true,
        'compact_dashboard' => false,
        'material_alerts' => true,
        'message_sound' => true,
        'two_factor_login' => false,
        'api_access' => false,
        'developer_mode' => false,
        'beta_features' => false,
        'activity_logs' => true,
        'session_timeout_short' => false,
    ];

    public function show(Request $request)
    {
        $currentUserId = (int) $request->user()->id;

        $user = DB::table('users')
            ->select([
                'full_name',
                'email',
                'phone',
                'company',
                'business_category',
                'location',
                'business_address',
                'business_description',
                'bank_name',
                'account_number',
                'role',
                'profile_image_path',
                Schema::hasColumn('users', 'subscription_plan') ? 'subscription_plan' : DB::raw("'standard' as subscription_plan"),
                Schema::hasColumn('users', 'subscription_started_at') ? 'subscription_started_at' : DB::raw('NULL as subscription_started_at'),
                Schema::hasColumn('users', 'is_verified_badge') ? 'is_verified_badge' : DB::raw('0 as is_verified_badge'),
            ])
            ->where('id', $currentUserId)
            ->first();

        $fullName = trim((string) ($user->full_name ?? ''));
        $nameParts = preg_split('/\s+/', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        return response()->json([
            'user' => $user,
            'full_name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }

    public function update(Request $request)
    {
        $currentUserId = (int) $request->user()->id;

        $currentUser = DB::table('users')
            ->select(['profile_image_path'])
            ->where('id', $currentUserId)
            ->first();

        $firstName = trim((string) $request->input('first_name', ''));
        $lastName = trim((string) $request->input('last_name', ''));
        $fullName = trim($firstName . ' ' . $lastName);
        $email = trim((string) $request->input('email', ''));
        $phone = trim((string) $request->input('phone', ''));
        $company = trim((string) $request->input('company', ''));
        $businessCategory = trim((string) $request->input('business_category', ''));
        $location = trim((string) $request->input('location', ''));
        $businessAddress = trim((string) $request->input('business_address', ''));
        $businessDescription = trim((string) $request->input('business_description', ''));
        $bankName = trim((string) $request->input('bank_name', ''));
        $accountNumber = trim((string) $request->input('account_number', ''));

        $emailOwner = DB::table('users')
            ->where('email', $email)
            ->where('id', '<>', $currentUserId)
            ->exists();

        if ($emailOwner) {
            return response()->json(['message' => 'Email already exists.'], 422);
        }

        $newProfileImagePath = null;
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');

            if (!$image || !$image->isValid()) {
                return response()->json(['message' => 'Image upload failed.'], 422);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $extension = strtolower((string) $image->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions, true)) {
                return response()->json(['message' => 'Invalid image type.'], 422);
            }

            if ((int) $image->getSize() > 2 * 1024 * 1024) {
                return response()->json(['message' => 'Image is too large.'], 422);
            }

            $uploadDir = public_path('assets/images/profile');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'user_' . $currentUserId . '_' . time() . '.' . $extension;
            $image->move($uploadDir, $filename);
            $newProfileImagePath = 'assets/images/profile/' . $filename;

            $previousPath = (string) ($currentUser->profile_image_path ?? '');
            if ($previousPath !== '' && str_starts_with($previousPath, 'assets/images/profile/')) {
                $absolutePreviousPath = public_path($previousPath);
                if (is_file($absolutePreviousPath)) {
                    @unlink($absolutePreviousPath);
                }
            }
        }

        $updatePayload = [
            'full_name' => $fullName !== '' ? $fullName : 'User',
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'business_category' => $businessCategory,
            'location' => $location,
            'business_address' => $businessAddress,
            'business_description' => $businessDescription,
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'updated_at' => now(),
        ];

        if ($newProfileImagePath !== null) {
            $updatePayload['profile_image_path'] = $newProfileImagePath;
        }

        DB::table('users')
            ->where('id', $currentUserId)
            ->update($updatePayload);

        return $this->show($request);
    }

    public function settings(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        $savedSettings = (array) Cache::get($this->settingsCacheKey($currentUserId), []);
        $settings = array_merge(self::DEFAULT_APP_SETTINGS, $savedSettings);

        return response()->json(['settings' => $settings]);
    }

    public function saveSettings(Request $request)
    {
        $currentUserId = (int) $request->user()->id;
        $settings = [
            'notifications_email' => (string) $request->input('notifications_email', '0') === '1',
            'notifications_sms' => (string) $request->input('notifications_sms', '0') === '1',
            'notifications_push' => (string) $request->input('notifications_push', '0') === '1',
            'language' => (string) $request->input('language', 'en'),
            'timezone' => (string) $request->input('timezone', 'Africa/Lagos'),
            'detected_timezone' => (string) $request->input('detected_timezone', ''),
            'currency_mode' => (string) $request->input('currency_mode', 'auto'),
            'currency' => (string) $request->input('currency', 'NGN'),
            'auto_save_drafts' => (string) $request->input('auto_save_drafts', '0') === '1',
            'compact_dashboard' => (string) $request->input('compact_dashboard', '0') === '1',
            'material_alerts' => (string) $request->input('material_alerts', '0') === '1',
            'message_sound' => (string) $request->input('message_sound', '0') === '1',
            'two_factor_login' => (string) $request->input('two_factor_login', '0') === '1',
            'api_access' => (string) $request->input('api_access', '0') === '1',
            'developer_mode' => (string) $request->input('developer_mode', '0') === '1',
            'beta_features' => (string) $request->input('beta_features', '0') === '1',
            'activity_logs' => (string) $request->input('activity_logs', '0') === '1',
            'session_timeout_short' => (string) $request->input('session_timeout_short', '0') === '1',
        ];

        if (!in_array($settings['language'], ['en', 'ha', 'yo', 'ig'], true)) {
            $settings['language'] = 'en';
        }

        if (!in_array($settings['timezone'], ['Africa/Lagos', 'UTC', 'Europe/London', 'America/New_York'], true)) {
            $settings['timezone'] = 'Africa/Lagos';
        }

        if (!in_array($settings['currency_mode'], ['auto', 'manual'], true)) {
            $settings['currency_mode'] = 'auto';
        }

        $currencyManager = app(CurrencyManager::class);
        if (!$currencyManager->isSupported($settings['currency'])) {
            $settings['currency'] = 'NGN';
        }

        Cache::put($this->settingsCacheKey($currentUserId), $settings, now()->addDays(30));

        return response()->json(['settings' => $settings]);
    }

    private function settingsCacheKey(int $userId): string
    {
        return 'mobile_settings_' . $userId;
    }
}
