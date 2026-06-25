<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('owner_type', 20);   // user | organization
                $table->unsignedBigInteger('owner_id');
                $table->string('type', 20);          // card | bank | virtual_account
                $table->string('label', 120)->nullable();
                $table->string('brand', 40)->nullable();   // visa | mastercard | verve | bank name
                $table->string('last4', 8)->nullable();
                $table->string('token', 255)->nullable();  // gateway token reference (never raw PAN)
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['owner_type', 'owner_id']);
            });
        }

        if (! Schema::hasTable('payout_accounts')) {
            Schema::create('payout_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');     // supplier / service provider
                $table->string('bank_name', 120);
                $table->string('bank_code', 20)->nullable();
                $table->string('account_number_masked', 30);
                $table->string('account_name', 191)->nullable();
                $table->boolean('verified')->default(false);
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_accounts');
        Schema::dropIfExists('payment_methods');
    }
};
