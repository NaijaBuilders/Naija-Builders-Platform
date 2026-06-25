<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_requests') && ! Schema::hasColumn('service_requests', 'service_provider_id')) {
            Schema::table('service_requests', function (Blueprint $table): void {
                $table->foreignId('service_provider_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->index(['service_provider_id', 'status']);
            });
        }

        if (! Schema::hasTable('service_provider_projects')) {
            Schema::create('service_provider_projects', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
                $table->string('service_type', 80)->nullable()->index();
                $table->string('title');
                $table->string('location', 191)->nullable();
                $table->enum('status', ['completed', 'ongoing'])->default('completed')->index();
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();

                $table->index(['provider_id', 'display_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_provider_projects');

        if (Schema::hasTable('service_requests') && Schema::hasColumn('service_requests', 'service_provider_id')) {
            Schema::table('service_requests', function (Blueprint $table): void {
                $table->dropIndex(['service_provider_id', 'status']);
                $table->dropConstrainedForeignId('service_provider_id');
            });
        }
    }
};
