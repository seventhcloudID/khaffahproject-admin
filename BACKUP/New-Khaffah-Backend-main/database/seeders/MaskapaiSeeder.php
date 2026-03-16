<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaskapaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now()->toDateTimeString();

        $maskapai = [
            [
                'is_active' => true,
                'kode_iata' => 'GA',
                'nama_maskapai' => 'Garuda Indonesia',
                'negara_asal' => 'Indonesia',
                'logo_url' => 'https://via.placeholder.com/150?text=Garuda+Indonesia',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode_iata' => 'SV',
                'nama_maskapai' => 'Saudia',
                'negara_asal' => 'Saudi Arabia',
                'logo_url' => 'https://via.placeholder.com/150?text=Saudia',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode_iata' => 'JT',
                'nama_maskapai' => 'Lion Air',
                'negara_asal' => 'Indonesia',
                'logo_url' => 'https://via.placeholder.com/150?text=Lion+Air',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode_iata' => 'QG',
                'nama_maskapai' => 'Citylink',
                'negara_asal' => 'Indonesia',
                'logo_url' => 'https://via.placeholder.com/150?text=Citylink',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode_iata' => 'WY',
                'nama_maskapai' => 'Oman Air',
                'negara_asal' => 'Oman',
                'logo_url' => 'https://via.placeholder.com/150?text=Oman+Air',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode_iata' => 'EK',
                'nama_maskapai' => 'Emirates',
                'negara_asal' => 'United Arab Emirates',
                'logo_url' => 'https://via.placeholder.com/150?text=Emirates',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode_iata' => 'EY',
                'nama_maskapai' => 'Etihad Airways',
                'negara_asal' => 'United Arab Emirates',
                'logo_url' => 'https://via.placeholder.com/150?text=Etihad',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode_iata' => '6E',
                'nama_maskapai' => 'IndiGo',
                'negara_asal' => 'India',
                'logo_url' => 'https://via.placeholder.com/150?text=IndiGo',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('maskapai_m')->insert($maskapai);
    }
}
