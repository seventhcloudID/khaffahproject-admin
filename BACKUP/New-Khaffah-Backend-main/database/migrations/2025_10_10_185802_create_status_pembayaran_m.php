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
        Schema::create('status_pembayaran_m', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            
            $table->string('kode', 100)->unique()->comment('kode unik, misal: BELUM_BAYAR, DP, LUNAS');
            $table->string('nama_status', 255)->comment('nama status pembayaran');
            $table->text('deskripsi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_pembayaran_m');
    }
};
