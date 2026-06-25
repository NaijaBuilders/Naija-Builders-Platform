<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_preferences')) {
            return;
        }

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('event_key', 60);
            $table->boolean('push')->default(true);
            $table->boolean('email')->default(false);
            $table->boolean('sms')->default(false);
            $table->boolean('in_app')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'event_key']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
