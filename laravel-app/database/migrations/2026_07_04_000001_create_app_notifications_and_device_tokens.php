<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('app_notifications')) {
            Schema::create('app_notifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('event_key', 60)->index();
                $table->string('title', 191);
                $table->string('body', 500)->nullable();
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'read_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('token', 191)->unique();
                $table->string('platform', 20)->default('unknown');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('password_reset_codes')) {
            Schema::create('password_reset_codes', function (Blueprint $table): void {
                $table->id();
                $table->string('email', 191)->index();
                $table->string('code_hash');
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('app_notifications');
    }
};
