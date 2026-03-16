<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NegaraKotaSeeder extends Seeder
{
    public function run(): void
    {
        // === Negara ===
        $negara = [
            ['nama_negara' => 'Arab Saudi', 'kode_iso2' => 'SA', 'kode_iso3' => 'SAU', 'kode_telepon' => '+966', 'mata_uang' => 'Riyal', 'kode_mata_uang' => 'SAR', 'benua' => 'Asia'],
            ['nama_negara' => 'Uni Emirat Arab', 'kode_iso2' => 'AE', 'kode_iso3' => 'ARE', 'kode_telepon' => '+971', 'mata_uang' => 'Dirham', 'kode_mata_uang' => 'AED', 'benua' => 'Asia'],
            ['nama_negara' => 'Turki', 'kode_iso2' => 'TR', 'kode_iso3' => 'TUR', 'kode_telepon' => '+90', 'mata_uang' => 'Lira', 'kode_mata_uang' => 'TRY', 'benua' => 'Eropa/Asia'],
            ['nama_negara' => 'Mesir', 'kode_iso2' => 'EG', 'kode_iso3' => 'EGY', 'kode_telepon' => '+20', 'mata_uang' => 'Pound Mesir', 'kode_mata_uang' => 'EGP', 'benua' => 'Afrika'],
            ['nama_negara' => 'Indonesia', 'kode_iso2' => 'ID', 'kode_iso3' => 'IDN', 'kode_telepon' => '+62', 'mata_uang' => 'Rupiah', 'kode_mata_uang' => 'IDR', 'benua' => 'Asia'],
        ];

        foreach ($negara as $n) {
            $exists = DB::table('negara_m')->where('nama_negara', $n['nama_negara'])->exists();
            if (! $exists) {
                DB::table('negara_m')->insert($n);
            }
        }

        // === Ambil ID tiap negara ===
        $id = fn($nama) => DB::table('negara_m')->where('nama_negara', $nama)->value('id');

        // === Provinsi (global + Indonesia samples) ===
        $provinsi = [
            // Indonesia sample provinsi
            ['nama_provinsi' => 'DKI Jakarta'],
            ['nama_provinsi' => 'Jawa Barat'],
            ['nama_provinsi' => 'Jawa Timur'],
            ['nama_provinsi' => 'Bali'],
            ['nama_provinsi' => 'Sumatera Utara'],
            ['nama_provinsi' => 'Sulawesi Selatan'],
            ['nama_provinsi' => 'DI Yogyakarta'],
            ['nama_provinsi' => 'Kalimantan Timur'],
            ['nama_provinsi' => 'Sumatera Selatan'],
            // Generic provinsi entries for other countries to satisfy FK
            ['nama_provinsi' => 'Provinsi Arab Saudi'],
            ['nama_provinsi' => 'Provinsi Uni Emirat Arab'],
            ['nama_provinsi' => 'Provinsi Turki'],
            ['nama_provinsi' => 'Provinsi Mesir'],
        ];

        foreach ($provinsi as $p) {
            $exists = DB::table('provinsi_m')->where('nama_provinsi', $p['nama_provinsi'])->exists();
            if (! $exists) {
                DB::table('provinsi_m')->insert($p);
            }
        }

        // helper untuk mengambil id provinsi dan negara
        $getProvId = fn($nama) => DB::table('provinsi_m')->where('nama_provinsi', $nama)->value('id');
        $getKotaId = fn($nama) => DB::table('kota_m')->where('nama_kota', $nama)->value('id');

        // === Kota per negara ===
        $kota = [
            // Arab Saudi (gunakan provinsi 'Provinsi Arab Saudi')
            ['nama_kota' => 'Makkah', 'provinsi_id' => $getProvId('Provinsi Arab Saudi'), 'negara_id' => $id('Arab Saudi'), 'kode_iata' => null, 'zona_waktu' => 'Asia/Riyadh'],
            ['nama_kota' => 'Madinah', 'provinsi_id' => $getProvId('Provinsi Arab Saudi'), 'negara_id' => $id('Arab Saudi'), 'kode_iata' => 'MED', 'zona_waktu' => 'Asia/Riyadh'],
            ['nama_kota' => 'Jeddah', 'provinsi_id' => $getProvId('Provinsi Arab Saudi'), 'negara_id' => $id('Arab Saudi'), 'kode_iata' => 'JED', 'zona_waktu' => 'Asia/Riyadh'],

            // Uni Emirat Arab
            ['nama_kota' => 'Dubai', 'provinsi_id' => $getProvId('Provinsi Uni Emirat Arab'), 'negara_id' => $id('Uni Emirat Arab'), 'kode_iata' => 'DXB', 'zona_waktu' => 'Asia/Dubai'],
            ['nama_kota' => 'Abu Dhabi', 'provinsi_id' => $getProvId('Provinsi Uni Emirat Arab'), 'negara_id' => $id('Uni Emirat Arab'), 'kode_iata' => 'AUH', 'zona_waktu' => 'Asia/Dubai'],

            // Turki
            ['nama_kota' => 'Istanbul', 'provinsi_id' => $getProvId('Provinsi Turki'), 'negara_id' => $id('Turki'), 'kode_iata' => 'IST', 'zona_waktu' => 'Europe/Istanbul'],
            ['nama_kota' => 'Ankara', 'provinsi_id' => $getProvId('Provinsi Turki'), 'negara_id' => $id('Turki'), 'kode_iata' => 'ESB', 'zona_waktu' => 'Europe/Istanbul'],

            // Mesir
            ['nama_kota' => 'Kairo', 'provinsi_id' => $getProvId('Provinsi Mesir'), 'negara_id' => $id('Mesir'), 'kode_iata' => 'CAI', 'zona_waktu' => 'Africa/Cairo'],
            ['nama_kota' => 'Alexandria', 'provinsi_id' => $getProvId('Provinsi Mesir'), 'negara_id' => $id('Mesir'), 'kode_iata' => 'ALY', 'zona_waktu' => 'Africa/Cairo'],

            // Indonesia (bandara internasional) - assign provinsi sesuai kota
            ['nama_kota' => 'Jakarta', 'provinsi_id' => $getProvId('DKI Jakarta'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'CGK', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Denpasar (Bali)', 'provinsi_id' => $getProvId('Bali'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'DPS', 'zona_waktu' => 'Asia/Makassar'],
            ['nama_kota' => 'Surabaya', 'provinsi_id' => $getProvId('Jawa Timur'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'SUB', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Medan', 'provinsi_id' => $getProvId('Sumatera Utara'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'KNO', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Makassar', 'provinsi_id' => $getProvId('Sulawesi Selatan'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'UPG', 'zona_waktu' => 'Asia/Makassar'],
            ['nama_kota' => 'Yogyakarta', 'provinsi_id' => $getProvId('DI Yogyakarta'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'YIA', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Balikpapan', 'provinsi_id' => $getProvId('Kalimantan Timur'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'BPN', 'zona_waktu' => 'Asia/Makassar'],
            ['nama_kota' => 'Manado', 'provinsi_id' => $getProvId('Sulawesi Selatan'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'MDC', 'zona_waktu' => 'Asia/Makassar'],
            ['nama_kota' => 'Batam', 'provinsi_id' => $getProvId('Sumatera Selatan'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'BTH', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Padang', 'provinsi_id' => $getProvId('Sumatera Utara'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'PDG', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Pontianak', 'provinsi_id' => $getProvId('Kalimantan Timur'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'PNK', 'zona_waktu' => 'Asia/Pontianak'],
            ['nama_kota' => 'Palembang', 'provinsi_id' => $getProvId('Sumatera Selatan'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'PLM', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Semarang', 'provinsi_id' => $getProvId('Jawa Tengah') ?: $getProvId('Jawa Timur'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'SRG', 'zona_waktu' => 'Asia/Jakarta'],
            ['nama_kota' => 'Lombok', 'provinsi_id' => $getProvId('Nusa Tenggara Barat') ?: $getProvId('Bali'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'LOP', 'zona_waktu' => 'Asia/Makassar'],
            ['nama_kota' => 'Pekanbaru', 'provinsi_id' => $getProvId('Riau') ?: $getProvId('Sumatera Utara'), 'negara_id' => $id('Indonesia'), 'kode_iata' => 'PKU', 'zona_waktu' => 'Asia/Jakarta'],
        ];

        // Insert kota (cek eksistensi berdasarkan nama + negara)
        foreach ($kota as $kt) {
            $exists = DB::table('kota_m')
                ->where('nama_kota', $kt['nama_kota'])
                ->where('negara_id', $kt['negara_id'])
                ->exists();
            if (! $exists) {
                DB::table('kota_m')->insert($kt);
            }
        }

        // === Provinsi (Indonesia only) ===
        // sample provinsi — jangan terlalu banyak, cukup beberapa besar
        $provinsi = [
            ['nama_provinsi' => 'DKI Jakarta'],
            ['nama_provinsi' => 'Jawa Barat'],
            ['nama_provinsi' => 'Jawa Timur'],
            ['nama_provinsi' => 'Bali'],
            ['nama_provinsi' => 'Sumatera Utara'],
        ];

        // Insert provinsi jika belum ada
        foreach ($provinsi as $p) {
            $exists = DB::table('provinsi_m')->where('nama_provinsi', $p['nama_provinsi'])->exists();
            if (! $exists) {
                DB::table('provinsi_m')->insert($p);
            }
        }

        // === Kecamatan sample (link ke beberapa kota besar Indonesia) ===
        // Ambil beberapa kota id yang sudah diinsert
        $getKotaId = fn($nama) => DB::table('kota_m')->where('nama_kota', $nama)->value('id');

        $kecamatan = [];

        // Kecamatan untuk Jakarta (kota bernama 'Jakarta' dari daftar sebelumnya)
        $jakartaId = $getKotaId('Jakarta');
        if ($jakartaId) {
            $kecamatan = array_merge($kecamatan, [
                ['nama_kecamatan' => 'Gambir', 'kota_id' => $jakartaId],
                ['nama_kecamatan' => 'Menteng', 'kota_id' => $jakartaId],
                ['nama_kecamatan' => 'Kebayoran Baru', 'kota_id' => $jakartaId],
            ]);
        }

        // Kecamatan untuk Denpasar (Bali)
        $denpasarId = $getKotaId('Denpasar (Bali)');
        if ($denpasarId) {
            $kecamatan = array_merge($kecamatan, [
                ['nama_kecamatan' => 'Denpasar Barat', 'kota_id' => $denpasarId],
                ['nama_kecamatan' => 'Denpasar Timur', 'kota_id' => $denpasarId],
            ]);
        }

        // Kecamatan untuk Surabaya
        $surabayaId = $getKotaId('Surabaya');
        if ($surabayaId) {
            $kecamatan = array_merge($kecamatan, [
                ['nama_kecamatan' => 'Genteng', 'kota_id' => $surabayaId],
                ['nama_kecamatan' => 'Sawahan', 'kota_id' => $surabayaId],
            ]);
        }

        // Kecamatan untuk Medan
        $medanId = $getKotaId('Medan');
        if ($medanId) {
            $kecamatan = array_merge($kecamatan, [
                ['nama_kecamatan' => 'Medan Kota', 'kota_id' => $medanId],
                ['nama_kecamatan' => 'Medan Timur', 'kota_id' => $medanId],
            ]);
        }

        // Insert kecamatan sample jika ada
        foreach ($kecamatan as $kc) {
            $exists = DB::table('kecamatan_m')
                ->where('nama_kecamatan', $kc['nama_kecamatan'])
                ->where('kota_id', $kc['kota_id'])
                ->exists();
            if (! $exists) {
                DB::table('kecamatan_m')->insert($kc);
            }
        }
    }
}
