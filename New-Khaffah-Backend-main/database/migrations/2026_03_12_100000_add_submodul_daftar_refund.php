<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah sub modul "Daftar Refund" di bawah modul Daftar Transaksi.
     */
    public function up(): void
    {
        $daftarTransaksiId = DB::table('modul_aplikasi_s')->where('nama_modul', 'Daftar Transaksi')->value('id');
        if (! $daftarTransaksiId) {
            return;
        }

        $exists = DB::table('sub_modul_aplikasi_s')
            ->where('modul_id', $daftarTransaksiId)
            ->where('url', '/Daftar-Transaksi/Daftar-Refund')
            ->exists();

        if (! $exists) {
            $maxUrutan = (int) DB::table('sub_modul_aplikasi_s')
                ->where('modul_id', $daftarTransaksiId)
                ->max('urutan');
            DB::table('sub_modul_aplikasi_s')->insert([
                'modul_id'       => $daftarTransaksiId,
                'is_active'      => true,
                'urutan'         => $maxUrutan + 1,
                'nama_sub_modul' => 'Daftar Refund',
                'url'            => '/Daftar-Transaksi/Daftar-Refund',
                'icon_id'        => null,
                'fa_icon_class'  => 'fa-solid fa-money-bill-transfer',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sub_modul_aplikasi_s')
            ->where('url', '/Daftar-Transaksi/Daftar-Refund')
            ->delete();
    }
};
