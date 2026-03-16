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
        Schema::create('paket_umrah_perlengkapan_t', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_head')->default(false);
            $table->foreignId('paket_umrah_id')->constrained('paket_umrah_m')->cascadeOnDelete();
            $table->foreignId('perlengkapan_id')->constrained('perlengkapan_m')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_umrah_perlengkapan_t');
    }
};
