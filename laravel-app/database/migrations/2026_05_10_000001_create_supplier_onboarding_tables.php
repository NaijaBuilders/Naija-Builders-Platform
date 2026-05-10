<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('DRAFT')->index();
            $table->string('current_stage', 40)->default('ACCOUNT');
            $table->string('provider', 40)->default('fake');

            $table->string('cac_number', 40)->nullable();
            $table->string('cac_number_hash', 128)->nullable()->index();
            $table->string('business_name', 191)->nullable();
            $table->string('business_type', 80)->nullable();
            $table->string('business_address', 255)->nullable();
            $table->string('state', 80)->nullable();
            $table->string('contact_name', 150)->nullable();
            $table->string('contact_email', 191)->nullable();
            $table->string('contact_phone', 40)->nullable();

            $table->string('bvn_hash', 128)->nullable()->index();
            $table->string('bvn_mask', 20)->nullable();
            $table->string('nin_hash', 128)->nullable()->index();
            $table->string('nin_mask', 20)->nullable();
            $table->string('id_document_type', 40)->nullable();
            $table->string('id_document_path', 255)->nullable();
            $table->string('selfie_path', 255)->nullable();
            $table->unsignedSmallInteger('face_match_score')->nullable();
            $table->boolean('liveness_passed')->nullable();

            $table->string('bank_name', 120)->nullable();
            $table->string('bank_code', 30)->nullable();
            $table->string('account_number_hash', 128)->nullable()->index();
            $table->string('account_number_mask', 20)->nullable();
            $table->string('account_name', 191)->nullable();
            $table->unsignedSmallInteger('name_match_score')->nullable();

            $table->string('supplier_message', 255)->nullable();
            $table->text('more_info_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_application_id')->constrained('supplier_applications')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('check_type', 60)->index();
            $table->string('status', 32)->index();
            $table->json('reason_codes')->nullable();
            $table->json('normalized_result')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_application_id')->constrained('supplier_applications')->cascadeOnDelete();
            $table->string('decision', 32)->index();
            $table->string('previous_status', 32)->nullable();
            $table->string('new_status', 32)->index();
            $table->json('triggered_checks')->nullable();
            $table->json('internal_reason_codes')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_application_id')->nullable()->constrained('supplier_applications')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->string('decision', 32)->nullable()->index();
            $table->string('previous_status', 32)->nullable();
            $table->string('new_status', 32)->nullable();
            $table->json('triggered_checks')->nullable();
            $table->json('internal_reason_codes')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('risk_signals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_application_id')->nullable()->constrained('supplier_applications')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signal_type', 40)->index();
            $table->string('signal_hash', 128)->index();
            $table->string('signal_display', 80)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_review_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_application_id')->constrained('supplier_applications')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_review_notes');
        Schema::dropIfExists('risk_signals');
        Schema::dropIfExists('supplier_audit_logs');
        Schema::dropIfExists('verification_decisions');
        Schema::dropIfExists('verification_checks');
        Schema::dropIfExists('supplier_applications');
    }
};
