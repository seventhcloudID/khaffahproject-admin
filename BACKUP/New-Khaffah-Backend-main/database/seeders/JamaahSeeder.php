<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JamaahSeeder extends Seeder
{
    public function run(): void
    {
        // akun_id = 3 (as requested). Insert a few sample jamaah.
        $now = now();

        DB::table('jamaah_m')->insert([
            [
                'is_active' => true,
                'akun_id' => 3,
                'nama_lengkap' => 'Krisna Yogantara',
                'nomor_identitas' => '3201123456789012',
                'nomor_passpor' => 'A1234567',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'akun_id' => 3,
                'nama_lengkap' => 'Siti Nurjanah',
                'nomor_identitas' => '3201987654321098',
                'nomor_passpor' => 'B9876543',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'akun_id' => 2,
                'nama_lengkap' => 'Reza Pratama',
                'nomor_identitas' => '3201234567890123',
                'nomor_passpor' => 'A1234568',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
