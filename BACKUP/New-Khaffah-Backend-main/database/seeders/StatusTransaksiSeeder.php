<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatusTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            [
                'kode' => 'BELUM_DIHUBUNGI',
                'nama_status' => 'Belum Dihubungi',
                'deskripsi' => 'Transaksi dibuat dan menunggu follow up dari admin.',
            ],
            [
                'kode' => 'DIHUBUNGI',
                'nama_status' => 'Dihubungi',
                'deskripsi' => 'Pelanggan telah dihubungi dan menunggu konfirmasi lebih lanjut.',
            ],
            [
                'kode' => 'MENUNGGU_PEMBAYARAN',
                'nama_status' => 'Menunggu Pembayaran',
                'deskripsi' => 'Transaksi menunggu pembayaran DP atau pembayaran penuh.',
            ],
            [
                'kode' => 'MENUNGGU_VERIFIKASI_PEMBAYARAN',
                'nama_status' => 'Menunggu Verifikasi Pembayaran',
                'deskripsi' => 'Pelanggan telah mengirim bukti pembayaran dan menunggu verifikasi admin.',
            ],
            [
                'kode' => 'PEMBAYARAN_DITOLAK',
                'nama_status' => 'Pembayaran Ditolak',
                'deskripsi' => 'Bukti pembayaran tidak valid atau tidak sesuai, perlu upload ulang.',
            ],
            [
                'kode' => 'DIPROSES',
                'nama_status' => 'Diproses',
                'deskripsi' => 'Pembayaran sudah divalidasi, transaksi sedang diproses menuju keberangkatan.',
            ],
            [
                'kode' => 'TERKONFIRMASI',
                'nama_status' => 'Terkonfirmasi',
                'deskripsi' => 'Admin telah mengonfirmasi seluruh data dan keberangkatan telah terjadwal.',
            ],
            [
                'kode' => 'SIAP_BERANGKAT',
                'nama_status' => 'Siap Berangkat',
                'deskripsi' => 'Semua sudah beres, jamaah tinggal menunggu hari keberangkatan.',
            ],
            [
                'kode' => 'BERANGKAT',
                'nama_status' => 'Berangkat',
                'deskripsi' => 'Jamaah sedang dalam perjalanan umrah.',
            ],
            [
                'kode' => 'PULANG',
                'nama_status' => 'Pulang',
                'deskripsi' => 'Jamaah telah kembali ke Indonesia.',
            ],
            [
                'kode' => 'SELESAI',
                'nama_status' => 'Selesai',
                'deskripsi' => 'Transaksi dan program umrah telah selesai sepenuhnya.',
            ],
            [
                'kode' => 'DIBATALKAN',
                'nama_status' => 'Dibatalkan',
                'deskripsi' => 'Transaksi dibatalkan oleh jamaah atau admin.',
            ],
            [
                'kode' => 'REFUND_DIAJUKAN',
                'nama_status' => 'Refund Diajukan',
                'deskripsi' => 'Pengajuan refund sedang diproses oleh admin.',
            ],
        ];

        foreach ($data as &$row) {
            $row['is_active'] = true;
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('status_transaksi_m')->insert($data);
    }
}
