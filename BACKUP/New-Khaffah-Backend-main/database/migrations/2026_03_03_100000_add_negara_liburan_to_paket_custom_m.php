<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Untuk paket "Umrah Plus Liburan": daftar negara tujuan liburan (contoh: Turki, Mesir, Yordania).
     */
    public function up(): void
    {
        Schema::table('paket_custom_m', function (Blueprint $table) {
            $table->json('negara_liburan')->nullable()->after('tipe_paket')->comment('Daftar negara liburan; hanya dipakai jika tipe_paket = Umrah Plus Liburan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paket_custom_m', function (Blueprint $table) {
            $table->dropColumn('negara_liburan');
        });
    }
};
