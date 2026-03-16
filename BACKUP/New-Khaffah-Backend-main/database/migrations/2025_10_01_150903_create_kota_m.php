<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kota_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('nama_kota', 100);

            $table->foreignId('provinsi_id')
                ->constrained('provinsi_m')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            
            // Relasi ke master negara
            $table->foreignId('negara_id')
                ->constrained('negara_m')
                ->onUpdate('cascade')
                ->onDelete('restrict'); // biar gak kehapus kalau negara dipakai

            $table->string('kode_iata', 10)->nullable(); // kode bandara / kota (misal: JED)
            $table->string('zona_waktu', 50)->nullable(); // contoh: Asia/Riyadh

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kota_m');
    }
};

