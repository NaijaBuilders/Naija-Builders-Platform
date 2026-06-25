<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('addresses')) {
            return;
        }

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 20);   // user | organization
            $table->unsignedBigInteger('owner_id');
            $table->string('label', 80)->nullable();        // e.g. "Lekki site", "Head office"
            $table->string('contact_name', 120)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('state', 80)->nullable();
            $table->string('lga', 80)->nullable();
            $table->string('address', 255);
            $table->text('instructions')->nullable();        // site access notes
            $table->string('default_window', 60)->nullable(); // preferred delivery window
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
