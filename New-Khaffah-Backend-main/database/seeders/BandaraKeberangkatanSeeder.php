<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BandaraKeberangkatanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'CGK', 'nama' => 'Soekarno-Hatta International Airport (Jakarta/Tangerang)', 'urutan' => 1, 'is_active' => true],
            ['kode' => 'SUB', 'nama' => 'Juanda International Airport (Surabaya)', 'urutan' => 2, 'is_active' => true],
            ['kode' => 'UPG', 'nama' => 'Sultan Hasanuddin International Airport (Makassar)', 'urutan' => 3, 'is_active' => true],
            ['kode' => 'KNO', 'nama' => 'Kualanamu International Airport (Medan)', 'urutan' => 4, 'is_active' => true],
            ['kode' => 'SOC', 'nama' => 'Adi Soemarmo International Airport (Solo)', 'urutan' => 5, 'is_active' => true],
            ['kode' => 'YIA', 'nama' => 'Yogyakarta International Airport (Yogyakarta)', 'urutan' => 6, 'is_active' => true],
            ['kode' => 'KJT', 'nama' => 'Kertajati International Airport (Majalengka/Jawa Barat)', 'urutan' => 7, 'is_active' => true],
            ['kode' => 'PDG', 'nama' => 'Minangkabau International Airport (Padang)', 'urutan' => 8, 'is_active' => true],
            ['kode' => 'PLM', 'nama' => 'Sultan Mahmud Badaruddin II International Airport (Palembang)', 'urutan' => 9, 'is_active' => true],
            ['kode' => 'BTH', 'nama' => 'Hang Nadim International Airport (Batam)', 'urutan' => 10, 'is_active' => true],
            ['kode' => 'BTJ', 'nama' => 'Sultan Iskandar Muda International Airport (Banda Aceh)', 'urutan' => 11, 'is_active' => true],
            ['kode' => 'BDJ', 'nama' => 'Syamsudin Noor International Airport (Banjarmasin)', 'urutan' => 12, 'is_active' => true],
            ['kode' => 'BPN', 'nama' => 'Sepinggan International Airport (Balikpapan)', 'urutan' => 13, 'is_active' => true],
            ['kode' => 'LOP', 'nama' => 'Zainuddin Abdul Madjid International Airport (Lombok)', 'urutan' => 14, 'is_active' => true],
        ];

        foreach ($data as $row) {
            $exists = DB::table('bandara_keberangkatan_m')->where('kode', $row['kode'])->exists();
            if (!$exists) {
                DB::table('bandara_keberangkatan_m')->insert(array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
