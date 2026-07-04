<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    /**
     * Change the signed-in user's password. All other sessions are revoked;
     * the current token stays valid so the user is not logged out.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'different:current_password'],
        ]);

        $user = $request->user();
        $stored = DB::table('users')->where('id', $user->id)->first(['id', 'password_hash']);

        if (! $stored || ! Hash::check($validated['current_password'], (string) $stored->password_hash)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is not correct.',
            ]);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password_hash' => Hash::make($validated['new_password']),
            'updated_at' => now(),
        ]);

        $currentTokenId = (int) optional($user->currentAccessToken())->id;
        DB::table('personal_access_tokens')
            ->where('tokenable_type', \App\Models\User::class)
            ->where('tokenable_id', $user->id)
            ->when($currentTokenId > 0, fn ($query) => $query->where('id', '<>', $currentTokenId))
            ->delete();

        return response()->json([
            'message' => 'Your password has been changed.',
        ]);
    }

    /**
     * Delete the authenticated user's account. Required for app store
     * compliance. The row is anonymised rather than hard-deleted so that
     * order history stays intact for counterparties.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();
        $stored = DB::table('users')->where('id', $user->id)->first(['id', 'password_hash']);

        if (! $stored || ! Hash::check($validated['password'], (string) $stored->password_hash)) {
            throw ValidationException::withMessages([
                'password' => 'The password is not correct.',
            ]);
        }

        $anonymisedEmail = 'deleted-'.$user->id.'-'.now()->timestamp.'@deleted.naijabuilders.com';

        DB::table('users')->where('id', $user->id)->update([
            'full_name' => 'Deleted Account',
            'email' => $anonymisedEmail,
            'username' => 'deleted_'.$user->id,
            'phone' => null,
            'company' => null,
            'location' => null,
            'profile_image_path' => null,
            'password_hash' => Hash::make((string) random_int(PHP_INT_MIN, PHP_INT_MAX)),
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'updated_at' => now(),
        ]);

        DB::table('personal_access_tokens')
            ->where('tokenable_type', \App\Models\User::class)
            ->where('tokenable_id', $user->id)
            ->delete();

        if (DB::getSchemaBuilder()->hasTable('device_tokens')) {
            DB::table('device_tokens')->where('user_id', $user->id)->delete();
        }

        return response()->json([
            'message' => 'Your account has been deleted.',
        ]);
    }
}
