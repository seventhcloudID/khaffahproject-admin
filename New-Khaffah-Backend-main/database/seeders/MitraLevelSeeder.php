<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MitraLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['nama_level' => 'Bronze', 'persen_potongan' => 2, 'urutan' => 1, 'is_active' => true],
            ['nama_level' => 'Silver', 'persen_potongan' => 5, 'urutan' => 2, 'is_active' => true],
            ['nama_level' => 'Gold', 'persen_potongan' => 8, 'urutan' => 3, 'is_active' => true],
            ['nama_level' => 'Platinum', 'persen_potongan' => 12, 'urutan' => 4, 'is_active' => true],
        ];

        foreach ($levels as $level) {
            DB::table('mitra_level_m')->updateOrInsert(
                ['nama_level' => $level['nama_level']],
                array_merge($level, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
