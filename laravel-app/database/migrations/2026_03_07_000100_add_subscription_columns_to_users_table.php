<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'subscription_plan')) {
                $table->string('subscription_plan', 40)->default('standard')->after('role');
            }

            if (!Schema::hasColumn('users', 'subscription_started_at')) {
                $table->timestamp('subscription_started_at')->nullable()->after('subscription_plan');
            }

            if (!Schema::hasColumn('users', 'is_verified_badge')) {
                $table->boolean('is_verified_badge')->default(false)->after('subscription_started_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_verified_badge')) {
                $table->dropColumn('is_verified_badge');
            }

            if (Schema::hasColumn('users', 'subscription_started_at')) {
                $table->dropColumn('subscription_started_at');
            }

            if (Schema::hasColumn('users', 'subscription_plan')) {
                $table->dropColumn('subscription_plan');
            }
        });
    }
};
