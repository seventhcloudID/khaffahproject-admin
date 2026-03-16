<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_m', function (Blueprint $table) {
            $table->text('refund_alasan')->nullable()->after('status_transaksi_id');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_alasan');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_m', function (Blueprint $table) {
            $table->dropColumn(['refund_alasan', 'refund_requested_at']);
        });
    }
};
