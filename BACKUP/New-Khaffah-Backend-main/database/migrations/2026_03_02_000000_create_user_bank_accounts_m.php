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
        Schema::create('user_bank_accounts_m', function (Blueprint $table) {
            $table->id();
            $table->foreignId('akun_id')->constrained('users')->cascadeOnDelete();
            $table->string('bank_name', 100);
            $table->string('account_number', 30);
            $table->string('account_holder_name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['akun_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bank_accounts_m');
    }
};
