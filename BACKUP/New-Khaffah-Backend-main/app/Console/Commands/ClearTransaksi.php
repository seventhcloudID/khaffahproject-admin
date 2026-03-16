<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearTransaksi extends Command
{
    protected $signature = 'transaksi:clear {--force : Tanpa konfirmasi}';

    protected $description = 'Kosongkan semua data transaksi (transaksi_m dan pembayaran_transaksi_m)';

    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Yakin akan menghapus SEMUA data transaksi? Tindakan ini tidak dapat dibatalkan.')) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        try {
            DB::beginTransaction();

            $pembayaran = DB::table('pembayaran_transaksi_m')->count();
            DB::table('pembayaran_transaksi_m')->delete();
            $this->info("Dihapus {$pembayaran} baris dari pembayaran_transaksi_m.");

            $transaksi = DB::table('transaksi_m')->count();
            DB::table('transaksi_m')->delete();
            $this->info("Dihapus {$transaksi} baris dari transaksi_m.");

            DB::commit();
            $this->info('Semua data transaksi telah dikosongkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
