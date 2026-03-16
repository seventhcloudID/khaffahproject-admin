<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paket_umrah_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('nama_paket', 255);
            $table->text('deskripsi')->nullable();
            $table->foreignId('musim_id')->nullable()->constrained('musim_m')->nullOnDelete();
            $table->foreignId('lokasi_keberangkatan_id')->constrained('kota_m')->cascadeOnDelete();
            $table->foreignId('lokasi_tujuan_id')->constrained('kota_m')->cascadeOnDelete();

            // Durasi total (gabungan semua kota)
            $table->integer('durasi_total')->nullable();
            $table->integer('jumlah_pax')->nullable();

            // --- Cache untuk kecepatan query halaman utama ---
            $table->decimal('harga_termurah', 15, 2)->nullable();
            $table->decimal('harga_termahal', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_umrah_m');
    }
};
