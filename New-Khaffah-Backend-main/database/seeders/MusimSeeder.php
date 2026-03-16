<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MusimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now()->toDateTimeString();

        
        $musim = [
            [
                'is_active' => true,
                'nama_musim' => 'Awal Musim',
            ],
            [
                'is_active' => true,
                'nama_musim' => 'Pertengahan Musim',
            ],
            [
                'is_active' => true,
                'nama_musim' => 'Akhir Musim',
            ],
        ];

        DB::table('musim_m')->insert($musim);
    }
}
