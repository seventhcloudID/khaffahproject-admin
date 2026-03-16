<?php

namespace Database\Seeders;

use App\Models\LayananPaketRequest;
use App\Models\LayananSection;
use Illuminate\Database\Seeder;

class LayananPaketRequestSeeder extends Seeder
{
    public function run(): void
    {
        LayananSection::insert([
            [
                'jenis'     => 'wajib',
                'judul'     => 'Pelayanan Yang Wajib Anda Pesan',
                'deskripsi' => 'Pilih paket makan yang paling sesuai dengan kebutuhan Anda selama di Tanah Suci.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jenis'     => 'tambahan',
                'judul'     => 'Layanan Tambahan Yang Anda Butuhkan',
                'deskripsi' => 'Pilih layanan tambahan yang dapat menunjang kenyamanan dan kelancaran perjalanan ibadah Anda.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $wajib = [
            ['nama' => 'Visa dan Pasport Umrah', 'harga' => 350000, 'satuan' => '/pax'],
            ['nama' => 'Transportasi', 'harga' => 500000, 'satuan' => '/pax'],
            ['nama' => 'Set Perlengkapan Lengkap', 'harga' => 350000, 'satuan' => '/pax'],
        ];
        foreach ($wajib as $i => $row) {
            LayananPaketRequest::create([
                'nama'      => $row['nama'],
                'harga'     => $row['harga'],
                'satuan'    => $row['satuan'],
                'jenis'     => LayananPaketRequest::JENIS_WAJIB,
                'urutan'    => $i + 1,
                'is_active' => true,
            ]);
        }

        $tambahan = [
            ['nama' => 'Asuransi', 'harga' => 275000, 'satuan' => '/orang'],
            ['nama' => 'Mutawwif', 'harga' => 1000000, 'satuan' => '/hari'],
            ['nama' => 'Tour Leader', 'harga' => 1000000, 'satuan' => '/hari'],
            ['nama' => 'Handling Bandara', 'harga' => 1000000, 'satuan' => '/orang'],
            ['nama' => 'Badal Umrah', 'harga' => 350000, 'satuan' => '/pax'],
            ['nama' => 'Manasik Online / Offline', 'harga' => 1000000, 'satuan' => '/orang'],
            ['nama' => 'Khalaqah Ilmu', 'harga' => 1000000, 'satuan' => '/orang'],
            ['nama' => 'Kereta Cepat', 'harga' => 1000000, 'satuan' => '/orang'],
        ];
        foreach ($tambahan as $i => $row) {
            LayananPaketRequest::create([
                'nama'      => $row['nama'],
                'harga'     => $row['harga'],
                'satuan'    => $row['satuan'],
                'jenis'     => LayananPaketRequest::JENIS_TAMBAHAN,
                'urutan'    => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
