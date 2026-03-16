<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Master layanan untuk halaman paket request: Pelayanan Wajib & Layanan Tambahan.
     */
    public function up(): void
    {
        Schema::create('layanan_section_m', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 20); // wajib | tambahan
            $table->string('judul', 255);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('layanan_paket_request_m', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->decimal('harga', 14, 0)->default(0);
            $table->string('satuan', 50)->default('/pax'); // /pax, /orang, /hari
            $table->string('jenis', 20); // wajib | tambahan
            $table->unsignedInteger('urutan')->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan_paket_request_m');
        Schema::dropIfExists('layanan_section_m');
    }
};
