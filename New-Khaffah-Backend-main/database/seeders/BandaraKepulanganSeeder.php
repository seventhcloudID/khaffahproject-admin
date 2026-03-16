<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BandaraKepulanganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'JED', 'nama' => 'King Abdulaziz International Airport (Jeddah)', 'urutan' => 1, 'is_active' => true],
            ['kode' => 'MED', 'nama' => 'Prince Mohammad bin Abdulaziz International Airport (Madinah)', 'urutan' => 2, 'is_active' => true],
            ['kode' => 'TIF', 'nama' => 'Taif Regional Airport (Taif)', 'urutan' => 3, 'is_active' => true],
        ];

        foreach ($data as $row) {
            $exists = DB::table('bandara_kepulangan_m')->where('kode', $row['kode'])->exists();
            if (! $exists) {
                DB::table('bandara_kepulangan_m')->insert(array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
