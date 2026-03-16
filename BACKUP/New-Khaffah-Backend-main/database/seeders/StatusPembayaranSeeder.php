<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatusPembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('status_pembayaran_m')->insert([
            [
                'is_active' => true,
                'kode' => 'BELUM_BAYAR',
                'nama_status' => 'Belum Bayar',
                'deskripsi' => 'Transaksi belum dilakukan pembayaran sama sekali.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode' => 'DP',
                'nama_status' => 'Sudah Bayar DP',
                'deskripsi' => 'Transaksi sudah membayar uang muka (down payment), belum lunas.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode' => 'CICIL',
                'nama_status' => 'Cicilan Berjalan',
                'deskripsi' => 'Pembayaran dilakukan bertahap sesuai kemampuan jamaah.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode' => 'LUNAS',
                'nama_status' => 'Lunas',
                'deskripsi' => 'Semua tagihan sudah dibayar, siap diproses lebih lanjut.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'kode' => 'REFUND',
                'nama_status' => 'Refund',
                'deskripsi' => 'Transaksi dibatalkan dan dana dikembalikan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
