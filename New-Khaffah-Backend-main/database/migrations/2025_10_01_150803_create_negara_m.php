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
        Schema::create('negara_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('nama_negara')->unique();
            $table->string('kode_iso2', 2)->nullable();
            $table->string('kode_iso3', 3)->nullable();
            $table->string('kode_telepon', 10)->nullable();
            $table->string('mata_uang', 50)->nullable();
            $table->string('kode_mata_uang', 10)->nullable();
            $table->string('benua', 50)->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negara_m');
    }
};
