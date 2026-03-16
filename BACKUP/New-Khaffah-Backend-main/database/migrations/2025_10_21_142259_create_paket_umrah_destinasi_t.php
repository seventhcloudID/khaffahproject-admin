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
        Schema::create('paket_umrah_destinasi_t', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->integer('durasi')->nullable();
            $table->foreignId('paket_umrah_id')->constrained('paket_umrah_m')->cascadeOnDelete();
            $table->foreignId('kota_id')->constrained('kota_m')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_umrah_destinasi_t');
    }
};
