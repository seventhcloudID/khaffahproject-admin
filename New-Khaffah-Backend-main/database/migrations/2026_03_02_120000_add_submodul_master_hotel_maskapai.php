<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $paketId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Paket')->value('id');
        if (!$paketId) {
            return;
        }

        $exists = DB::table('sub_modul_aplikasi_s')
            ->where('modul_id', $paketId)
            ->where('url', '/Paket/Master-Hotel')
            ->exists();

        if (!$exists) {
            DB::table('sub_modul_aplikasi_s')->insert([
                [
                    'modul_id' => $paketId,
                    'is_active' => true,
                    'urutan' => 5,
                    'nama_sub_modul' => 'Master Hotel',
                    'url' => '/Paket/Master-Hotel',
                    'icon_id' => null,
                    'fa_icon_class' => 'fa-solid fa-hotel',
                ],
                [
                    'modul_id' => $paketId,
                    'is_active' => true,
                    'urutan' => 6,
                    'nama_sub_modul' => 'Master Maskapai',
                    'url' => '/Paket/Master-Maskapai',
                    'icon_id' => null,
                    'fa_icon_class' => 'fa-solid fa-plane',
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $paketId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Paket')->value('id');
        if ($paketId) {
            DB::table('sub_modul_aplikasi_s')
                ->where('modul_id', $paketId)
                ->whereIn('url', ['/Paket/Master-Hotel', '/Paket/Master-Maskapai'])
                ->delete();
        }
    }
};
