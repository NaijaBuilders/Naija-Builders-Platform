<?php

namespace App\Services\BuyerOnboarding;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BuyerOtpService
{
    public function __construct(private readonly BuyerOnboardingSettings $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function issue(User $user, string $channel): array
    {
        $channel = $this->channel($channel);
        $code = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes($this->settings->otpTtlMinutes());

        DB::table('users')->where('id', $user->id)->update([
            $channel.'_otp_hash' => Hash::make($code),
            $channel.'_otp_expires_at' => $expiresAt,
            'updated_at' => now(),
        ]);

        return [
            'channel' => $channel,
            'expires_at' => $expiresAt->toISOString(),
            'debug_code' => $this->shouldExposeCode() ? $code : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function issueBoth(User $user): array
    {
        return [
            'email' => $this->issue($user, 'email'),
            'phone' => $this->issue($user, 'phone'),
        ];
    }

    public function confirm(User $user, string $channel, string $code): void
    {
        $channel = $this->channel($channel);
        $freshUser = User::query()->findOrFail($user->id);
        $hash = (string) ($freshUser->{$channel.'_otp_hash'} ?? '');
        $expiresAt = $freshUser->{$channel.'_otp_expires_at'} ?? null;

        if ($hash === '' || ! $expiresAt || now()->greaterThan($expiresAt)) {
            throw ValidationException::withMessages([
                'otp' => 'This confirmation code has expired. Please request a new code.',
            ]);
        }

        if (! Hash::check($code, $hash)) {
            throw ValidationException::withMessages([
                'otp' => 'The confirmation code is not correct.',
            ]);
        }

        DB::table('users')->where('id', $user->id)->update([
            $channel.'_verified_at' => now(),
            $channel.'_otp_hash' => null,
            $channel.'_otp_expires_at' => null,
            'updated_at' => now(),
        ]);
    }

    private function channel(string $channel): string
    {
        $channel = strtolower(trim($channel));
        if (! in_array($channel, ['email', 'phone'], true)) {
            throw ValidationException::withMessages([
                'channel' => 'Choose email or phone confirmation.',
            ]);
        }

        return $channel;
    }

    private function shouldExposeCode(): bool
    {
        return (bool) config('buyer_onboarding.otp.expose_in_local', true)
            && app()->environment(['local', 'testing']);
    }
}
