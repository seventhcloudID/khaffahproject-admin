<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            [
                'kode' => 'pending',
                'nama_status' => 'Pending',
            ],
            [
                'kode' => 'sedang_direview',
                'nama_status' => 'Sedang Direview',
            ],
            [
                'kode' => 'diproses',
                'nama_status' => 'Diproses',
            ],
            [
                'kode' => 'disetujui',
                'nama_status' => 'Disetujui',
            ],
            [
                'kode' => 'ditolak',
                'nama_status' => 'Ditolak',
            ],
            [
                'kode' => 'valid',
                'nama_status' => 'Valid',
            ],
            [
                'kode' => 'tidak_valid',
                'nama_status' => 'Tidak Valid',
            ],
        ];

        foreach ($data as &$row) {
            $row['is_active'] = true;
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('status_m')->insert($data);
    }
}
