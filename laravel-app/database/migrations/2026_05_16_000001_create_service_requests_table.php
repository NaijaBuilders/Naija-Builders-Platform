<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'offers_services')) {
                $table->boolean('offers_services')->default(false)->after('role')->index();
            }

            if (! Schema::hasColumn('users', 'service_category')) {
                $table->string('service_category', 80)->nullable()->after('offers_services')->index();
            }

            if (! Schema::hasColumn('users', 'service_areas')) {
                $table->string('service_areas')->nullable()->after('service_category');
            }
        });

        if (! Schema::hasTable('service_requests')) {
            Schema::create('service_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('service_type', 80)->index();
                $table->string('project_title');
                $table->string('project_location');
                $table->text('project_description');
                $table->string('budget_range', 80)->nullable();
                $table->date('preferred_start_date')->nullable();
                $table->string('contact_name');
                $table->string('contact_phone', 40);
                $table->string('contact_email');
                $table->string('status', 40)->default('new')->index();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');

        Schema::table('users', function (Blueprint $table): void {
            foreach (['service_areas', 'service_category', 'offers_services'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
