<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaketEdutripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        $paketEdutrip = [
            [
                'nama_paket' => 'Paket Edutrip 20 Hari',
                'jumlah_hari' => 20,
                'deskripsi' => 'Paket kunjungan edukatif selama 20 hari dengan program pembelajaran intensif dan kunjungan ke berbagai destinasi edukasi. Cocok untuk program pertukaran pelajar atau study tour jangka panjang.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_paket' => 'Paket Edutrip 30 Hari',
                'jumlah_hari' => 30,
                'deskripsi' => 'Paket kunjungan edukatif selama 30 hari dengan program komprehensif mencakup pembelajaran, pelatihan, dan kunjungan ke berbagai pusat edukasi. Ideal untuk program immersion dan pengembangan kompetensi jangka panjang.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('paket_edutrip_m')->insert($paketEdutrip);
    }
}