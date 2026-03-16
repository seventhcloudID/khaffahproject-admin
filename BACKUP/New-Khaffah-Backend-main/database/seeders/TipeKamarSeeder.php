<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TipeKamarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now()->toDateTimeString();

        
        $tipeKamar = [
            [
                'is_active' => true,
                'tipe_kamar' => 'NonBed',
                'kapasitas' => 1,
            ],
            [
                'is_active' => true,
                'tipe_kamar' => 'Double',
                'kapasitas' => 2,
            ],
            [
                'is_active' => true,
                'tipe_kamar' => 'Triple',
                'kapasitas' => 3,
            ],
            [
                'is_active' => true,
                'tipe_kamar' => 'Quad',
                'kapasitas' => 4,
            ],
            [
                'is_active' => true,
                'tipe_kamar' => 'Quint',
                'kapasitas' => 5,
            ],
        ];

        DB::table('tipe_kamar_m')->insert($tipeKamar);
    }
}
