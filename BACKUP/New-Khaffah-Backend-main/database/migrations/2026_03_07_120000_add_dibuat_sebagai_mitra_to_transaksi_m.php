<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pembelian dari mitra (buat pesanan di dashboard mitra) jangan digabung dengan
     * daftar transaksi user. User list hanya tampilkan transaksi biasa; mitra list
     * hanya tampilkan transaksi yang dibuat sebagai mitra.
     */
    public function up(): void
    {
        Schema::table('transaksi_m', function (Blueprint $table) {
            $table->boolean('dibuat_sebagai_mitra')->default(false)->after('akun_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_m', function (Blueprint $table) {
            $table->dropColumn('dibuat_sebagai_mitra');
        });
    }
};
