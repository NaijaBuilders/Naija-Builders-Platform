<?php

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
        $hadKycStatus = Schema::hasColumn('users', 'kyc_status');

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'kyc_status')) {
                $table->string('kyc_status', 20)->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'kyc_submitted_at')) {
                $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_status');
            }

            if (!Schema::hasColumn('users', 'kyc_verified_at')) {
                $table->timestamp('kyc_verified_at')->nullable()->after('kyc_submitted_at');
            }
        });

        if (!$hadKycStatus) {
            DB::table('users')->where('role', 'supplier')->update(['kyc_status' => 'approved']);
            DB::table('users')->where('role', 'builder')->update(['kyc_status' => 'approved']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'kyc_verified_at')) {
                $table->dropColumn('kyc_verified_at');
            }

            if (Schema::hasColumn('users', 'kyc_submitted_at')) {
                $table->dropColumn('kyc_submitted_at');
            }

            if (Schema::hasColumn('users', 'kyc_status')) {
                $table->dropColumn('kyc_status');
            }
        });
    }
};
