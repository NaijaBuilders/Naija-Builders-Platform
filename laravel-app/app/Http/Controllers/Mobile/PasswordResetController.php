<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    private const TTL_MINUTES = 15;

    public function sendCode(Request $request)
    {
        if (! Schema::hasTable('password_reset_codes')) {
            return response()->json([
                'message' => 'Password reset is being set up. Please try again shortly.',
            ], 503);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = DB::table('users')->where('email', $email)->first(['id', 'full_name', 'email']);

        // Always respond identically so the endpoint can't be used to
        // discover which emails have accounts.
        $genericResponse = [
            'message' => 'If an account exists for this email, a reset code has been sent.',
        ];

        if (! $user) {
            return response()->json($genericResponse);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_codes')->where('email', $email)->delete();
        DB::table('password_reset_codes')->insert([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $firstName = trim(explode(' ', trim((string) $user->full_name))[0] ?? '');

            Mail::to($email)->send(new PasswordResetMail(
                code: $code,
                ttlMinutes: self::TTL_MINUTES,
                recipientName: $firstName !== '' ? $firstName : null,
            ));
        } catch (\Throwable $exception) {
            Log::error('Password reset email failed to send.', [
                'error' => $exception->getMessage(),
            ]);
        }

        $exposeCode = app()->environment(['local', 'testing']);

        return response()->json($genericResponse + [
            'debug_code' => $exposeCode ? $code : null,
        ]);
    }

    public function reset(Request $request)
    {
        if (! Schema::hasTable('password_reset_codes')) {
            return response()->json([
                'message' => 'Password reset is being set up. Please try again shortly.',
            ], 503);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $email = strtolower(trim($validated['email']));
        $record = DB::table('password_reset_codes')->where('email', $email)->first();

        if (! $record || now()->greaterThan($record->expires_at)) {
            throw ValidationException::withMessages([
                'code' => 'This reset code has expired. Please request a new one.',
            ]);
        }

        if (! Hash::check($validated['code'], (string) $record->code_hash)) {
            throw ValidationException::withMessages([
                'code' => 'The reset code is not correct.',
            ]);
        }

        $user = DB::table('users')->where('email', $email)->first(['id']);
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'This account no longer exists.',
            ]);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password_hash' => Hash::make($validated['password']),
            'updated_at' => now(),
        ]);

        DB::table('password_reset_codes')->where('email', $email)->delete();
        DB::table('personal_access_tokens')
            ->where('tokenable_type', \App\Models\User::class)
            ->where('tokenable_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Your password has been reset. You can now sign in.',
        ]);
    }
}
