<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BandaraKeberangkatanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'CGK', 'nama' => 'Soekarno-Hatta (Jakarta)', 'urutan' => 1, 'is_active' => true],
            ['kode' => 'SUB', 'nama' => 'Juanda (Surabaya)', 'urutan' => 2, 'is_active' => true],
            ['kode' => 'JOG', 'nama' => 'Adisutjipto (Yogyakarta)', 'urutan' => 3, 'is_active' => true],
        ];

        foreach ($data as $row) {
            $exists = DB::table('bandara_keberangkatan_m')->where('kode', $row['kode'])->exists();
            if (!$exists) {
                DB::table('bandara_keberangkatan_m')->insert(array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
