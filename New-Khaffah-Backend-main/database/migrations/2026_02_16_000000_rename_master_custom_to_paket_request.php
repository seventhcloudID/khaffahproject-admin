<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update submodul dari Master-Custom ke Paket Request (untuk support halaman product-request).
     */
    public function up(): void
    {
        DB::table('sub_modul_aplikasi_s')
            ->where('url', '/Paket/Master-Custom')
            ->update([
                'nama_sub_modul' => 'Paket Request',
                'url' => '/paket-request',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sub_modul_aplikasi_s')
            ->where('url', '/paket-request')
            ->update([
                'nama_sub_modul' => 'Paket Custom',
                'url' => '/Paket/Master-Custom',
                'updated_at' => now(),
            ]);
    }
};
