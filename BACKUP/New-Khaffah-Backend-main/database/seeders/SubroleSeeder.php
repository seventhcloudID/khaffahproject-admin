<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubroleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('subrole_m')->insert([
            [
                'nama_role'     => 'superadmin',
                'deskripsi'     => 'Akses penuh untuk mengelola aplikasi admin',
                'url_dashboard' => '/dashboard',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_role'     => 'admin',
                'deskripsi'     => 'Akses penuh untuk mengelola aplikasi admin',
                'url_dashboard' => '/dashboard',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_role'     => 'akutansi',
                'deskripsi'     => 'Mengelola data keuangan dan laporan',
                'url_dashboard' => '/dashboard-akutansi',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_role'     => 'manajemen',
                'deskripsi'     => 'Memantau operasional dan melakukan approval',
                'url_dashboard' => '/dashboard-manajemen',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'nama_role'     => 'support',
                'deskripsi'     => 'Menangani bantuan dan kebutuhan teknis',
                'url_dashboard' => '/dashboard-support',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}
