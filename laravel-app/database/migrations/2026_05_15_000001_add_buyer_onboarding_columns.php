<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'registration_ip_hash')) {
                $table->string('registration_ip_hash', 128)->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'registration_ip_display')) {
                $table->string('registration_ip_display', 80)->nullable();
            }
            if (! Schema::hasColumn('users', 'device_fingerprint_hash')) {
                $table->string('device_fingerprint_hash', 128)->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'device_fingerprint_display')) {
                $table->string('device_fingerprint_display', 80)->nullable();
            }
            if (! Schema::hasColumn('users', 'email_otp_hash')) {
                $table->string('email_otp_hash', 255)->nullable();
            }
            if (! Schema::hasColumn('users', 'email_otp_expires_at')) {
                $table->timestamp('email_otp_expires_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'phone_otp_hash')) {
                $table->string('phone_otp_hash', 255)->nullable();
            }
            if (! Schema::hasColumn('users', 'phone_otp_expires_at')) {
                $table->timestamp('phone_otp_expires_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'first_transaction_monitoring')) {
                $table->boolean('first_transaction_monitoring')->default(true)->index();
            }
            if (! Schema::hasColumn('users', 'first_successful_order_id')) {
                $table->unsignedBigInteger('first_successful_order_id')->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'first_transaction_completed_at')) {
                $table->timestamp('first_transaction_completed_at')->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'reference')) {
                $table->string('reference', 40)->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'verification_tier')) {
                $table->unsignedTinyInteger('verification_tier')->default(1)->index();
            }
            if (! Schema::hasColumn('orders', 'verification_status')) {
                $table->string('verification_status', 32)->default('approved')->index();
            }
            if (! Schema::hasColumn('orders', 'review_status')) {
                $table->string('review_status', 32)->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'fraud_score')) {
                $table->unsignedSmallInteger('fraud_score')->default(0)->index();
            }
            if (! Schema::hasColumn('orders', 'fraud_trigger_level')) {
                $table->string('fraud_trigger_level', 20)->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'fraud_triggers')) {
                $table->json('fraud_triggers')->nullable();
            }
            if (! Schema::hasColumn('orders', 'payment_provider')) {
                $table->string('payment_provider', 30)->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'payment_method_type')) {
                $table->string('payment_method_type', 40)->nullable();
            }
            if (! Schema::hasColumn('orders', 'payment_currency')) {
                $table->string('payment_currency', 3)->default('NGN');
            }
            if (! Schema::hasColumn('orders', 'payment_amount')) {
                $table->decimal('payment_amount', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('orders', 'gateway_risk_level')) {
                $table->string('gateway_risk_level', 20)->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'gateway_risk_metadata')) {
                $table->json('gateway_risk_metadata')->nullable();
            }
            if (! Schema::hasColumn('orders', 'is_first_transaction')) {
                $table->boolean('is_first_transaction')->default(false)->index();
            }
            if (! Schema::hasColumn('orders', 'monitoring_flag')) {
                $table->boolean('monitoring_flag')->default(false)->index();
            }
            if (! Schema::hasColumn('orders', 'billing_name')) {
                $table->string('billing_name', 150)->nullable();
            }
            if (! Schema::hasColumn('orders', 'billing_country')) {
                $table->string('billing_country', 2)->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'card_country')) {
                $table->string('card_country', 2)->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'delivery_address')) {
                $table->string('delivery_address', 255)->nullable();
            }
            if (! Schema::hasColumn('orders', 'delivery_country')) {
                $table->string('delivery_country', 2)->default('NG')->index();
            }
            if (! Schema::hasColumn('orders', 'recipient_name')) {
                $table->string('recipient_name', 150)->nullable();
            }
            if (! Schema::hasColumn('orders', 'recipient_phone')) {
                $table->string('recipient_phone', 40)->nullable();
            }
            if (! Schema::hasColumn('orders', 'recipient_relationship')) {
                $table->string('recipient_relationship', 80)->nullable();
            }
            if (! Schema::hasColumn('orders', 'delivery_status')) {
                $table->string('delivery_status', 32)->default('pending')->index();
            }
            if (! Schema::hasColumn('orders', 'delivery_otp_hash')) {
                $table->string('delivery_otp_hash', 255)->nullable();
            }
            if (! Schema::hasColumn('orders', 'delivery_otp_generated_at')) {
                $table->timestamp('delivery_otp_generated_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'delivery_otp_confirmed_at')) {
                $table->timestamp('delivery_otp_confirmed_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'delivery_gps_lat')) {
                $table->decimal('delivery_gps_lat', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('orders', 'delivery_gps_lng')) {
                $table->decimal('delivery_gps_lng', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('orders', 'dispute_window_ends_at')) {
                $table->timestamp('dispute_window_ends_at')->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'dispute_status')) {
                $table->string('dispute_status', 32)->default('none')->index();
            }
            if (! Schema::hasColumn('orders', 'dispute_outcome')) {
                $table->string('dispute_outcome', 80)->nullable();
            }
            if (! Schema::hasColumn('orders', 'escrow_release_at')) {
                $table->timestamp('escrow_release_at')->nullable()->index();
            }
            if (! Schema::hasColumn('orders', 'manual_review_deadline_at')) {
                $table->timestamp('manual_review_deadline_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach ([
                'reference',
                'verification_tier',
                'verification_status',
                'review_status',
                'fraud_score',
                'fraud_trigger_level',
                'fraud_triggers',
                'payment_provider',
                'payment_method_type',
                'payment_currency',
                'payment_amount',
                'gateway_risk_level',
                'gateway_risk_metadata',
                'is_first_transaction',
                'monitoring_flag',
                'billing_name',
                'billing_country',
                'card_country',
                'delivery_address',
                'delivery_country',
                'recipient_name',
                'recipient_phone',
                'recipient_relationship',
                'delivery_status',
                'delivery_otp_hash',
                'delivery_otp_generated_at',
                'delivery_otp_confirmed_at',
                'delivery_gps_lat',
                'delivery_gps_lng',
                'dispute_window_ends_at',
                'dispute_status',
                'dispute_outcome',
                'escrow_release_at',
                'manual_review_deadline_at',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            foreach ([
                'registration_ip_hash',
                'registration_ip_display',
                'device_fingerprint_hash',
                'device_fingerprint_display',
                'email_otp_hash',
                'email_otp_expires_at',
                'email_verified_at',
                'phone_otp_hash',
                'phone_otp_expires_at',
                'phone_verified_at',
                'first_transaction_monitoring',
                'first_successful_order_id',
                'first_transaction_completed_at',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
