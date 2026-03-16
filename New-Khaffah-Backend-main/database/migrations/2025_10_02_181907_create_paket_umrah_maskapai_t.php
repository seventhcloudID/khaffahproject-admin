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
        //
        Schema::create('paket_umrah_maskapai_t', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->foreignId('paket_umrah_id')->constrained('paket_umrah_m')->onDelete('cascade');
            $table->foreignId('maskapai_id')->constrained('maskapai_m')->onDelete('cascade');
            $table->foreignId('kelas_penerbangan_id')->constrained('kelas_penerbangan_m')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['paket_umrah_id', 'maskapai_id'], 'uniq_paket_umrah_maskapai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_umrah_maskapai_t');
    }
};
