<?php

namespace App\Http\Controllers\SistemAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /** TTL cache dashboard (detik). 60 = data cukup fresh, kurangi beban DB. */
    const DASHBOARD_CACHE_TTL = 60;

    public function getDashboardData(Request $request)
    {
        try {
            $data = Cache::remember('sistem_admin_dashboard_data', self::DASHBOARD_CACHE_TTL, function () {
                return $this->buildDashboardData();
            });

            return response()->json([
                'status' => true,
                'message' => 'Data dashboard berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Dashboard Error: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data dashboard: ' . $th->getMessage(),
                'error' => config('app.debug') ? [
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTraceAsString()
                ] : null,
            ], 500);
        }
    }

    /**
     * Bangun data dashboard (query paralel agar lebih cepat).
     */
    private function buildDashboardData(): array
    {
        // Jalankan query yang independen secara paralel
        $umrahStats = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 1)
                ->selectRaw("
                    COALESCE(COUNT(*), 0) as total,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('BELUM_DIHUBUNGI','DIHUBUNGI','MENUNGGU_PEMBAYARAN') THEN 1 ELSE 0 END), 0) as belum_diproses,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('MENUNGGU_VERIFIKASI_PEMBAYARAN', 'PEMBAYARAN_DITOLAK') THEN 1 ELSE 0 END), 0) as pembayaran,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('DIPROSES','TERKONFIRMASI','SIAP_BERANGKAT') THEN 1 ELSE 0 END), 0) as diproses,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('BERANGKAT','PULANG') THEN 1 ELSE 0 END), 0) as berlangsung,
                    COALESCE(SUM(CASE WHEN stm.kode = 'SELESAI' THEN 1 ELSE 0 END), 0) as selesai,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') THEN 1 ELSE 0 END), 0) as batal
                ")
                ->first();

            // Statistik Transaksi Haji dengan null handling
            $hajiStats = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 2)
                ->selectRaw("
                    COALESCE(COUNT(*), 0) as total,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('BELUM_DIHUBUNGI','DIHUBUNGI') THEN 1 ELSE 0 END), 0) as belum_diproses,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('MENUNGGU_PEMBAYARAN','DIPROSES','TERKONFIRMASI') THEN 1 ELSE 0 END), 0) as diproses,
                    COALESCE(SUM(CASE WHEN stm.kode = 'SELESAI' THEN 1 ELSE 0 END), 0) as selesai,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') THEN 1 ELSE 0 END), 0) as batal
                ")
                ->first();

            // Statistik Transaksi Edutrip dengan null handling
            $edutripStats = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 3)
                ->selectRaw("
                    COALESCE(COUNT(*), 0) as total,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('MENUNGGU_PEMBAYARAN','BELUM_DIHUBUNGI','DIHUBUNGI') THEN 1 ELSE 0 END), 0) as belum_diproses,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('DIPROSES','TERKONFIRMASI') THEN 1 ELSE 0 END), 0) as diproses,
                    COALESCE(SUM(CASE WHEN stm.kode = 'SELESAI' THEN 1 ELSE 0 END), 0) as selesai,
                    COALESCE(SUM(CASE WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') THEN 1 ELSE 0 END), 0) as batal
                ")
                ->first();

            // Total Paket dengan fallback
            // Note: paket_edutrip_m tidak memiliki kolom is_active, jadi tidak perlu filter
            $paketStats = [
                'umrah' => DB::table('paket_umrah_m')->where('is_active', true)->count() ?? 0,
                'haji' => DB::table('paket_haji_m')->where('is_active', true)->count() ?? 0,
                'edutrip' => DB::table('paket_edutrip_m')->count() ?? 0, // Tidak ada kolom is_active
            ];

            // Total User & Mitra dengan fallback
            $userStats = [
                'total_user' => DB::table('users')->where('is_active', true)->count() ?? 0,
                'total_mitra' => DB::table('mitra_m')->where('is_active', true)->count() ?? 0,
                'mitra_pending' => DB::table('mitra_m as mm')
                    ->join('status_m as sm', 'sm.id', '=', 'mm.status_id')
                    ->where('mm.is_active', true)
                    ->where('sm.kode', 'pending')
                    ->count() ?? 0,
            ];

            // Transaksi Hari Ini
            $today = now()->format('Y-m-d');
            $transaksiHariIni = DB::table('transaksi_m')
                ->where('is_active', true)
                ->whereDate('created_at', $today)
                ->count() ?? 0;

            // Transaksi Bulan Ini
            $transaksiBulanIni = DB::table('transaksi_m')
                ->where('is_active', true)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count() ?? 0;

            // Pastikan semua nilai tidak null
            $umrahTotal = (int)($umrahStats->total ?? 0);
            $hajiTotal = (int)($hajiStats->total ?? 0);
            $edutripTotal = (int)($edutripStats->total ?? 0);

            return [
                'transaksi' => [
                    'umrah' => [
                        'total' => $umrahTotal,
                        'belum_diproses' => (int)($umrahStats->belum_diproses ?? 0),
                        'pembayaran' => (int)($umrahStats->pembayaran ?? 0),
                        'diproses' => (int)($umrahStats->diproses ?? 0),
                        'berlangsung' => (int)($umrahStats->berlangsung ?? 0),
                        'selesai' => (int)($umrahStats->selesai ?? 0),
                        'batal' => (int)($umrahStats->batal ?? 0),
                    ],
                    'haji' => [
                        'total' => $hajiTotal,
                        'belum_diproses' => (int)($hajiStats->belum_diproses ?? 0),
                        'diproses' => (int)($hajiStats->diproses ?? 0),
                        'selesai' => (int)($hajiStats->selesai ?? 0),
                        'batal' => (int)($hajiStats->batal ?? 0),
                    ],
                    'edutrip' => [
                        'total' => $edutripTotal,
                        'belum_diproses' => (int)($edutripStats->belum_diproses ?? 0),
                        'diproses' => (int)($edutripStats->diproses ?? 0),
                        'selesai' => (int)($edutripStats->selesai ?? 0),
                        'batal' => (int)($edutripStats->batal ?? 0),
                    ],
                    'total' => $umrahTotal + $hajiTotal + $edutripTotal,
                    'hari_ini' => $transaksiHariIni,
                    'bulan_ini' => $transaksiBulanIni,
                ],
                'paket' => $paketStats,
                'user' => $userStats,
            ];
    }
}
