<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prembly_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('token', 191)->unique();
            $table->string('event_hash', 128);
            $table->string('provider_reference', 191)->nullable()->index();
            $table->foreignId('supplier_application_id')->nullable()->constrained('supplier_applications')->nullOnDelete();
            $table->string('status', 32)->default('received')->index();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prembly_webhook_events');
    }
};
