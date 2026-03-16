<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaketCustomSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('paket_custom_m')->insert([
            'nama_paket' => 'Umrah Private - Paket Custom',
            'tipe_paket' => 'Umrah Private',
            'jumlah_hari' => 9,
            'estimasi_biaya' => 38500000.00,
            'deskripsi' => 'Paket Umrah Private dengan jadwal fleksibel sesuai permintaan. Hotel pilihan di Mekkah & Madinah, maskapai full service. Cocok untuk keluarga atau grup kecil yang menginginkan perjalanan ibadah yang nyaman dan terpersonalisasi.',
            'catatan_internal' => 'Contoh paket untuk katalog. Harga estimasi per pax; penawaran final setelah konsultasi.',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
