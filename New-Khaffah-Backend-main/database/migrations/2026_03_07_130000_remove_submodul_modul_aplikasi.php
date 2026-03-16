<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sub_modul_aplikasi_s')
            ->where('nama_sub_modul', 'Modul Aplikasi')
            ->where('url', '/Sistem-Admin/Modul-Aplikasi')
            ->delete();
    }

    public function down(): void
    {
        $sistemAdminId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Sistem Admin')->value('id');
        if (!$sistemAdminId) return;

        DB::table('sub_modul_aplikasi_s')->insert([
            'modul_id' => $sistemAdminId,
            'is_active' => true,
            'urutan' => 1,
            'nama_sub_modul' => 'Modul Aplikasi',
            'url' => '/Sistem-Admin/Modul-Aplikasi',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-layer-group',
        ]);
    }
};
