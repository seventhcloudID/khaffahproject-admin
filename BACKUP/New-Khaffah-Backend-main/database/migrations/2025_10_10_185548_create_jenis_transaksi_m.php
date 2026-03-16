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
        Schema::create('jenis_transaksi_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            
            $table->string('kode', 100)->unique()->comment('kode unik, misal: UMRAH, HAJI, EDUTRIP, REQUEST');
            $table->string('nama_jenis', 255)->comment('nama lengkap jenis transaksi');
            $table->text('deskripsi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_transaksi_m');
    }
};
