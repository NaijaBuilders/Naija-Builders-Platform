<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('buyer_identity_verifications')) {
            Schema::create('buyer_identity_verifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('provider', 40)->default('fake');
                $table->string('provider_reference', 191)->nullable()->index();
                $table->string('verified_id_name', 150)->nullable();
                $table->string('document_type', 40)->index();
                $table->string('status', 32)->default('pending')->index();
                $table->string('document_path', 255)->nullable();
                $table->string('selfie_path', 255)->nullable();
                $table->unsignedSmallInteger('face_match_score')->nullable();
                $table->boolean('liveness_passed')->nullable();
                $table->json('normalized_result')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('buyer_fraud_events')) {
            Schema::create('buyer_fraud_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
                $table->unsignedTinyInteger('tier')->default(1)->index();
                $table->string('signal_type', 60)->index();
                $table->string('trigger_level', 20)->default('passive')->index();
                $table->unsignedSmallInteger('score')->default(0);
                $table->string('signal_hash', 128)->nullable()->index();
                $table->string('signal_display', 80)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('captured_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('buyer_review_audits')) {
            Schema::create('buyer_review_audits', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('operator_name', 150)->nullable();
                $table->string('action', 60)->index();
                $table->string('previous_status', 32)->nullable();
                $table->string('new_status', 32)->nullable()->index();
                $table->string('trigger_level', 20)->nullable()->index();
                $table->json('reason_codes')->nullable();
                $table->text('notes')->nullable();
                $table->text('buyer_message')->nullable();
                $table->timestamp('deadline_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_delivery_photos')) {
            Schema::create('order_delivery_photos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('path', 255);
                $table->timestamp('captured_at')->nullable()->index();
                $table->decimal('gps_lat', 10, 7)->nullable();
                $table->decimal('gps_lng', 10, 7)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_disputes')) {
            Schema::create('order_disputes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reason');
                $table->string('status', 32)->default('open')->index();
                $table->string('outcome', 80)->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_disputes');
        Schema::dropIfExists('order_delivery_photos');
        Schema::dropIfExists('buyer_review_audits');
        Schema::dropIfExists('buyer_fraud_events');
        Schema::dropIfExists('buyer_identity_verifications');
    }
};
