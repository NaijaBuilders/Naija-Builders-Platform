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
        if (!Schema::hasTable('materials') || Schema::hasColumn('materials', 'is_negotiable')) {
            return;
        }

        Schema::table('materials', function (Blueprint $table): void {
            $table->boolean('is_negotiable')->default(false)->after('price_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('materials') || !Schema::hasColumn('materials', 'is_negotiable')) {
            return;
        }

        Schema::table('materials', function (Blueprint $table): void {
            $table->dropColumn('is_negotiable');
        });
    }
};
