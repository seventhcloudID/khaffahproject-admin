<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubroleModulAplikasiSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan tabel pivot
        DB::table('subrole_modul_aplikasi_s')->truncate();

        // Ambil subroles
        $subroles = DB::table('subrole_m')->pluck('id', 'nama_role');

        // Ambil modul
        $moduls = DB::table('modul_aplikasi_s')->pluck('id', 'nama_modul');

        // Akuntansi
        DB::table('subrole_modul_aplikasi_s')->insert([
            'sub_role_id' => $subroles['akutansi'],
            'modul_id'   => $moduls['Akuntansi'],
        ]);

        // Manajemen
        DB::table('subrole_modul_aplikasi_s')->insert([
            'sub_role_id' => $subroles['manajemen'],
            'modul_id'   => $moduls['Manajemen'],
        ]);

        // Support
        DB::table('subrole_modul_aplikasi_s')->insert([
            'sub_role_id' => $subroles['support'],
            'modul_id'   => $moduls['Support'],
        ]);
    }
}
