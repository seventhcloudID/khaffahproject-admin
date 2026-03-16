<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Jamaah;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard mitra: statistik dari database (tanpa hardcode).
 * Hanya untuk akun dengan role mitra.
 */
class MitraDashboardController extends Controller
{
    /**
     * GET /api/mitra/dashboard
     * Mengembalikan total jamaah, total pesanan, dan trend (dari data riil).
     */
    public function dashboard(Request $request): JsonResponse
    {
        $t0 = microtime(true);
        $user = Auth::user();

        if (! $user->hasRole('mitra')) {
            return response()->json([
                'status' => false,
                'message' => 'Akses hanya untuk mitra.',
            ], 403);
        }

        $akunId = (int) $user->id;

        // Total jamaah aktif milik akun ini
        $totalJamaah = Jamaah::where('akun_id', $akunId)
            ->where('is_active', true)
            ->count();

        // Total pesanan (transaksi) aktif milik akun ini yang dibuat sebagai mitra (sama dengan Daftar Pesanan)
        $totalPesanan = Transaksi::where('akun_id', $akunId)
            ->where('is_active', true)
            ->where('dibuat_sebagai_mitra', true)
            ->count();

        // Trend: bandingkan dengan periode sebelumnya (30 hari lalu vs 30 hari terakhir)
        $now = Carbon::now();
        $periodEnd = $now->copy();
        $periodStart = $now->copy()->subDays(30);
        $previousEnd = $periodStart->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays(30);

        $pesananPeriodeIni = Transaksi::where('akun_id', $akunId)
            ->where('is_active', true)
            ->where('dibuat_sebagai_mitra', true)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $pesananPeriodeLalu = Transaksi::where('akun_id', $akunId)
            ->where('is_active', true)
            ->where('dibuat_sebagai_mitra', true)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        $trendPesanan = $this->hitungTrend($pesananPeriodeIni, $pesananPeriodeLalu);

        // Trend jamaah: bandingkan jamaah yang dibuat 30 hari terakhir vs 30 hari sebelumnya
        $jamaahPeriodeIni = Jamaah::where('akun_id', $akunId)
            ->where('is_active', true)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        $jamaahPeriodeLalu = Jamaah::where('akun_id', $akunId)
            ->where('is_active', true)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        $trendJamaah = $this->hitungTrend($jamaahPeriodeIni, $jamaahPeriodeLalu);

        $controllerMs = round((microtime(true) - $t0) * 1000);
        \Illuminate\Support\Facades\Log::channel('single')->info('[Laravel mitra/dashboard] controller', ['controllerMs' => $controllerMs]);

        return response()->json([
            'status' => true,
            'message' => 'Data dashboard mitra',
            'data' => [
                'total_jamaah' => $totalJamaah,
                'total_pesanan' => $totalPesanan,
                'trend_jamaah' => $trendJamaah,
                'trend_pesanan' => $trendPesanan,
            ],
        ]);
    }

    /**
     * @return array{ direction: 'up'|'down'|'same', percent: float, label: string }
     */
    private function hitungTrend(int $sekarang, int $lalu): array
    {
        if ($lalu === 0) {
            $percent = $sekarang > 0 ? 100.0 : 0.0;
            $direction = $sekarang > 0 ? 'up' : 'same';
            return [
                'direction' => $direction,
                'percent' => round($percent, 1),
                'label' => $sekarang > 0 ? '+' . round($percent, 1) . '%' : '0%',
            ];
        }

        $percent = (($sekarang - $lalu) / $lalu) * 100;
        $direction = $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'same');
        $label = $percent >= 0 ? '+' . round($percent, 1) . '%' : round($percent, 1) . '%';

        return [
            'direction' => $direction,
            'percent' => round($percent, 1),
            'label' => $label,
        ];
    }
}
