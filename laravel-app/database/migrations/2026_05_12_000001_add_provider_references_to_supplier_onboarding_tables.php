<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_checks', function (Blueprint $table): void {
            if (! Schema::hasColumn('verification_checks', 'provider_reference')) {
                $table->string('provider_reference', 191)->nullable()->after('provider')->index();
            }
        });

        Schema::table('verification_decisions', function (Blueprint $table): void {
            if (! Schema::hasColumn('verification_decisions', 'provider')) {
                $table->string('provider', 40)->nullable()->after('new_status');
            }
            if (! Schema::hasColumn('verification_decisions', 'provider_references')) {
                $table->json('provider_references')->nullable()->after('provider');
            }
        });

        Schema::table('supplier_audit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('supplier_audit_logs', 'provider')) {
                $table->string('provider', 40)->nullable()->after('new_status');
            }
            if (! Schema::hasColumn('supplier_audit_logs', 'provider_references')) {
                $table->json('provider_references')->nullable()->after('provider');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_audit_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('supplier_audit_logs', 'provider_references')) {
                $table->dropColumn('provider_references');
            }
            if (Schema::hasColumn('supplier_audit_logs', 'provider')) {
                $table->dropColumn('provider');
            }
        });

        Schema::table('verification_decisions', function (Blueprint $table): void {
            if (Schema::hasColumn('verification_decisions', 'provider_references')) {
                $table->dropColumn('provider_references');
            }
            if (Schema::hasColumn('verification_decisions', 'provider')) {
                $table->dropColumn('provider');
            }
        });

        Schema::table('verification_checks', function (Blueprint $table): void {
            if (Schema::hasColumn('verification_checks', 'provider_reference')) {
                $table->dropColumn('provider_reference');
            }
        });
    }
};
