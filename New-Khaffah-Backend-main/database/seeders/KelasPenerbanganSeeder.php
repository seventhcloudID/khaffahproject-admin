<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KelasPenerbanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now()->toDateTimeString();

        
        $kelasPenerbangan = [
            [
                'is_active' => true,
                'kelas_penerbangan' => 'Ekonomi',
            ],
            [
                'is_active' => true,
                'kelas_penerbangan' => 'Ekonomi Premium',
            ],
            [
                'is_active' => true,
                'kelas_penerbangan' => 'Bisnis',
            ],
            [
                'is_active' => true,
                'kelas_penerbangan' => 'Utama',
            ],
        ];

        DB::table('kelas_penerbangan_m')->insert($kelasPenerbangan);
    }
}
