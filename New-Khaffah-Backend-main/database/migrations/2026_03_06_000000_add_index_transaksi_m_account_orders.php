<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index untuk mempercepat query list pesanan user (account/orders).
     * Query: WHERE akun_id = ? AND is_active = true ORDER BY created_at DESC LIMIT n
     */
    public function up(): void
    {
        Schema::table('transaksi_m', function (Blueprint $table) {
            $table->index(['akun_id', 'is_active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_m', function (Blueprint $table) {
            $table->dropIndex(['akun_id', 'is_active', 'created_at']);
        });
    }
};
