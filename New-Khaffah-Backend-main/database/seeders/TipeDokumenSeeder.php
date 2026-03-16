<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipeDokumenSeeder extends Seeder
{
    public function run()
    {
        DB::table('tipe_dokumen_m')->insert([
            ['kode' => 'ktp', 'nama' => 'KTP', 'is_active' => true],
            ['kode' => 'paspor', 'nama' => 'Paspor', 'is_active' => true],
            ['kode' => 'npwp', 'nama' => 'NPWP', 'is_active' => true],
            ['kode' => 'ppiu', 'nama' => 'Dokumen PPIU', 'is_active' => true],
            ['kode' => 'pihk', 'nama' => 'Dokumen PIHK', 'is_active' => true],
            ['kode' => 'ijin_usaha', 'nama' => 'Ijin Usaha', 'is_active' => true],
        ]);
    }
}

