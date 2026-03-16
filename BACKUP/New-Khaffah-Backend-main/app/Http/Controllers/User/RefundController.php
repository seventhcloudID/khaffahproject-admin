<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PembayaranTransaksi;
use App\Models\StatusTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /** Status yang boleh diajukan refund (belum berangkat, sudah/sedang pembayaran). */
    private const ELIGIBLE_REFUND_STATUSES = [
        'MENUNGGU_VERIFIKASI_PEMBAYARAN',
        'DIPROSES',
        'TERKONFIRMASI',
    ];

    /**
     * Resolve nominal total untuk tampilan: pakai total_biaya, fallback dari snapshot (harga_per_pax * jamaah)
     * atau total/grand_total di snapshot, lalu fallback dari sum pembayaran terverifikasi.
     */
    private function resolveTotalBiaya(Transaksi $t): string
    {
        $nilai = (float) $t->total_biaya;
        if ($nilai > 0) {
            return number_format($nilai, 0, ',', '.');
        }

        $snapshot = is_array($t->snapshot_produk) ? $t->snapshot_produk : (array) json_decode($t->snapshot_produk ?? '{}', true);
        $hargaPerPax = (float) ($snapshot['harga_per_pax'] ?? $snapshot['harga_per_pax_net'] ?? 0);
        $jamaahData = $t->jamaah_data;
        $jamaahCount = is_array($jamaahData) ? max(1, count($jamaahData)) : 1;
        if ($hargaPerPax > 0) {
            return number_format($hargaPerPax * $jamaahCount, 0, ',', '.');
        }

        $totalFromSnap = (float) ($snapshot['total_biaya'] ?? $snapshot['total'] ?? $snapshot['grand_total'] ?? $snapshot['total_harga'] ?? 0);
        if ($totalFromSnap > 0) {
            return number_format($totalFromSnap, 0, ',', '.');
        }

        $sumPembayaran = PembayaranTransaksi::query()
            ->where('transaksi_id', $t->id)
            ->where('is_active', true)
            ->whereIn('status', ['verified', 'validated', 'VALIDATED', 'VERIFIED'])
            ->sum('nominal_asli');
        if ($sumPembayaran > 0) {
            return number_format((float) $sumPembayaran, 0, ',', '.');
        }

        return '0';
    }

    /**
     * Daftar transaksi user yang terkait refund: bisa ajukan, sudah diajukan, atau batal.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $statusRefundDiajukan = StatusTransaksi::where('kode', 'REFUND_DIAJUKAN')->value('id');
        $statusDibatalkan = StatusTransaksi::where('kode', 'DIBATALKAN')->value('id');
        $eligibleKodes = self::ELIGIBLE_REFUND_STATUSES;

        $items = Transaksi::query()
            ->where('akun_id', $user->id)
            ->where('is_active', true)
            ->with(['statusTransaksi'])
            ->orderByDesc('created_at')
            ->get()
            ->filter(function ($t) use ($eligibleKodes, $statusRefundDiajukan, $statusDibatalkan) {
                $kode = $t->statusTransaksi?->kode;
                return in_array($kode, $eligibleKodes, true)
                    || (int) $t->status_transaksi_id === (int) $statusRefundDiajukan
                    || (int) $t->status_transaksi_id === (int) $statusDibatalkan;
            })
            ->values()
            ->map(function (Transaksi $t) use ($eligibleKodes) {
                $kode = $t->statusTransaksi?->kode ?? '';
                $canRequest = in_array($kode, $eligibleKodes, true);
                $snapshot = is_array($t->snapshot_produk) ? $t->snapshot_produk : (array) json_decode($t->snapshot_produk ?? '{}', true);
                $title = $snapshot['nama_paket'] ?? 'Paket';
                return [
                    'id' => $t->id,
                    'kode_transaksi' => $t->kode_transaksi,
                    'title' => $title,
                    'subtitle' => 'Program Umrah',
                    'total_biaya' => $this->resolveTotalBiaya($t),
                    'status_kode' => $kode,
                    'status_nama' => $t->statusTransaksi?->nama_status ?? '',
                    'can_request_refund' => $canRequest,
                    'refund_alasan' => $t->refund_alasan,
                    'refund_requested_at' => $t->refund_requested_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Daftar transaksi mitra yang terkait refund (hanya yang dibuat sebagai mitra).
     * Format sama dengan index() plus durasi_hari, tanggal_berangkat, tanggal_pulang, jumlah_jamaah.
     */
    public function indexMitra(): JsonResponse
    {
        $user = Auth::user();
        $statusRefundDiajukan = StatusTransaksi::where('kode', 'REFUND_DIAJUKAN')->value('id');
        $statusDibatalkan = StatusTransaksi::where('kode', 'DIBATALKAN')->value('id');
        $eligibleKodes = self::ELIGIBLE_REFUND_STATUSES;

        $items = Transaksi::query()
            ->where('akun_id', $user->id)
            ->where('is_active', true)
            ->where('dibuat_sebagai_mitra', true)
            ->with(['statusTransaksi'])
            ->orderByDesc('created_at')
            ->get()
            ->filter(function ($t) use ($eligibleKodes, $statusRefundDiajukan, $statusDibatalkan) {
                $kode = $t->statusTransaksi?->kode;
                return in_array($kode, $eligibleKodes, true)
                    || (int) $t->status_transaksi_id === (int) $statusRefundDiajukan
                    || (int) $t->status_transaksi_id === (int) $statusDibatalkan;
            })
            ->values()
            ->map(function (Transaksi $t) use ($eligibleKodes) {
                $kode = $t->statusTransaksi?->kode ?? '';
                $canRequest = in_array($kode, $eligibleKodes, true);
                $snapshot = is_array($t->snapshot_produk) ? $t->snapshot_produk : (array) json_decode($t->snapshot_produk ?? '{}', true);
                $title = $snapshot['nama_paket'] ?? $snapshot['kategori_paket'] ?? 'Paket';
                $durasi = (int) ($snapshot['durasi_total'] ?? 0);
                $tglBerangkat = '';
                $tglPulang = '';
                $tanggal = $snapshot['tanggal_program_umrah'] ?? null;
                if (is_array($tanggal)) {
                    $tglBerangkat = $tanggal['departureDate'] ?? '';
                    $tglPulang = $tanggal['returnDate'] ?? '';
                    if ($tglBerangkat && $tglPulang && $durasi === 0) {
                        $d = \Carbon\Carbon::parse($tglBerangkat);
                        $r = \Carbon\Carbon::parse($tglPulang);
                        $durasi = max(1, (int) $d->diffInDays($r));
                    }
                }
                $keberangkatan = $snapshot['keberangkatan'] ?? null;
                if (is_array($keberangkatan)) {
                    $tglBerangkat = $keberangkatan['tanggal_berangkat'] ?? $tglBerangkat;
                    $tglPulang = $keberangkatan['tanggal_pulang'] ?? $tglPulang;
                    if ($tglBerangkat && $tglPulang && $durasi === 0) {
                        $d = \Carbon\Carbon::parse($tglBerangkat);
                        $r = \Carbon\Carbon::parse($tglPulang);
                        $durasi = max(1, (int) $d->diffInDays($r));
                    }
                }
                $jamaahData = $t->jamaah_data;
                $jumlahJamaah = is_array($jamaahData) ? count($jamaahData) : 1;
                if ($jumlahJamaah < 1) {
                    $jumlahJamaah = 1;
                }
                if ($durasi < 1) {
                    $durasi = 1;
                }

                return [
                    'id' => $t->id,
                    'kode_transaksi' => $t->kode_transaksi,
                    'title' => $title,
                    'subtitle' => 'Program Umrah',
                    'total_biaya' => $this->resolveTotalBiaya($t),
                    'status_kode' => $kode,
                    'status_nama' => $t->statusTransaksi?->nama_status ?? '',
                    'can_request_refund' => $canRequest,
                    'refund_alasan' => $t->refund_alasan,
                    'refund_requested_at' => $t->refund_requested_at?->toIso8601String(),
                    'durasi_hari' => $durasi,
                    'tanggal_berangkat' => $tglBerangkat,
                    'tanggal_pulang' => $tglPulang,
                    'jumlah_jamaah' => $jumlahJamaah,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Ajukan pengembalian dana untuk satu transaksi.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaksi_id' => 'required|integer|exists:transaksi_m,id',
            'alasan' => 'required|string|min:10|max:2000',
        ], [
            'alasan.min' => 'Alasan pengembalian dana minimal 10 karakter.',
        ]);

        $user = Auth::user();
        $statusRefund = StatusTransaksi::where('kode', 'REFUND_DIAJUKAN')->first();
        if (!$statusRefund) {
            return response()->json(['success' => false, 'message' => 'Status refund tidak ditemukan.'], 500);
        }

        $transaksi = Transaksi::where('id', $validated['transaksi_id'])
            ->where('akun_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $kode = $transaksi->statusTransaksi?->kode ?? '';
        if (!in_array($kode, self::ELIGIBLE_REFUND_STATUSES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini tidak dapat diajukan refund. Hanya transaksi dengan status Menunggu Verifikasi, Diproses, atau Terkonfirmasi yang dapat diajukan.',
            ], 422);
        }

        $transaksi->update([
            'status_transaksi_id' => $statusRefund->id,
            'refund_alasan' => $validated['alasan'],
            'refund_requested_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pengembalian dana berhasil dikirim. Tim kami akan memproses permintaan Anda.',
            'data' => ['transaksi_id' => $transaksi->id],
        ]);
    }
}
