<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\StoresDocuments;
use App\Models\JenisTransaksi;
use App\Models\PembayaranTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use StoresDocuments;
    /**
     * Daftar pesanan (transaksi).
     * - User: milik user yang login (halaman Pesanan Saya / account/orders).
     * - Admin: scope=admin & role superadmin → semua permintaan custom (REQUEST), format response sama.
     */
    public function index(Request $request): JsonResponse
    {
        $t0 = microtime(true);
        $user = Auth::user();
        $isAdminScope = $request->get('scope') === 'admin' && $user->hasRole('superadmin');

        if ($isAdminScope) {
            return $this->indexAdmin($request);
        }

        $perPage = max(5, min(50, (int) $request->get('per_page', 10)));
        $page = max(1, (int) $request->get('page', 1));
        $statusIds = $request->get('status_ids');
        if (is_string($statusIds)) {
            $statusIds = array_filter(array_map('intval', explode(',', $statusIds)));
        } elseif (!is_array($statusIds)) {
            $statusIds = null;
        }
        $search = $request->get('q');
        $search = is_string($search) ? trim($search) : '';

        // Hanya transaksi yang dibuat sebagai user (bukan dari dashboard mitra)
        $query = Transaksi::query()
            ->where('akun_id', $user->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('dibuat_sebagai_mitra', false)->orWhereNull('dibuat_sebagai_mitra');
            })
            ->with(['statusTransaksi', 'statusPembayaran'])
            ->orderByDesc('created_at');

        if ($statusIds !== null && $statusIds !== []) {
            $query->whereIn('status_transaksi_id', $statusIds);
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $driver = DB::connection()->getDriverName();
            $query->where(function ($q) use ($like, $driver) {
                $q->where('kode_transaksi', 'like', $like)
                    ->orWhere('nama_lengkap', 'like', $like);
                if ($driver === 'pgsql') {
                    $q->orWhereRaw("snapshot_produk->>'nama_paket' ILIKE ?", [$like]);
                } else {
                    $q->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(snapshot_produk, "$.nama_paket")) LIKE ?', [$like]);
                }
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Transaksi $t) {
            return $this->formatOrderItem($t);
        })->values()->all();

        $controllerMs = round((microtime(true) - $t0) * 1000);
        \Illuminate\Support\Facades\Log::channel('single')->info('[Laravel account/orders] controller', ['controllerMs' => $controllerMs]);

        return response()->json([
            'status' => true,
            'message' => 'Daftar pesanan',
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Daftar pesanan mitra (hanya transaksi yang dibuat dari dashboard mitra).
     * Dipanggil dari halaman Mitra > Daftar Pesanan. Response format sama dengan index (user).
     */
    public function indexMitra(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = max(5, min(50, (int) $request->get('per_page', 10)));
        $page = max(1, (int) $request->get('page', 1));
        $statusIds = $request->get('status_ids');
        if (is_string($statusIds)) {
            $statusIds = array_filter(array_map('intval', explode(',', $statusIds)));
        } elseif (! is_array($statusIds)) {
            $statusIds = null;
        }
        $search = $request->get('q');
        $search = is_string($search) ? trim($search) : '';

        $query = Transaksi::query()
            ->where('akun_id', $user->id)
            ->where('is_active', true)
            ->where('dibuat_sebagai_mitra', true)
            ->with(['statusTransaksi', 'statusPembayaran'])
            ->orderByDesc('created_at');

        if ($statusIds !== null && $statusIds !== []) {
            $query->whereIn('status_transaksi_id', $statusIds);
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $driver = DB::connection()->getDriverName();
            $query->where(function ($q) use ($like, $driver) {
                $q->where('kode_transaksi', 'like', $like)
                    ->orWhere('nama_lengkap', 'like', $like);
                if ($driver === 'pgsql') {
                    $q->orWhereRaw("snapshot_produk->>'nama_paket' ILIKE ?", [$like]);
                } else {
                    $q->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(snapshot_produk, "$.nama_paket")) LIKE ?', [$like]);
                }
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $data = $paginator->getCollection()->map(function (Transaksi $t) {
            return $this->formatOrderItem($t);
        })->values()->all();

        return response()->json([
            'status' => true,
            'message' => 'Daftar pesanan mitra',
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * List permintaan custom untuk admin (jenis REQUEST). Response format sama dengan index user.
     */
    private function indexAdmin(Request $request): JsonResponse
    {
        $jenisRequest = JenisTransaksi::where('kode', 'REQUEST')->first();
        if (! $jenisRequest) {
            return response()->json([
                'status' => true,
                'message' => 'Daftar pesanan',
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 10, 'total' => 0],
                'counts' => ['belum_diproses' => 0, 'diproses' => 0, 'selesai' => 0, 'dibatalkan' => 0],
            ]);
        }

        $perPage = max(5, min(100, (int) $request->get('per_page', 10)));
        $page = max(1, (int) $request->get('page', 1));
        $statusTab = $request->get('status'); // belum | diproses | selesai | batal
        $tglAwal = $request->get('tglAwal');
        $tglAkhir = $request->get('tglAkhir');

        $query = Transaksi::query()
            ->where('is_active', true)
            ->where('jenis_transaksi_id', $jenisRequest->id)
            ->with(['statusTransaksi', 'statusPembayaran'])
            ->orderByDesc('created_at');

        if ($tglAwal && $tglAkhir) {
            $query->whereBetween('created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
        }

        $statusKodes = [
            'belum' => ['BELUM_DIHUBUNGI', 'DIHUBUNGI', 'MENUNGGU_PEMBAYARAN'],
            'diproses' => ['DIPROSES', 'TERKONFIRMASI', 'MENUNGGU_VERIFIKASI_PEMBAYARAN'],
            'selesai' => ['SELESAI'],
            'batal' => ['DIBATALKAN', 'REFUND_DIAJUKAN'],
        ];
        if ($statusTab && isset($statusKodes[$statusTab])) {
            $query->whereHas('statusTransaksi', function ($q) use ($statusKodes, $statusTab) {
                $q->whereIn('kode', $statusKodes[$statusTab]);
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Transaksi $t) {
            $arr = $this->formatOrderItem($t);
            $arr['tgl_pemesanan'] = $t->created_at?->format('Y-m-d H:i:s');
            $arr['nama_paket'] = $this->namaPaketFromSnapshot($t->snapshot_produk);
            $arr['gelar'] = $t->gelar_id ? (DB::table('gelar_m')->where('id', $t->gelar_id)->value('gelar')) : null;
            return $arr;
        })->values()->all();

        $counts = $this->getCountsPermintaanCustom($jenisRequest->id, $tglAwal, $tglAkhir);

        return response()->json([
            'status' => true,
            'message' => 'Daftar pesanan',
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'counts' => $counts,
        ]);
    }

    private function formatOrderItem(Transaksi $t): array
    {
        $arr = $t->toArray();
        $arr['status_nama'] = $t->statusTransaksi?->nama_status ?? '';
        $arr['status_kode'] = $t->statusTransaksi?->kode ?? '';
        $arr['status_pembayaran_nama'] = $t->statusPembayaran?->nama_status ?? '';
        $arr['status_pembayaran_kode'] = $t->statusPembayaran?->kode ?? '';
        $totalBiaya = isset($arr['total_biaya']) ? (float) $arr['total_biaya'] : 0;
        if ($totalBiaya <= 0) {
            $snap = is_array($arr['snapshot_produk'] ?? null) ? $arr['snapshot_produk'] : [];
            $hargaPerPax = isset($snap['harga_per_pax']) ? (float) $snap['harga_per_pax'] : 0;
            $jamaahData = $arr['jamaah_data'] ?? null;
            $jamaahCount = is_array($jamaahData) ? max(1, count($jamaahData)) : 1;
            if ($hargaPerPax > 0) {
                $arr['total_biaya'] = (string) ($hargaPerPax * $jamaahCount);
            }
        }
        $arr['snapshot_produk'] = $this->enrichSnapshotUmrah($arr['snapshot_produk'] ?? null, (int) ($t->jenis_transaksi_id ?? 0), (int) ($t->produk_id ?? 0), (int) ($t->keberangkatan_id ?? 0));
        return $arr;
    }

    /**
     * Untuk pesanan paket umrah: isi durasi_total dan keberangkatan di snapshot jika belum ada
     * (agar daftar pesanan mitra bisa menampilkan durasi untuk order lama).
     */
    private function enrichSnapshotUmrah($snapshot, int $jenisTransaksiId, int $produkId, int $keberangkatanId): ?array
    {
        $jenisUmrah = JenisTransaksi::where('kode', 'PAKET_UMRAH')->value('id');
        if ($jenisUmrah === null || $jenisTransaksiId !== (int) $jenisUmrah) {
            return is_array($snapshot) ? $snapshot : (array) $snapshot;
        }
        $snap = is_array($snapshot) ? $snapshot : (is_object($snapshot) ? (array) $snapshot : []);
        $needEnrich = empty($snap['durasi_total']) && empty($snap['keberangkatan']);
        if (! $needEnrich || $produkId <= 0) {
            return $snap;
        }
        $paket = DB::table('paket_umrah_m')->where('id', $produkId)->first();
        if ($paket) {
            $snap['durasi_total'] = $paket->durasi_total ?? null;
        }
        if ($keberangkatanId > 0) {
            $k = DB::table('paket_umrah_keberangkatan_t')->where('id', $keberangkatanId)->first();
            if ($k) {
                $snap['keberangkatan'] = [
                    'id' => $k->id,
                    'tanggal_berangkat' => $k->tanggal_berangkat,
                    'tanggal_pulang' => $k->tanggal_pulang,
                    'jam_berangkat' => $k->jam_berangkat ?? null,
                    'jam_pulang' => $k->jam_pulang ?? null,
                ];
            }
        }
        return $snap;
    }

    private function namaPaketFromSnapshot($snap): string
    {
        if (! $snap) {
            return 'Permintaan Custom';
        }
        $arr = is_array($snap) ? $snap : (array) $snap;
        return $arr['kategori_paket'] ?? $arr['tipe'] ?? $arr['nama_paket'] ?? 'Permintaan Custom';
    }

    /**
     * Enrich jamaah_data dengan dokumen_ktp_id & dokumen_paspor_id dari master jemaah
     * untuk item yang punya id (jamaah_m) atau bisa dicocokkan via akun + nama/NIK (order lama).
     */
    private function enrichJamaahDataWithDocumentIds(?array $jamaahData, int $akunId = 0): ?array
    {
        if (! is_array($jamaahData) || count($jamaahData) === 0) {
            return $jamaahData;
        }

        $ownerTypeId = $this->getOwnerTypeId('jamaah');

        foreach ($jamaahData as $i => $j) {
            $needEnrich = empty($j['dokumen_ktp_id']) && empty($j['dokumen_paspor_id']);
            if (! $needEnrich) {
                continue;
            }

            $jamaahId = isset($j['id']) ? (is_numeric($j['id']) ? (int) $j['id'] : null) : null;

            // Fallback: order lama tanpa id — cari jemaah dari akun yang sama by nama atau NIK
            if ($jamaahId === null && $akunId > 0) {
                $nama = trim((string) ($j['nama'] ?? $j['fullName'] ?? ''));
                $nik = trim((string) ($j['nik'] ?? ''));
                if ($nama !== '' || $nik !== '') {
                    $q = DB::table('jamaah_m')->where('akun_id', $akunId)->where('is_active', true);
                    if ($nama !== '' && $nik !== '') {
                        $q->where(function ($q2) use ($nama, $nik) {
                            $q2->where('nama_lengkap', $nama)->orWhere('nomor_identitas', $nik);
                        });
                    } elseif ($nik !== '') {
                        $q->where('nomor_identitas', $nik);
                    } else {
                        $q->where('nama_lengkap', $nama);
                    }
                    $found = $q->first();
                    $jamaahId = $found ? (int) $found->id : null;
                }
            }

            if ($jamaahId === null) {
                continue;
            }

            $docs = $this->getDocumentsByOwner($ownerTypeId, $jamaahId);
            $ktpId = null;
            $pasporId = null;
            foreach ($docs as $d) {
                if (($d->tipe_dokumen ?? '') === 'ktp') {
                    $ktpId = $d->id;
                }
                if (($d->tipe_dokumen ?? '') === 'paspor') {
                    $pasporId = $d->id;
                }
            }
            $jamaahData[$i]['dokumen_ktp_id'] = $ktpId;
            $jamaahData[$i]['dokumen_paspor_id'] = $pasporId;
        }

        return $jamaahData;
    }

    private function getCountsPermintaanCustom(int $jenisRequestId, ?string $tglAwal, ?string $tglAkhir): array
    {
        $query = Transaksi::query()
            ->join('status_transaksi_m as stm', 'stm.id', '=', 'transaksi_m.status_transaksi_id')
            ->where('transaksi_m.is_active', true)
            ->where('transaksi_m.jenis_transaksi_id', $jenisRequestId);

        if ($tglAwal && $tglAkhir) {
            $query->whereBetween('transaksi_m.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
        }

        $raw = $query->selectRaw("
            COALESCE(SUM(CASE WHEN stm.kode IN ('BELUM_DIHUBUNGI','DIHUBUNGI','MENUNGGU_PEMBAYARAN') THEN 1 ELSE 0 END), 0) AS belum_diproses,
            COALESCE(SUM(CASE WHEN stm.kode IN ('DIPROSES','TERKONFIRMASI','MENUNGGU_VERIFIKASI_PEMBAYARAN') THEN 1 ELSE 0 END), 0) AS diproses,
            COALESCE(SUM(CASE WHEN stm.kode = 'SELESAI' THEN 1 ELSE 0 END), 0) AS selesai,
            COALESCE(SUM(CASE WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') THEN 1 ELSE 0 END), 0) AS dibatalkan
        ")->first();

        return [
            'belum_diproses' => (int) ($raw->belum_diproses ?? 0),
            'diproses' => (int) ($raw->diproses ?? 0),
            'selesai' => (int) ($raw->selesai ?? 0),
            'dibatalkan' => (int) ($raw->dibatalkan ?? 0),
        ];
    }

    /**
     * Detail satu pesanan (transaksi).
     * - User: milik user yang login.
     * - Admin: scope=admin & role superadmin → boleh lihat semua (untuk permintaan custom).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $isAdminScope = $request->get('scope') === 'admin' && $user->hasRole('superadmin');

        $query = Transaksi::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->with(['statusTransaksi', 'statusPembayaran']);

        if (! $isAdminScope) {
            $query->where('akun_id', $user->id);
        }

        $transaksi = $query->first();

        if (! $transaksi) {
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        $arr = $this->formatOrderItem($transaksi);
        if ($isAdminScope) {
            $arr['tgl_pemesanan'] = $transaksi->created_at?->format('Y-m-d H:i:s');
            $arr['nama_paket'] = $this->namaPaketFromSnapshot($transaksi->snapshot_produk);
            $arr['gelar'] = $transaksi->gelar_id ? (DB::table('gelar_m')->where('id', $transaksi->gelar_id)->value('gelar')) : null;
        }

        // Enrich jamaah_data dengan dokumen_ktp_id & dokumen_paspor_id dari master jemaah jika belum ada (order lama / data belum tersimpan)
        $arr['jamaah_data'] = $this->enrichJamaahDataWithDocumentIds($arr['jamaah_data'] ?? null, (int) $transaksi->akun_id);

        // Daftar pembayaran (upload bukti dari halaman Bayar Sekarang) untuk ditampilkan di detail order / admin
        $arr['pembayaran'] = PembayaranTransaksi::where('transaksi_id', $transaksi->id)
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->orderByDesc('created_at')
            ->get(['id', 'nominal_asli', 'nominal_transfer', 'bank_tujuan', 'status', 'bukti_pembayaran', 'validasi_manual', 'created_at', 'tanggal_transfer'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nominal_asli' => (float) $p->nominal_asli,
                    'nominal_transfer' => (float) $p->nominal_transfer,
                    'bank_tujuan' => $p->bank_tujuan,
                    'status' => $p->status,
                    'bukti_pembayaran' => $p->bukti_pembayaran,
                    'validasi_manual' => $p->validasi_manual,
                    'created_at' => $p->created_at?->toIso8601String(),
                    'tanggal_transfer' => $p->tanggal_transfer?->toIso8601String(),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'status' => true,
            'message' => 'Detail pesanan',
            'data' => $arr,
        ]);
    }

    /**
     * Upload bukti pembayaran untuk pesanan (transaksi) milik user.
     * Membuat record PembayaranTransaksi lalu menyimpan file bukti.
     */
    public function uploadBukti(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'bank_tujuan' => 'nullable|string|max:100',
            'bukti_pembayaran' => 'required|file|mimes:jpeg,jpg,png,pdf|max:2048', // 2MB
        ]);

        $user = Auth::user();
        $transaksi = Transaksi::where('id', $id)
            ->where('akun_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (! $transaksi) {
            return response()->json([
                'status' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        try {
            $nominal = (float) $request->input('nominal');
            $bankTujuan = $request->input('bank_tujuan');

            $pembayaran = PembayaranTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'nominal_asli' => $nominal,
                'nominal_transfer' => $nominal,
                'bank_tujuan' => $bankTujuan,
                'status' => 'pending',
                'validasi_manual' => true,
                'is_active' => true,
            ]);

            $file = $request->file('bukti_pembayaran');
            $path = $file->store('private/bukti_pembayaran', 'local');
            $pembayaran->bukti_pembayaran = $path;
            $pembayaran->save();

            return response()->json([
                'status' => true,
                'message' => 'Bukti pembayaran berhasil diunggah. Admin akan memverifikasi.',
                'data' => ['pembayaran_id' => $pembayaran->id],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengunggah bukti: ' . $th->getMessage(),
            ], 500);
        }
    }
}
