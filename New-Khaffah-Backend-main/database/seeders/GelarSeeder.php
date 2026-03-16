<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GelarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now()->toDateTimeString();

        
        $gelar = [
            [
                'is_active' => true,
                'gelar' => 'Bapak',
            ],
            [
                'is_active' => true,
                'gelar' => 'Ibu',
            ],
        ];

        DB::table('gelar_m')->insert($gelar);
    }
}
