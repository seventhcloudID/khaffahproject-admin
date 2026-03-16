<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dataMasterId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Data Master')->value('id');
        if (!$dataMasterId) return;

        $exists = DB::table('sub_modul_aplikasi_s')
            ->where('modul_id', $dataMasterId)
            ->where('url', '/Paket/Master-Keberangkatan')
            ->exists();
        if (!$exists) {
            DB::table('sub_modul_aplikasi_s')->insert([
                'modul_id' => $dataMasterId,
                'is_active' => true,
                'urutan' => 3,
                'nama_sub_modul' => 'Master Keberangkatan',
                'url' => '/Paket/Master-Keberangkatan',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-location-dot',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('sub_modul_aplikasi_s')
            ->where('url', '/Paket/Master-Keberangkatan')
            ->delete();
    }
};
