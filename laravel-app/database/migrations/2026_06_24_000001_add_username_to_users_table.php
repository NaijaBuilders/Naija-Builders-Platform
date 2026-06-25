<?php

use App\Support\Username;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 30)->nullable()->unique()->after('email');
            });
        }

        $this->backfillUsernames();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_username_unique');
                $table->dropColumn('username');
            });
        }
    }

    /**
     * Give every existing user a unique username derived from their email
     * local part (falling back to their name) so login-by-username works
     * immediately for accounts created before this column existed.
     */
    private function backfillUsernames(): void
    {
        $taken = DB::table('users')
            ->whereNotNull('username')
            ->pluck('username')
            ->map(static fn ($value) => mb_strtolower((string) $value))
            ->all();
        $taken = array_flip($taken);

        DB::table('users')
            ->where(function ($query) {
                $query->whereNull('username')->orWhere('username', '');
            })
            ->orderBy('id')
            ->select(['id', 'email', 'full_name'])
            ->each(function ($user) use (&$taken) {
                $seed = (string) $user->email;
                $atPos = strpos($seed, '@');
                if ($atPos !== false) {
                    $seed = substr($seed, 0, $atPos);
                }
                if (trim($seed) === '') {
                    $seed = (string) $user->full_name;
                }

                $username = Username::generate($seed, static fn (string $candidate): bool => isset($taken[$candidate]));
                $taken[$username] = true;

                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            });
    }
};
