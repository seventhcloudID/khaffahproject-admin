<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket_custom_m', function (Blueprint $table) {
            $table->string('tipe_paket', 100)->nullable()->after('nama_paket')->comment('Contoh: Umrah Private, Land Arrangement');
            $table->integer('jumlah_hari')->nullable()->after('tipe_paket');
            $table->decimal('estimasi_biaya', 15, 2)->nullable()->after('jumlah_hari')->comment('Estimasi biaya per pax (rupiah)');
            $table->text('catatan_internal')->nullable()->after('deskripsi')->comment('Catatan untuk admin');
        });
    }

    public function down(): void
    {
        Schema::table('paket_custom_m', function (Blueprint $table) {
            $table->dropColumn(['tipe_paket', 'jumlah_hari', 'estimasi_biaya', 'catatan_internal']);
        });
    }
};
