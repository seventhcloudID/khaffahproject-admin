<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModulAplikasiSeeder extends Seeder
{
    public function run(): void
    {
        // clear dulu (MySQL butuh nonaktifkan FK check saat truncate)
        Schema::disableForeignKeyConstraints();
        DB::table('sub_modul_aplikasi_s')->truncate();
        DB::table('modul_aplikasi_s')->truncate();
        Schema::enableForeignKeyConstraints();

        // insert modul

        $daftar_transaksi = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 2,
            'nama_modul' => 'Daftar Transaksi',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-list',
        ]);

        $paket = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 3,
            'nama_modul' => 'Paket',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-box',
        ]);

        $sistemAdmin = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 4,
            'nama_modul' => 'Sistem Admin',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-cogs',
        ]);

        $pendaftaran = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 5,
            'nama_modul' => 'Pendaftaran',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-pencil',
        ]);

        $akuntansi = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 6,
            'nama_modul' => 'Akuntansi',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-file-invoice-dollar',
        ]);

        // Modul Manajemen
        $manajemen = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 7,
            'nama_modul' => 'Manajemen',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-briefcase',
        ]);

        // Modul Mitra (Data Mitra & Pendaftaran Mitra)
        $mitra = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 8,
            'nama_modul' => 'Mitra',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-handshake',
        ]);

        // Modul Support
        $support = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 9,
            'nama_modul' => 'Support',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-headset',
        ]);

        // Modul Data Master (Hotel, Maskapai, dll)
        $dataMaster = DB::table('modul_aplikasi_s')->insertGetId([
            'is_active' => true,
            'urutan' => 10,
            'nama_modul' => 'Data Master',
            'icon_id' => null,
            'fa_icon_class' => 'fa-solid fa-database',
        ]);

        // insert sub modul
        DB::table('sub_modul_aplikasi_s')->insert([
            [
                'modul_id' => $daftar_transaksi,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Pemesanan Paket Umrah',
                'url' => '/Daftar-Transaksi/Pemesanan-Paket-Umrah',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-plane-departure',
            ],
            [
                'modul_id' => $daftar_transaksi,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Pendaftaran Haji',
                'url' => '/Daftar-Transaksi/Pendaftaran-Haji',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-users',
            ],
            [
                'modul_id' => $daftar_transaksi,
                'is_active' => true,
                'urutan' => 3,
                'nama_sub_modul' => 'Peminat Edutrip',
                'url' => '/Daftar-Transaksi/Peminat-Edutrip',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-graduation-cap',
            ],
            [
                'modul_id' => $daftar_transaksi,
                'is_active' => true,
                'urutan' => 4,
                'nama_sub_modul' => 'Permintaan Custom',
                'url' => '/Daftar-Transaksi/Permintaan-Custom',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-pencil',
            ],
            [
                'modul_id' => $daftar_transaksi,
                'is_active' => true,
                'urutan' => 5,
                'nama_sub_modul' => 'Daftar Refund',
                'url' => '/Daftar-Transaksi/Daftar-Refund',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-money-bill-transfer',
            ],
            [
                'modul_id' => $sistemAdmin,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'User Management',
                'url' => '/Sistem-Admin/User-Management',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-user-cog',
            ],
            [
                'modul_id' => $sistemAdmin,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Setting Tentang Kami',
                'url' => '/sistem-admin/Setting-Tentang-Kami',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-info-circle',
            ],
            [
                'modul_id' => $sistemAdmin,
                'is_active' => true,
                'urutan' => 3,
                'nama_sub_modul' => 'Manajemen Banner',
                'url' => '/sistem-admin/Manajemen-Banner',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-image',
            ],
            [
                'modul_id' => $dataMaster,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Master Hotel',
                'url' => '/Paket/Master-Hotel',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-hotel',
            ],
            [
                'modul_id' => $dataMaster,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Master Maskapai',
                'url' => '/Paket/Master-Maskapai',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-plane',
            ],
            [
                'modul_id' => $dataMaster,
                'is_active' => true,
                'urutan' => 3,
                'nama_sub_modul' => 'Master Keberangkatan',
                'url' => '/Paket/Master-Keberangkatan',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-location-dot',
            ],
            [
                'modul_id' => $dataMaster,
                'is_active' => true,
                'urutan' => 4,
                'nama_sub_modul' => 'Master Kepulangan',
                'url' => '/Paket/Master-Kepulangan',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-plane-arrival',
            ],
            [
                'modul_id' => $dataMaster,
                'is_active' => true,
                'urutan' => 5,
                'nama_sub_modul' => 'Master Level Mitra',
                'url' => '/Paket/Master-Level-Mitra',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-percent',
            ],
            [
                'modul_id' => $paket,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Paket Haji',
                'url' => '/Paket/Master-Haji',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-kaaba',
            ],
            [
                'modul_id' => $paket,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Paket Edutrip',
                'url' => '/Paket/Master-Edutrip',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-school',
            ],
            [
                'modul_id' => $paket,
                'is_active' => true,
                'urutan' => 3,
                'nama_sub_modul' => 'Paket Umrah',
                'url' => '/Paket/Master-Umrah',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-kaaba',
            ],
            [
                'modul_id' => $paket,
                'is_active' => true,
                'urutan' => 4,
                'nama_sub_modul' => 'Paket Request',
                'url' => '/paket-request',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-puzzle-piece',
            ],
            [
                'modul_id' => $pendaftaran,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Pendaftaran User',
                'url' => '/Pendaftaran/Pendaftaran-User',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-user',
            ],
            [
                'modul_id' => $pendaftaran,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Pendaftaran Mitra',
                'url' => '/Pendaftaran/Pendaftaran-mitra',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-handshake',
            ],
        ]);

        DB::table('sub_modul_aplikasi_s')->insert([
            // --- Akuntansi ---
            [
                'modul_id' => $akuntansi,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Laporan Keuangan',
                'url' => '/Akuntansi/Laporan-Keuangan',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-chart-line',
            ],
            [
                'modul_id' => $akuntansi,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Jurnal Transaksi',
                'url' => '/Akuntansi/Jurnal-Transaksi',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-book',
            ],

            // --- Manajemen ---
            [
                'modul_id' => $manajemen,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Monitoring Operasional',
                'url' => '/Manajemen/Monitoring-Operasional',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-chart-area',
            ],

            // --- Mitra ---
            [
                'modul_id' => $mitra,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Data Mitra',
                'url' => '/Mitra/Data-Mitra',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-users',
            ],
            [
                'modul_id' => $mitra,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Pendaftaran Mitra',
                'url' => '/Mitra/Pendaftaran-Mitra',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-user-plus',
            ],

            // --- Support ---
            [
                'modul_id' => $support,
                'is_active' => true,
                'urutan' => 1,
                'nama_sub_modul' => 'Daftar Ticketing',
                'url' => '/Support/Daftar-Ticketing',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-ticket',
            ],
            [
                'modul_id' => $support,
                'is_active' => true,
                'urutan' => 2,
                'nama_sub_modul' => 'Knowledge Base',
                'url' => '/Support/Knowledge-Base',
                'icon_id' => null,
                'fa_icon_class' => 'fa-solid fa-book-open',
            ],
        ]);
    }
}
