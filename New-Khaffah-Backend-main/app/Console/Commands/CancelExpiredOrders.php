<?php

namespace App\Console\Commands;

use App\Models\StatusTransaksi;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Pesanan (permintaan custom / dll) yang statusnya masih "belum diproses"
 * dan dibuat lebih dari 24 jam yang lalu dianggap batal → status diubah ke DIBATALKAN.
 * Jadwalkan dengan scheduler: php artisan schedule:run (setiap menit) atau cron.
 */
class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired {--hours=24 : Batas waktu dalam jam} {--dry-run : Hanya tampilkan, tidak update}';

    protected $description = 'Batalkan pesanan yang belum dibayar/diproses setelah 24 jam';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        $cutoff = Carbon::now()->subHours($hours);

        $statusBelumDiproses = ['BELUM_DIHUBUNGI', 'DIHUBUNGI', 'MENUNGGU_PEMBAYARAN'];
        $statusDibatalkanId = StatusTransaksi::where('kode', 'DIBATALKAN')->value('id');
        if (! $statusDibatalkanId) {
            $this->error('Status DIBATALKAN tidak ditemukan di status_transaksi_m.');
            return 1;
        }

        $query = Transaksi::query()
            ->where('is_active', true)
            ->where('created_at', '<', $cutoff)
            ->whereHas('statusTransaksi', function ($q) use ($statusBelumDiproses) {
                $q->whereIn('kode', $statusBelumDiproses);
            });

        $count = $query->count();
        if ($count === 0) {
            $this->info('Tidak ada pesanan yang kedaluwarsa.');
            return 0;
        }

        if ($dryRun) {
            $this->info("[Dry run] {$count} pesanan akan diubah statusnya menjadi Dibatalkan (created_at < {$cutoff}).");
            $query->get()->each(fn ($t) => $this->line("  - #{$t->id} {$t->kode_transaksi} ({$t->created_at})"));
            return 0;
        }

        $updated = $query->update(['status_transaksi_id' => $statusDibatalkanId]);
        $this->info("{$updated} pesanan kedaluwarsa telah diubah statusnya menjadi Dibatalkan.");
        return 0;
    }
}
