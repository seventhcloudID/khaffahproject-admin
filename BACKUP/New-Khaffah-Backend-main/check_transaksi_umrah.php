<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Transaksi Umrah ===\n\n";

// Check total transaksi umrah
$total = DB::table('transaksi_m')
    ->where('jenis_transaksi_id', 1)
    ->where('is_active', true)
    ->count();

echo "Total transaksi umrah aktif: $total\n\n";

if ($total > 0) {
    // Get sample data
    $sample = DB::table('transaksi_m as tm')
        ->leftjoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
        ->leftjoin('paket_umrah_m as pum', 'pum.id', '=', 'tm.produk_id')
        ->leftjoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
        ->select('tm.id', 'tm.kode_transaksi', 'tm.nama_lengkap', 'pum.nama_paket', 'stm.nama_status', 'tm.created_at')
        ->where('tm.jenis_transaksi_id', 1)
        ->where('tm.is_active', true)
        ->limit(5)
        ->get();
    
    echo "Sample data:\n";
    foreach ($sample as $item) {
        echo "  - {$item->kode_transaksi}: {$item->nama_lengkap} ({$item->nama_paket}) - {$item->nama_status}\n";
    }
} else {
    echo "⚠️  Tidak ada transaksi umrah di database!\n";
    echo "\nMungkin perlu:\n";
    echo "1. Buat transaksi umrah baru melalui frontend\n";
    echo "2. Atau cek apakah seeder sudah membuat data transaksi umrah\n";
}
