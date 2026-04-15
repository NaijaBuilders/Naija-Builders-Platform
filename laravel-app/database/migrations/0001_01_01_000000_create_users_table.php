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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('email', 191)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('company', 191)->nullable();
            $table->string('business_category', 120)->nullable();
            $table->string('location', 120)->nullable();
            $table->string('business_address', 255)->nullable();
            $table->text('business_description')->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('account_number', 40)->nullable();
            $table->enum('role', ['builder', 'supplier', 'admin'])->default('builder');
            $table->string('password_hash', 255);
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
