<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type', 20);   // user | branch | organization
            $table->unsignedBigInteger('scope_id');
            $table->string('namespace', 60);     // e.g. notifications, marketplace, privacy
            $table->string('key', 80);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['scope_type', 'scope_id', 'namespace', 'key'], 'settings_scope_unique');
            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
