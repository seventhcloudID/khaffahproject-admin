<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiUmrahSeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil data master yang diperlukan
        $jenisTransaksiId = DB::table('jenis_transaksi_m')->where('kode', 'PAKET_UMRAH')->value('id');
        $paketUmrahId = DB::table('paket_umrah_m')->where('is_active', true)->value('id');
        $keberangkatanIds = DB::table('paket_umrah_keberangkatan_t')
            ->where('paket_umrah_id', $paketUmrahId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
        $tipeIds = DB::table('paket_umrah_tipe_m')
            ->where('paket_umrah_id', $paketUmrahId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        // Status pembayaran & transaksi untuk variasi
        $statusPembayaranIds = DB::table('status_pembayaran_m')
            ->where('is_active', true)
            ->pluck('id', 'kode')
            ->toArray();

        $statusTransaksiIds = DB::table('status_transaksi_m')
            ->where('is_active', true)
            ->pluck('id', 'kode')
            ->toArray();

        // Data lokasi untuk pemesan
        $provinsiId = DB::table('provinsi_m')->value('id');
        $kotaId = DB::table('kota_m')->where('nama_kota', 'Jakarta')->value('id');
        $kecamatanId = DB::table('kecamatan_m')->value('id');

        // Validasi data master
        if (!$jenisTransaksiId || !$paketUmrahId || empty($keberangkatanIds) || empty($tipeIds)) {
            throw new \Exception('Data master (jenis transaksi, paket umrah, keberangkatan, atau tipe) tidak ditemukan. Pastikan PaketUmrahSeeder sudah berjalan.');
        }

        // Daftar nama dan nomor WA untuk sample transaksi
        $namaSamples = [
            'Ahmad Santoso',
            'Siti Nurhaliza',
            'Budi Rahmat',
            'Nur Fathimah',
            'Reza Perdana',
            'Aisyah Putri',
            'Muhammad Rizki',
            'Dewi Lestari',
            'Joni Pratama',
            'Elsa Saputri',
        ];

        $noWaSamples = [
            '08123456789',
            '08234567890',
            '08345678901',
            '08456789012',
            '08567890123',
            '08678901234',
            '08789012345',
            '08890123456',
            '08901234567',
            '08112345678',
        ];

        // Variasi status untuk membuat data yang diverse
        $statusVariasi = [
            // Status pembayaran BELUM_BAYAR + berbagai status transaksi
            [
                'status_pembayaran' => 'BELUM_BAYAR',
                'status_transaksi' => 'BELUM_DIHUBUNGI',
            ],
            [
                'status_pembayaran' => 'BELUM_BAYAR',
                'status_transaksi' => 'DIHUBUNGI',
            ],
            [
                'status_pembayaran' => 'BELUM_BAYAR',
                'status_transaksi' => 'MENUNGGU_PEMBAYARAN',
            ],
            // Status pembayaran MENUNGGU_VERIFIKASI
            [
                'status_pembayaran' => 'MENUNGGU_VERIFIKASI',
                'status_transaksi' => 'MENUNGGU_VERIFIKASI_PEMBAYARAN',
            ],
            // Status pembayaran DITOLAK
            [
                'status_pembayaran' => 'DITOLAK',
                'status_transaksi' => 'PEMBAYARAN_DITOLAK',
            ],
            // Status pembayaran LUNAS dengan berbagai status transaksi
            [
                'status_pembayaran' => 'LUNAS',
                'status_transaksi' => 'DIPROSES',
            ],
            [
                'status_pembayaran' => 'LUNAS',
                'status_transaksi' => 'TERKONFIRMASI',
            ],
            [
                'status_pembayaran' => 'LUNAS',
                'status_transaksi' => 'SIAP_BERANGKAT',
            ],
            [
                'status_pembayaran' => 'LUNAS',
                'status_transaksi' => 'BERANGKAT',
            ],
            [
                'status_pembayaran' => 'LUNAS',
                'status_transaksi' => 'SELESAI',
            ],
            // Status lainnya
            [
                'status_pembayaran' => 'REFUND',
                'status_transaksi' => 'DIBATALKAN',
            ],
            [
                'status_pembayaran' => 'LUNAS',
                'status_transaksi' => 'REFUND_DIAJUKAN',
            ],
        ];

        // Buat 12 transaksi dengan berbagai kombinasi status
        foreach ($statusVariasi as $index => $statusCombo) {
            $keberangkatanId = $keberangkatanIds[array_rand($keberangkatanIds)];
            $tipeId = $tipeIds[array_rand($tipeIds)];
            $namaIndex = $index % count($namaSamples);
            $namaPemesan = $namaSamples[$namaIndex];
            $noWa = $noWaSamples[$namaIndex];

            // Ambil data tipe paket untuk snapshot
            $tipeData = DB::table('paket_umrah_tipe_m')
                ->where('id', $tipeId)
                ->where('paket_umrah_id', $paketUmrahId)
                ->first();

            $paketData = DB::table('paket_umrah_m')
                ->where('id', $paketUmrahId)
                ->first();

            $keberangkatanData = DB::table('paket_umrah_keberangkatan_t')
                ->where('id', $keberangkatanId)
                ->first();

            // Snapshot produk
            $snapshot = [
                'id' => (int) $paketData->id,
                'nama_paket' => $paketData->nama_paket,
                'musim_id' => (int) $paketData->musim_id,
                'paket_umrah_tipe_id' => (int) $tipeId,
                'harga_per_pax' => (float) $tipeData->harga_per_pax,
            ];

            // Jamaah data (bisa 1 atau 2 orang)
            $jumlahJamaah = rand(1, 2);
            $jamaahData = [];
            for ($j = 0; $j < $jumlahJamaah; $j++) {
                $jamaahData[] = [
                    'id' => null,
                    'nama' => $namaPemesan . ($j > 0 ? ' (Keluarga)' : ''),
                    'nik' => (string) (1234567890123456 + $j + $index),
                    'no_paspor' => 'A' . str_pad($index * 10 + $j + 1, 7, '0', STR_PAD_LEFT),
                ];
            }

            $totalBiaya = (float) $tipeData->harga_per_pax * $jumlahJamaah;

            // Generate kode transaksi
            $tanggal = $now->format('dmy');
            $autoIncrement = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $kodeTransaksi = 'UMRAH-' . $tanggal . '-' . $autoIncrement;

            // Generate nomor pembayaran unik
            $nomorPembayaran = null;
            if ($statusCombo['status_pembayaran'] !== 'BELUM_BAYAR') {
                $nomorPembayaran = 'NP-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            }

            // Insert transaksi
            $transaksiId = DB::table('transaksi_m')->insertGetId([
                'is_active' => true,
                'gelar_id' => null,
                'nama_lengkap' => $namaPemesan,
                'no_whatsapp' => $noWa,
                'provinsi_id' => $provinsiId,
                'kota_id' => $kotaId,
                'kecamatan_id' => $kecamatanId,
                'alamat_lengkap' => 'Jl. Merdeka No. ' . ($index + 1) . ', Jakarta',
                'deskripsi' => 'Transaksi sample umrah untuk testing',
                'jenis_transaksi_id' => $jenisTransaksiId,
                'produk_id' => $paketUmrahId,
                'keberangkatan_id' => $keberangkatanId,
                'snapshot_produk' => json_encode($snapshot),
                'jamaah_data' => json_encode($jamaahData),
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => true,
                'total_biaya' => $totalBiaya,
                'status_pembayaran_id' => $statusPembayaranIds[$statusCombo['status_pembayaran']] ?? null,
                'status_transaksi_id' => $statusTransaksiIds[$statusCombo['status_transaksi']] ?? null,
                'nomor_pembayaran' => $nomorPembayaran,
                'tanggal_transaksi' => $now->copy()->subDays(rand(0, 30)),
                'created_at' => $now->copy()->subDays(rand(0, 30)),
                'updated_at' => $now->copy()->subDays(rand(0, 5)),
            ]);

            // Insert pembayaran transaksi jika bukan BELUM_BAYAR
            if ($statusCombo['status_pembayaran'] !== 'BELUM_BAYAR') {
                $kodePembayaran = rand(100, 999);
                $nominalAsli = (int) $totalBiaya;
                $nominalTransfer = $nominalAsli + $kodePembayaran;

                $statusPembayaran = match ($statusCombo['status_pembayaran']) {
                    'MENUNGGU_VERIFIKASI' => 'pending',
                    'DITOLAK' => 'rejected',
                    'LUNAS' => 'verified',
                    'REFUND' => 'refunded',
                    default => 'pending',
                };

                $verified = $statusPembayaran === 'verified';

                DB::table('pembayaran_transaksi_m')->insert([
                    'is_active' => true,
                    'transaksi_id' => $transaksiId,
                    'nomor_pembayaran' => $nomorPembayaran,
                    'kode_unik' => $kodePembayaran,
                    'nominal_asli' => $nominalAsli,
                    'nominal_transfer' => $nominalTransfer,
                    'moota_reference' => $verified ? 'MOOTA-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT) : null,
                    'bank_pengirim' => $verified ? 'BCA' : null,
                    'nama_pengirim' => $verified ? $namaPemesan : null,
                    'tanggal_transfer' => $verified ? $now->copy()->subDays(rand(1, 15)) : null,
                    'status' => $statusPembayaran,
                    'validasi_manual' => $verified ? true : false,
                    'verified_by' => $verified ? 1 : null,
                    'verified_at' => $verified ? $now->copy()->subDays(rand(1, 10)) : null,
                    'expired_at' => $statusPembayaran === 'pending' ? $now->addDay() : null,
                    'created_at' => $now->copy()->subDays(rand(5, 30)),
                    'updated_at' => $now->copy()->subDays(rand(0, 5)),
                ]);
            }
        }

        $this->command->info('✓ 12 Transaksi Umrah dengan berbagai status berhasil dibuat!');
    }
}
