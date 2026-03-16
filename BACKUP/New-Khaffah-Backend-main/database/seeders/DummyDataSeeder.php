<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Mitra;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $this->command->info('🚀 Membuat dummy data...');

        // 1. Buat lebih banyak user aktif
        $this->createUsers($now);
        
        // 2. Buat lebih banyak mitra
        $this->createMitras($now);
        
        // 3. Buat lebih banyak transaksi Umrah
        $this->createTransaksiUmrah($now);
        
        // 4. Buat transaksi Haji
        $this->createTransaksiHaji($now);
        
        // 5. Buat transaksi Edutrip
        $this->createTransaksiEdutrip($now);

        $this->command->info('✅ Dummy data berhasil dibuat!');
    }

    private function createUsers($now)
    {
        $this->command->info('📝 Membuat user aktif...');
        
        $users = [
            ['Ahmad Fauzi', 'ahmad.fauzi@example.com'],
            ['Siti Rahayu', 'siti.rahayu@example.com'],
            ['Budi Pratama', 'budi.pratama@example.com'],
            ['Dewi Lestari', 'dewi.lestari@example.com'],
            ['Muhammad Rizki', 'muhammad.rizki@example.com'],
            ['Nur Aisyah', 'nur.aisyah@example.com'],
            ['Reza Perdana', 'reza.perdana@example.com'],
            ['Indah Sari', 'indah.sari@example.com'],
            ['Joko Widodo', 'joko.widodo@example.com'],
            ['Sinta Dewi', 'sinta.dewi@example.com'],
        ];

        $count = 0;
        foreach ($users as $index => $userData) {
            // Generate nomor handphone yang unik
            $noHandphone = '08' . str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
            
            // Pastikan nomor handphone unik
            while (User::where('no_handphone', $noHandphone)->exists()) {
                $noHandphone = '08' . str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
            }

            $user = User::firstOrCreate(
                ['email' => $userData[1]],
                [
                    'nama_lengkap' => $userData[0],
                    'jenis_kelamin' => rand(0, 1) ? 'laki-laki' : 'perempuan',
                    'tgl_lahir' => $now->copy()->subYears(rand(20, 50))->format('Y-m-d'),
                    'no_handphone' => $noHandphone,
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'created_at' => $now->copy()->subDays(rand(1, 90)),
                ]
            );
            
            if (!$user->hasRole('user')) {
                $user->assignRole('user');
            }
            $count++;
        }

        $this->command->info('✅ ' . $count . ' user aktif berhasil dibuat');
    }

    private function createMitras($now)
    {
        $this->command->info('🤝 Membuat mitra...');
        
        $statusIds = DB::table('status_m')
            ->where('is_active', true)
            ->pluck('id', 'kode')
            ->toArray();

        $mitras = [
            ['PT Travel Umrah Sejahtera', 'travel.sejahtera@example.com'],
            ['CV Haji Berkah', 'haji.berkah@example.com'],
            ['PT Wisata Religi Indonesia', 'wisata.religi@example.com'],
            ['CV Umrah Plus', 'umrah.plus@example.com'],
            ['PT Tour & Travel Makkah', 'tour.makkah@example.com'],
        ];

        foreach ($mitras as $index => $mitraData) {
            // Generate nomor handphone yang unik
            $noHandphone = '08' . str_pad(rand(2000000000, 2999999999), 10, '0', STR_PAD_LEFT);
            
            // Pastikan nomor handphone unik
            while (User::where('no_handphone', $noHandphone)->exists()) {
                $noHandphone = '08' . str_pad(rand(2000000000, 2999999999), 10, '0', STR_PAD_LEFT);
            }

            $user = User::firstOrCreate(
                ['email' => $mitraData[1]],
                [
                    'nama_lengkap' => $mitraData[0],
                    'jenis_kelamin' => 'laki-laki',
                    'tgl_lahir' => $now->copy()->subYears(rand(30, 50))->format('Y-m-d'),
                    'no_handphone' => $noHandphone,
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'created_at' => $now->copy()->subDays(rand(30, 180)),
                ]
            );
            
            if (!$user->hasRole('mitra')) {
                $user->assignRole('mitra');
            }

            // Buat data mitra_m jika belum ada
            $statusKode = ['pending', 'diproses', 'disetujui', 'ditolak'][$index % 4];
            $provinsiId = DB::table('provinsi_m')->value('id');
            $kotaId = DB::table('kota_m')->where('nama_kota', 'Jakarta')->value('id');
            $kecamatanId = DB::table('kecamatan_m')->value('id');

            Mitra::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'is_active' => true,
                    'nama_lengkap' => $mitraData[0],
                    'nik' => (string) (1234567890123456 + $index),
                    'provinsi_id' => $provinsiId,
                    'kota_id' => $kotaId,
                    'kecamatan_id' => $kecamatanId,
                    'alamat_lengkap' => 'Jl. Mitra No. ' . ($index + 1) . ', Jakarta',
                    'nomor_ijin_usaha' => 'SIUP-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'masa_berlaku_ijin_usaha' => $now->copy()->addYears(1)->format('Y-m-d'),
                    'status_id' => $statusIds[$statusKode] ?? $statusIds['pending'],
                    'created_at' => $now->copy()->subDays(rand(30, 180)),
                    'updated_at' => $now->copy()->subDays(rand(0, 30)),
                ]
            );
        }

        $this->command->info('✅ ' . count($mitras) . ' mitra berhasil dibuat');
    }

    private function createTransaksiUmrah($now)
    {
        $this->command->info('🕋 Membuat transaksi Umrah...');
        
        $jenisTransaksiId = DB::table('jenis_transaksi_m')->where('kode', 'PAKET_UMRAH')->value('id');
        $paketUmrahId = DB::table('paket_umrah_m')->where('is_active', true)->value('id');
        
        if (!$jenisTransaksiId || !$paketUmrahId) {
            $this->command->warn('⚠️  Paket Umrah tidak ditemukan, skip transaksi Umrah');
            return;
        }

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

        $statusPembayaranIds = DB::table('status_pembayaran_m')
            ->where('is_active', true)
            ->pluck('id', 'kode')
            ->toArray();

        $statusTransaksiIds = DB::table('status_transaksi_m')
            ->where('is_active', true)
            ->pluck('id', 'kode')
            ->toArray();

        $provinsiId = DB::table('provinsi_m')->value('id');
        $kotaId = DB::table('kota_m')->where('nama_kota', 'Jakarta')->value('id');
        $kecamatanId = DB::table('kecamatan_m')->value('id');

        $namaSamples = [
            'Ahmad Santoso', 'Siti Nurhaliza', 'Budi Rahmat', 'Nur Fathimah',
            'Reza Perdana', 'Aisyah Putri', 'Muhammad Rizki', 'Dewi Lestari',
            'Joni Pratama', 'Elsa Saputri', 'Rudi Hartono', 'Lina Marlina',
            'Agus Setiawan', 'Rina Wati', 'Hendra Gunawan', 'Maya Sari',
        ];

        $statusVariasi = [
            ['BELUM_BAYAR', 'BELUM_DIHUBUNGI'],
            ['BELUM_BAYAR', 'DIHUBUNGI'],
            ['BELUM_BAYAR', 'MENUNGGU_PEMBAYARAN'],
            ['MENUNGGU_VERIFIKASI', 'MENUNGGU_VERIFIKASI_PEMBAYARAN'],
            ['DITOLAK', 'PEMBAYARAN_DITOLAK'],
            ['LUNAS', 'DIPROSES'],
            ['LUNAS', 'TERKONFIRMASI'],
            ['LUNAS', 'SIAP_BERANGKAT'],
            ['LUNAS', 'BERANGKAT'],
            ['LUNAS', 'PULANG'],
            ['LUNAS', 'SELESAI'],
            ['REFUND', 'DIBATALKAN'],
            ['LUNAS', 'REFUND_DIAJUKAN'],
        ];

        $count = 0;
        for ($i = 0; $i < 30; $i++) {
            $keberangkatanId = $keberangkatanIds[array_rand($keberangkatanIds)] ?? null;
            $tipeId = $tipeIds[array_rand($tipeIds)] ?? null;
            
            if (!$keberangkatanId || !$tipeId) continue;

            $namaIndex = $i % count($namaSamples);
            $namaPemesan = $namaSamples[$namaIndex];
            $noWa = '08' . str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);

            $tipeData = DB::table('paket_umrah_tipe_m')
                ->where('id', $tipeId)
                ->where('paket_umrah_id', $paketUmrahId)
                ->first();

            $paketData = DB::table('paket_umrah_m')
                ->where('id', $paketUmrahId)
                ->first();

            if (!$tipeData || !$paketData) continue;

            $snapshot = [
                'id' => (int) $paketData->id,
                'nama_paket' => $paketData->nama_paket,
                'musim_id' => (int) $paketData->musim_id,
                'paket_umrah_tipe_id' => (int) $tipeId,
                'harga_per_pax' => (float) $tipeData->harga_per_pax,
                'tanggal_kunjungan' => $now->copy()->addDays(rand(30, 180))->format('Y-m-d'),
            ];

            $jumlahJamaah = rand(1, 3);
            $jamaahData = [];
            for ($j = 0; $j < $jumlahJamaah; $j++) {
                $jamaahData[] = [
                    'id' => null,
                    'nama' => $namaPemesan . ($j > 0 ? ' (Keluarga ' . $j . ')' : ''),
                    'nik' => (string) (1234567890123456 + $j + $i),
                    'no_paspor' => 'A' . str_pad($i * 10 + $j + 1, 7, '0', STR_PAD_LEFT),
                ];
            }

            $totalBiaya = (float) $tipeData->harga_per_pax * $jumlahJamaah;
            $statusCombo = $statusVariasi[$i % count($statusVariasi)];

            $tanggal = $now->copy()->subDays(rand(0, 60))->format('dmy');
            $kodeTransaksi = 'UMRAH-' . $tanggal . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            $nomorPembayaran = null;
            if ($statusCombo[0] !== 'BELUM_BAYAR') {
                // Generate nomor pembayaran yang unik
                $nomorPembayaran = 'NP-' . time() . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                
                // Pastikan nomor pembayaran unik
                while (DB::table('transaksi_m')->where('nomor_pembayaran', $nomorPembayaran)->exists()) {
                    $nomorPembayaran = 'NP-' . time() . '-' . str_pad($i + rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                }
            }

            $transaksiId = DB::table('transaksi_m')->insertGetId([
                'is_active' => true,
                'gelar_id' => null,
                'nama_lengkap' => $namaPemesan,
                'no_whatsapp' => $noWa,
                'provinsi_id' => $provinsiId,
                'kota_id' => $kotaId,
                'kecamatan_id' => $kecamatanId,
                'alamat_lengkap' => 'Jl. Merdeka No. ' . ($i + 1) . ', Jakarta',
                'deskripsi' => 'Transaksi sample umrah',
                'jenis_transaksi_id' => $jenisTransaksiId,
                'produk_id' => $paketUmrahId,
                'keberangkatan_id' => $keberangkatanId,
                'snapshot_produk' => json_encode($snapshot),
                'jamaah_data' => json_encode($jamaahData),
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => true,
                'total_biaya' => $totalBiaya,
                'status_pembayaran_id' => $statusPembayaranIds[$statusCombo[0]] ?? null,
                'status_transaksi_id' => $statusTransaksiIds[$statusCombo[1]] ?? null,
                'nomor_pembayaran' => $nomorPembayaran,
                'tanggal_transaksi' => $now->copy()->subDays(rand(0, 60)),
                'created_at' => $now->copy()->subDays(rand(0, 60)),
                'updated_at' => $now->copy()->subDays(rand(0, 5)),
            ]);

            if ($statusCombo[0] !== 'BELUM_BAYAR') {
                $kodePembayaran = rand(100, 999);
                $nominalAsli = (int) $totalBiaya;
                $nominalTransfer = $nominalAsli + $kodePembayaran;

                $statusPembayaran = match ($statusCombo[0]) {
                    'MENUNGGU_VERIFIKASI' => 'pending',
                    'DITOLAK' => 'rejected',
                    'LUNAS' => 'verified',
                    'REFUND' => 'refunded',
                    default => 'pending',
                };

                $verified = $statusPembayaran === 'verified';

                $moota_reference = null;
                if ($verified) {
                    $moota_reference = 'MOOTA-' . time() . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
                    // Pastikan moota_reference unik
                    while (DB::table('pembayaran_transaksi_m')->where('moota_reference', $moota_reference)->exists()) {
                        $moota_reference = 'MOOTA-' . time() . '-' . str_pad($i + rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                    }
                }

                DB::table('pembayaran_transaksi_m')->insert([
                    'is_active' => true,
                    'transaksi_id' => $transaksiId,
                    'nomor_pembayaran' => $nomorPembayaran,
                    'kode_unik' => $kodePembayaran,
                    'nominal_asli' => $nominalAsli,
                    'nominal_transfer' => $nominalTransfer,
                    'moota_reference' => $moota_reference,
                    'bank_pengirim' => $verified ? 'BCA' : null,
                    'nama_pengirim' => $verified ? $namaPemesan : null,
                    'tanggal_transfer' => $verified ? $now->copy()->subDays(rand(1, 30)) : null,
                    'status' => $statusPembayaran,
                    'validasi_manual' => $verified ? true : false,
                    'verified_by' => $verified ? 1 : null,
                    'verified_at' => $verified ? $now->copy()->subDays(rand(1, 20)) : null,
                    'expired_at' => $statusPembayaran === 'pending' ? $now->addDay() : null,
                    'created_at' => $now->copy()->subDays(rand(5, 60)),
                    'updated_at' => $now->copy()->subDays(rand(0, 5)),
                ]);
            }

            $count++;
        }

        $this->command->info('✅ ' . $count . ' transaksi Umrah berhasil dibuat');
    }

    private function createTransaksiHaji($now)
    {
        $this->command->info('🕌 Membuat transaksi Haji...');
        
        $jenisTransaksiId = DB::table('jenis_transaksi_m')->where('kode', 'PAKET_HAJI')->value('id');
        $paketHajiId = DB::table('paket_haji_m')->where('is_active', true)->value('id');
        
        if (!$jenisTransaksiId || !$paketHajiId) {
            $this->command->warn('⚠️  Paket Haji tidak ditemukan, skip transaksi Haji');
            return;
        }

        $statusTransaksiIds = DB::table('status_transaksi_m')
            ->where('is_active', true)
            ->pluck('id', 'kode')
            ->toArray();

        $provinsiId = DB::table('provinsi_m')->value('id');
        $kotaId = DB::table('kota_m')->where('nama_kota', 'Jakarta')->value('id');
        $kecamatanId = DB::table('kecamatan_m')->value('id');

        $namaSamples = [
            'Ahmad Haji', 'Siti Haji', 'Budi Haji', 'Nur Haji',
            'Reza Haji', 'Aisyah Haji', 'Muhammad Haji', 'Dewi Haji',
        ];

        $statusVariasi = [
            'BELUM_DIHUBUNGI',
            'DIHUBUNGI',
            'MENUNGGU_PEMBAYARAN',
            'DIPROSES',
            'TERKONFIRMASI',
            'SELESAI',
            'DIBATALKAN',
        ];

        $count = 0;
        for ($i = 0; $i < 20; $i++) {
            $namaIndex = $i % count($namaSamples);
            $namaPemesan = $namaSamples[$namaIndex];
            $noWa = '08' . str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);

            $paketData = DB::table('paket_haji_m')
                ->where('id', $paketHajiId)
                ->first();

            if (!$paketData) continue;

            $snapshot = [
                'id' => (int) $paketData->id,
                'nama_paket' => $paketData->nama_paket,
            ];

            $jumlahJamaah = rand(1, 2);
            $jamaahData = [];
            for ($j = 0; $j < $jumlahJamaah; $j++) {
                $jamaahData[] = [
                    'id' => null,
                    'nama' => $namaPemesan . ($j > 0 ? ' (Keluarga)' : ''),
                    'nik' => (string) (2234567890123456 + $j + $i),
                    'no_paspor' => 'B' . str_pad($i * 10 + $j + 1, 7, '0', STR_PAD_LEFT),
                ];
            }

            $totalBiaya = rand(80000000, 120000000);
            $statusKode = $statusVariasi[$i % count($statusVariasi)];

            $tanggal = $now->copy()->subDays(rand(0, 90))->format('dmy');
            $kodeTransaksi = 'HAJI-' . $tanggal . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            DB::table('transaksi_m')->insert([
                'is_active' => true,
                'gelar_id' => null,
                'nama_lengkap' => $namaPemesan,
                'no_whatsapp' => $noWa,
                'provinsi_id' => $provinsiId,
                'kota_id' => $kotaId,
                'kecamatan_id' => $kecamatanId,
                'alamat_lengkap' => 'Jl. Haji No. ' . ($i + 1) . ', Jakarta',
                'deskripsi' => 'Transaksi sample haji',
                'jenis_transaksi_id' => $jenisTransaksiId,
                'produk_id' => $paketHajiId,
                'keberangkatan_id' => null,
                'snapshot_produk' => json_encode($snapshot),
                'jamaah_data' => json_encode($jamaahData),
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => true,
                'total_biaya' => $totalBiaya,
                'status_pembayaran_id' => null,
                'status_transaksi_id' => $statusTransaksiIds[$statusKode] ?? null,
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => $now->copy()->subDays(rand(0, 90)),
                'created_at' => $now->copy()->subDays(rand(0, 90)),
                'updated_at' => $now->copy()->subDays(rand(0, 5)),
            ]);

            $count++;
        }

        $this->command->info('✅ ' . $count . ' transaksi Haji berhasil dibuat');
    }

    private function createTransaksiEdutrip($now)
    {
        $this->command->info('🎓 Membuat transaksi Edutrip...');
        
        $jenisTransaksiId = DB::table('jenis_transaksi_m')->where('kode', 'PAKET_EDUTRIP')->value('id');
        $paketEdutripId = DB::table('paket_edutrip_m')->value('id');
        
        if (!$jenisTransaksiId || !$paketEdutripId) {
            $this->command->warn('⚠️  Paket Edutrip tidak ditemukan, skip transaksi Edutrip');
            return;
        }

        $statusTransaksiIds = DB::table('status_transaksi_m')
            ->where('is_active', true)
            ->pluck('id', 'kode')
            ->toArray();

        $provinsiId = DB::table('provinsi_m')->value('id');
        $kotaId = DB::table('kota_m')->where('nama_kota', 'Jakarta')->value('id');
        $kecamatanId = DB::table('kecamatan_m')->value('id');

        $namaSamples = [
            'Ahmad Edutrip', 'Siti Edutrip', 'Budi Edutrip', 'Nur Edutrip',
            'Reza Edutrip', 'Aisyah Edutrip', 'Muhammad Edutrip', 'Dewi Edutrip',
        ];

        $statusVariasi = [
            'MENUNGGU_PEMBAYARAN',
            'BELUM_DIHUBUNGI',
            'DIHUBUNGI',
            'DIPROSES',
            'TERKONFIRMASI',
            'SELESAI',
            'DIBATALKAN',
        ];

        $count = 0;
        for ($i = 0; $i < 15; $i++) {
            $namaIndex = $i % count($namaSamples);
            $namaPemesan = $namaSamples[$namaIndex];
            $noWa = '08' . str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);

            $paketData = DB::table('paket_edutrip_m')
                ->where('id', $paketEdutripId)
                ->first();

            if (!$paketData) continue;

            $snapshot = [
                'id' => (int) $paketData->id,
                'nama_paket' => $paketData->nama_paket,
                'tanggal_kunjungan' => $now->copy()->addDays(rand(30, 180))->format('Y-m-d'),
            ];

            $jumlahJamaah = rand(1, 2);
            $jamaahData = [];
            for ($j = 0; $j < $jumlahJamaah; $j++) {
                $jamaahData[] = [
                    'id' => null,
                    'nama' => $namaPemesan . ($j > 0 ? ' (Keluarga)' : ''),
                    'nik' => (string) (3234567890123456 + $j + $i),
                    'no_paspor' => 'C' . str_pad($i * 10 + $j + 1, 7, '0', STR_PAD_LEFT),
                ];
            }

            $totalBiaya = rand(15000000, 30000000);
            $statusKode = $statusVariasi[$i % count($statusVariasi)];

            $tanggal = $now->copy()->subDays(rand(0, 60))->format('dmy');
            $kodeTransaksi = 'EDUTRIP-' . $tanggal . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            DB::table('transaksi_m')->insert([
                'is_active' => true,
                'gelar_id' => null,
                'nama_lengkap' => $namaPemesan,
                'no_whatsapp' => $noWa,
                'provinsi_id' => $provinsiId,
                'kota_id' => $kotaId,
                'kecamatan_id' => $kecamatanId,
                'alamat_lengkap' => 'Jl. Edutrip No. ' . ($i + 1) . ', Jakarta',
                'deskripsi' => 'Transaksi sample edutrip',
                'jenis_transaksi_id' => $jenisTransaksiId,
                'produk_id' => $paketEdutripId,
                'keberangkatan_id' => null,
                'snapshot_produk' => json_encode($snapshot),
                'jamaah_data' => json_encode($jamaahData),
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => true,
                'total_biaya' => $totalBiaya,
                'status_pembayaran_id' => null,
                'status_transaksi_id' => $statusTransaksiIds[$statusKode] ?? null,
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => $now->copy()->subDays(rand(0, 60)),
                'created_at' => $now->copy()->subDays(rand(0, 60)),
                'updated_at' => $now->copy()->subDays(rand(0, 5)),
            ]);

            $count++;
        }

        $this->command->info('✅ ' . $count . ' transaksi Edutrip berhasil dibuat');
    }
}
