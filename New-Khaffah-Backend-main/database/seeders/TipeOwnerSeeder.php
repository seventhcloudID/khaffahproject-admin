<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipeOwnerSeeder extends Seeder
{
    public function run()
    {
        DB::table('tipe_owner_m')->insert([
            ['kode' => 'jamaah', 'nama' => 'Jamaah', 'is_active' => true],
            ['kode' => 'mitra', 'nama' => 'Mitra', 'is_active' => true],
            ['kode' => 'transaksi', 'nama' => 'Transaksi', 'is_active' => true],
        ]);
    }
}

