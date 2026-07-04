<?php

namespace App\Services\BuyerOnboarding;

use App\Mail\BuyerOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

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

        if ($channel === 'email') {
            $this->deliverEmail($user, $code);
        }

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

    private function deliverEmail(User $user, string $code): void
    {
        $email = trim((string) $user->email);
        if ($email === '') {
            throw ValidationException::withMessages([
                'channel' => 'Your account has no email address on file.',
            ]);
        }

        try {
            $firstName = trim(explode(' ', trim((string) $user->full_name))[0] ?? '');

            Mail::to($email)->send(new BuyerOtpMail(
                code: $code,
                ttlMinutes: $this->settings->otpTtlMinutes(),
                recipientName: $firstName !== '' ? $firstName : null,
            ));
        } catch (Throwable $exception) {
            Log::error('Buyer OTP email failed to send.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            if (! $this->shouldExposeCode()) {
                throw ValidationException::withMessages([
                    'channel' => 'We could not send the email right now. Please try again shortly.',
                ]);
            }
        }
    }

    private function shouldExposeCode(): bool
    {
        return (bool) config('buyer_onboarding.otp.expose_in_local', true)
            && app()->environment(['local', 'testing']);
    }
}
