<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pisahkan Master Hotel & Master Maskapai dari menu Paket ke menu baru "Data Master".
     */
    public function up(): void
    {
        // Hapus submodul Master Hotel & Master Maskapai dari Paket (atau dari mana pun)
        DB::table('sub_modul_aplikasi_s')
            ->whereIn('url', ['/Paket/Master-Hotel', '/Paket/Master-Maskapai'])
            ->delete();

        // Cek apakah modul "Data Master" sudah ada
        $dataMasterId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Data Master')->value('id');

        if (!$dataMasterId) {
            $maxUrutan = DB::table('modul_aplikasi_s')->max('urutan') ?? 0;
            $dataMasterId = DB::table('modul_aplikasi_s')->insertGetId([
                'is_active' => true,
                'urutan' => $maxUrutan + 1,
                'nama_modul' => 'Data Master',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-database',
            ]);
        }

        // Tambah submodul Master Hotel & Master Maskapai di bawah Data Master
        $exists = DB::table('sub_modul_aplikasi_s')
            ->where('modul_id', $dataMasterId)
            ->where('url', '/Paket/Master-Hotel')
            ->exists();

        if (!$exists) {
            DB::table('sub_modul_aplikasi_s')->insert([
                [
                    'modul_id' => $dataMasterId,
                    'is_active' => true,
                    'urutan' => 1,
                    'nama_sub_modul' => 'Master Hotel',
                    'url' => '/Paket/Master-Hotel',
                    'icon_id' => null,
                    'fa_icon_class' => 'fa-solid fa-hotel',
                ],
                [
                    'modul_id' => $dataMasterId,
                    'is_active' => true,
                    'urutan' => 2,
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
        $dataMasterId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Data Master')->value('id');
        if ($dataMasterId) {
            DB::table('sub_modul_aplikasi_s')
                ->where('modul_id', $dataMasterId)
                ->whereIn('url', ['/Paket/Master-Hotel', '/Paket/Master-Maskapai'])
                ->delete();
        }

        // Kembalikan ke bawah Paket
        $paketId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Paket')->value('id');
        if ($paketId) {
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
};
