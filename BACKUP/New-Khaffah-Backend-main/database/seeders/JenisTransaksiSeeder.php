<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JenisTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('jenis_transaksi_m')->insert([
            [
                'is_active' => true,
                'kode' => 'PAKET_UMRAH',
                'nama_jenis' => 'Paket Umrah',
                'deskripsi' => 'Transaksi untuk pemesanan paket umrah reguler dan tipe-tipe variannya.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode' => 'HAJI',
                'nama_jenis' => 'Paket Haji',
                'deskripsi' => 'Transaksi untuk paket haji reguler maupun haji plus.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode' => 'EDUTRIP',
                'nama_jenis' => 'Paket EduTrip',
                'deskripsi' => 'Transaksi untuk perjalanan edukatif ke luar negeri atau dalam negeri.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode' => 'REQUEST',
                'nama_jenis' => 'Custom / Request Paket',
                'deskripsi' => 'Transaksi untuk permintaan paket custom seperti Umrah Private, Land Arrangement, dan lainnya.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
