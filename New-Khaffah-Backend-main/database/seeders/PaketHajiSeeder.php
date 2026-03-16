<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaketHajiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('paket_haji_m')->insert([
            [
                'is_active' => true,
                'nama_paket' => 'Paket Haji Plus Ekonomis',
                'biaya_per_pax' => 145000000, // Rp145 juta
                'deskripsi' => 'Paket Haji Plus Ekonomis dengan fasilitas lengkap dan akomodasi bintang 4, cocok bagi jamaah yang mengutamakan kenyamanan dengan biaya terjangkau.',

                'akomodasi' => json_encode([
                    [
                        'id_kota' => 2,
                        'kota' => 'Madinah',
                        'id_hotel' => 2,
                        'hotel' => 'Al Haram Dar Al Eiman',
                        'rating_hotel' => 4,
                        'jarak_ke_masjid' => '350 meter',
                        'fasilitas_hotel' => ['AC', 'WiFi', 'Restoran']
                    ],
                    [
                        'id_kota' => 1,
                        'kota' => 'Mekkah',
                        'id_hotel' => 1,
                        'hotel' => 'Al Ghufran Safwah',
                        'rating_hotel' => 4,
                        'jarak_ke_masjid' => '300 meter',
                        'fasilitas_hotel' => ['AC', 'WiFi', 'Shuttle']
                    ]
                ]),
                'deskripsi_akomodasi' => 'Menginap di hotel bintang 4 di Mekkah dan Madinah, berjarak sekitar 300-350 meter dari masjid utama dengan fasilitas lengkap dan pelayanan profesional.',

                'waktu_tunggu_min' => 5,
                'waktu_tunggu_max' => 7,
                'deskripsi_waktu_tunggu' => 'Perkiraan waktu tunggu keberangkatan antara 5 hingga 7 tahun, tergantung pada kuota dan regulasi pemerintah.',

                'fasilitas_tambahan' => json_encode([
                    ['fasilitas_id' => 1, 'nama_fasilitas' => 'Bimbingan Manasik', 'icon_id' => 3],
                    ['fasilitas_id' => 2, 'nama_fasilitas' => 'Perlengkapan Ibadah', 'icon_id' => 5],
                    ['fasilitas_id' => 3, 'nama_fasilitas' => 'Asuransi Perjalanan', 'icon_id' => 8]
                ]),
                'deskripsi_fasilitas' => 'Fasilitas mencakup bimbingan manasik pra-keberangkatan, perlengkapan ibadah lengkap, dan asuransi perjalanan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'is_active' => true,
                'nama_paket' => 'Paket Haji VIP Premium',
                'biaya_per_pax' => 295000000, // Rp295 juta
                'deskripsi' => 'Paket Haji VIP dengan akomodasi bintang 5, layanan eksklusif, dan waktu tunggu lebih singkat.',

                'akomodasi' => json_encode([
                    [
                        'id_kota' => 2,
                        'kota' => 'Madinah',
                        'id_hotel' => 3,
                        'hotel' => 'Millenium Al Aqeeq',
                        'rating_hotel' => 5,
                        'jarak_ke_masjid' => '100 meter',
                        'fasilitas_hotel' => ['AC', 'WiFi', 'Breakfast', 'Shuttle']
                    ],
                    [
                        'id_kota' => 1,
                        'kota' => 'Mekkah',
                        'id_hotel' => 2,
                        'hotel' => 'Pulman Zam Zam',
                        'rating_hotel' => 5,
                        'jarak_ke_masjid' => '80 meter',
                        'fasilitas_hotel' => ['AC', 'WiFi', 'Restoran', 'View Ka\'bah']
                    ]
                ]),
                'deskripsi_akomodasi' => 'Hotel bintang 5 dengan jarak sangat dekat ke Masjid Nabawi dan Masjidil Haram, layanan concierge 24 jam, dan menu makanan Indonesia premium.',

                'waktu_tunggu_min' => 4,
                'waktu_tunggu_max' => 5,
                'deskripsi_waktu_tunggu' => 'Waktu tunggu diperkirakan hanya 4 hingga 5 tahun berkat kuota prioritas untuk paket VIP.',

                'fasilitas_tambahan' => json_encode([
                    ['fasilitas_id' => 1, 'nama_fasilitas' => 'Manasik Eksklusif', 'icon_id' => 3],
                    ['fasilitas_id' => 2, 'nama_fasilitas' => 'Asuransi Premium', 'icon_id' => 8],
                    ['fasilitas_id' => 4, 'nama_fasilitas' => 'Makan Buffet Hotel', 'icon_id' => 12],
                    ['fasilitas_id' => 5, 'nama_fasilitas' => 'Ziarah Plus Thaif', 'icon_id' => 15]
                ]),
                'deskripsi_fasilitas' => 'Layanan VIP termasuk manasik eksklusif, asuransi premium, buffet hotel internasional, serta perjalanan ziarah tambahan ke Thaif.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
