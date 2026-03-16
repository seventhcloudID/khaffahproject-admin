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
        Schema::table('transaksi_m', function (Blueprint $table) {
            // akun_id will reference users.id so we can link transactions to accounts
            $table->foreignId('akun_id')->nullable()->constrained('users')->nullOnDelete()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_m', function (Blueprint $table) {
            // remove foreign key and column
            // dropConstrainedForeignId is available on recent Laravel versions
            if (method_exists($table, 'dropConstrainedForeignId')) {
                $table->dropConstrainedForeignId('akun_id');
            } else {
                $table->dropForeign(['akun_id']);
                $table->dropColumn('akun_id');
            }
        });
    }
};
