<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaketEdutrip;
use App\Models\Transaksi;
use App\Models\JenisTransaksi;
use App\Models\StatusPembayaran;
use App\Models\StatusTransaksi;
use Illuminate\Support\Facades\DB;

class PaketEdutripController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/edutrip/transaksi",
     *     summary="Daftar transaksi program edutrip (khusus superadmin)",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar transaksi program edutrip",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function indexTransaksiEdutrip(Request $request)
    {

        $transaksi = Transaksi::where('jenis_transaksi_id', 3)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar transaksi program edutrip',
            'data' => $transaksi,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/edutrip/paket",
     *     summary="Daftar paket edutrip aktif",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar paket edutrip berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Terjadi kesalahan"
     *     )
     * )
     */
    public function getPaketEdutrip(Request $request)
    {
        try {
            $query = PaketEdutrip::select([
                'id',
                'nama_paket',
                'jumlah_hari',
                'deskripsi',
            ])->orderBy('nama_paket');

            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');
            if ($tglAwal && $tglAkhir) {
                $query->whereDate('created_at', '>=', $tglAwal)
                    ->whereDate('created_at', '<=', $tglAkhir);
            }

            $paketEdutrip = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Daftar paket edutrip berhasil diambil',
                'data' => $paketEdutrip,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/edutrip/pesan",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     summary="Pesan paket edutrip (Non Payment)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_lengkap","no_whatsapp","produk_id","tanggal_kunjungan","jam_kunjungan"},
     *             @OA\Property(property="gelar_id", type="integer", example=1),
     *             @OA\Property(property="nama_lengkap", type="string", example="Ahmad Santoso"),
     *             @OA\Property(property="no_whatsapp", type="string", example="08123456789"),
     *             @OA\Property(property="deskripsi", type="string", example="Rombongan sekolah 50 orang"),
     *             @OA\Property(property="produk_id", type="integer", example=1),
     *             @OA\Property(property="tanggal_kunjungan", type="string", format="date", example="2025-10-15"),
     *             @OA\Property(property="jam_kunjungan", type="string", example="09:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pesanan berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pemesanan paket edutrip berhasil dibuat"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function pesanPaketEdutrip(Request $request)
    {
        $request->validate([
            'gelar_id' => 'nullable|integer|exists:gelar_m,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_whatsapp' => 'required|string|max:20',
            'deskripsi' => 'nullable|string',
            'produk_id' => 'required|integer|exists:paket_edutrip_m,id',
            'tanggal_kunjungan' => 'required|date|after_or_equal:today',
            'jam_kunjungan' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Ambil user yang sedang login
            $userId = auth()->id();

            // Ambil paket edutrip
            $paket = PaketEdutrip::findOrFail($request->produk_id);

            // Snapshot produk (simple, sesuai struktur tabel)
            $snapshot = [
                'id' => $paket->id,
                'nama_paket' => $paket->nama_paket,
                'jumlah_hari' => $paket->jumlah_hari,
                'deskripsi' => $paket->deskripsi,
                'tanggal_kunjungan' => $request->tanggal_kunjungan,
                'jam_kunjungan' => $request->jam_kunjungan,
            ];

            // Ambil jenis transaksi untuk edutrip
            $jenisTransaksi = JenisTransaksi::where('kode', 'EDUTRIP')->firstOrFail();

            // Generate kode transaksi dengan format: KODE-DDMMYY-USERID-AUTOINCREMENT
            $tanggal = date('dmy');
            $lastTransaksi = Transaksi::where('jenis_transaksi_id', $jenisTransaksi->id)
                ->whereDate('created_at', today())
                ->count();
            $autoIncrement = str_pad($lastTransaksi + 1, 4, '0', STR_PAD_LEFT);
            $kodeTransaksi = $jenisTransaksi->kode . '-' . $tanggal . '-' . $userId . '-' . $autoIncrement;

            $transaksi = Transaksi::create([
                'is_active' => true,
                'akun_id' => $userId,
                'dibuat_sebagai_mitra' => (bool) $request->input('dibuat_sebagai_mitra', false),
                'gelar_id' => $request->gelar_id ?? null,
                'nama_lengkap' => $request->nama_lengkap,
                'no_whatsapp' => $request->no_whatsapp,
                'provinsi_id' => null,
                'kota_id' => null,
                'kecamatan_id' => null,
                'alamat_lengkap' => null,
                'deskripsi' => $request->deskripsi ?? null,
                'jenis_transaksi_id' => $jenisTransaksi->id,
                'produk_id' => $paket->id,
                'snapshot_produk' => $snapshot,
                'jamaah_data' => null, // Tidak ada data jamaah untuk edutrip
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => false, // Non payment untuk order edutrip
                'total_biaya' => 0, // Tidak ada harga untuk non-payment
                'status_pembayaran_id' => StatusPembayaran::where('kode', 'NON_PAYMENT')->value('id'),
                'status_transaksi_id' => StatusTransaksi::where('kode', 'BELUM_DIHUBUNGI')->value('id'),
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pemesanan paket edutrip berhasil dibuat',
                'data' => [
                    'transaksi' => $transaksi,
                    'kode_transaksi' => $kodeTransaksi,
                    'tanggal_kunjungan' => $request->tanggal_kunjungan,
                    'jam_kunjungan' => $request->jam_kunjungan,
                ],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat pemesanan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/sistem-admin/paket-edutrip/create-paket-edutrip",
     *     summary="Buat paket edutrip baru",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_paket","jumlah_hari","deskripsi"},
     *             @OA\Property(property="nama_paket", type="string", example="test paket"),
     *             @OA\Property(property="jumlah_hari", type="integer", example=1),
     *             @OA\Property(property="deskripsi", type="string", example="test")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paket edutrip berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function createPaketEdutrip(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jumlah_hari' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $paket = PaketEdutrip::create([
                'nama_paket' => $request->nama_paket,
                'jumlah_hari' => $request->jumlah_hari,
                'deskripsi' => $request->deskripsi ?? '',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Paket edutrip berhasil dibuat',
                'data' => $paket,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat paket edutrip: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/sistem-admin/paket-edutrip/update-paket-edutrip/{id}",
     *     summary="Update paket edutrip",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID paket edutrip",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nama_paket","jumlah_hari","deskripsi"},
     *             @OA\Property(property="nama_paket", type="string", example="test paket"),
     *             @OA\Property(property="jumlah_hari", type="integer", example=1),
     *             @OA\Property(property="deskripsi", type="string", example="test")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paket edutrip berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paket edutrip tidak ditemukan"
     *     )
     * )
     */
    public function updatePaketEdutrip(Request $request, $id)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jumlah_hari' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $paket = PaketEdutrip::findOrFail($id);

            $paket->update([
                'nama_paket' => $request->nama_paket,
                'jumlah_hari' => $request->jumlah_hari,
                'deskripsi' => $request->deskripsi ?? '',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Paket edutrip berhasil diupdate',
                'data' => $paket,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate paket edutrip: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/sistem-admin/paket-edutrip/delete-paket-edutrip/{id}",
     *     summary="Hapus paket edutrip",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID paket edutrip",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paket edutrip berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paket edutrip tidak ditemukan"
     *     )
     * )
     */
    public function deletePaketEdutrip($id)
    {
        try {
            $paket = PaketEdutrip::findOrFail($id);
            $paket->delete();

            return response()->json([
                'status' => true,
                'message' => 'Paket edutrip berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus paket edutrip: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/edutrip/submission",
     *     summary="Daftar submission edutrip milik user/mitra yang login",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter status: belum, diproses, selesai, batal",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tglAwal",
     *         in="query",
     *         description="Tanggal awal filter (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tglAkhir",
     *         in="query",
     *         description="Tanggal akhir filter (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar submission edutrip berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */
    public function getListSubmissionEdutrip(Request $request)
    {
        try {
            $userId = auth()->id();
            $status = $request->input('status');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('transaksi_m as tm')
                ->leftjoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftjoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftjoin('paket_edutrip_m as pem', 'pem.id', '=', 'tm.produk_id')
                ->leftjoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftjoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->select(
                    'tm.id',
                    'gm.gelar',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.deskripsi',
                    'pem.nama_paket',
                    'pem.jumlah_hari',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'tm.tanggal_transaksi',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'stm.kode as status_kode',
                    'tm.snapshot_produk',
                    'tm.created_at as tgl_pemesanan',
                )
                ->where('tm.is_active', true)
                ->where('jm.id', 3) // Jenis transaksi edutrip
                ->where('tm.akun_id', $userId); // Hanya submission milik user yang login
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween(DB::raw("DATE(tm.snapshot_produk->>'tanggal_kunjungan')"), [$tglAwal, $tglAkhir]);
            }

            // Filter berdasarkan status
            if ($status) {
                switch ($status) {
                    case 'belum':
                        $query->whereIn('stm.kode', ['MENUNGGU_PEMBAYARAN', 'BELUM_DIHUBUNGI', 'DIHUBUNGI']);
                        break;
                    case 'diproses':
                        $query->whereIn('stm.kode', ['DIPROSES', 'TERKONFIRMASI']);
                        break;
                    case 'selesai':
                        $query->where('stm.kode', 'SELESAI');
                        break;
                    case 'batal':
                        $query->whereIn('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN']);
                        break;
                }
            }

            $data = $query->orderBy('tm.created_at', 'desc')->get();

            $data = $data->map(function ($item) {
                $item->snapshot_produk = json_decode($item->snapshot_produk);
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'List submission edutrip berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data submission edutrip: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/edutrip/submission/{id}",
     *     summary="Detail submission edutrip berdasarkan ID",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID submission edutrip",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail submission edutrip berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Submission edutrip tidak ditemukan"
     *     )
     * )
     */
    public function getDetailSubmissionEdutrip($id)
    {
        try {
            $userId = auth()->id();

            $submission = DB::table('transaksi_m as tm')
                ->leftjoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftjoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftjoin('paket_edutrip_m as pem', 'pem.id', '=', 'tm.produk_id')
                ->leftjoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftjoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->select(
                    'tm.id',
                    'gm.gelar',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.deskripsi',
                    'pem.nama_paket',
                    'pem.id as paket_edutrip_id',
                    'pem.jumlah_hari',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'tm.tanggal_transaksi',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'stm.kode as status_kode',
                    'tm.snapshot_produk',
                    'tm.created_at as tgl_pemesanan',
                    'tm.updated_at',
                )
                ->where('tm.is_active', true)
                ->where('jm.id', 3) // Jenis transaksi edutrip
                ->where('tm.akun_id', $userId) // Hanya submission milik user yang login
                ->where('tm.id', $id)
                ->first();

            if (!$submission) {
                return response()->json([
                    'status' => false,
                    'message' => 'Submission edutrip tidak ditemukan',
                ], 404);
            }

            $submission->snapshot_produk = json_decode($submission->snapshot_produk);

            return response()->json([
                'status' => true,
                'message' => 'Detail submission edutrip berhasil diambil',
                'data' => $submission,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil detail submission edutrip: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/edutrip/submission/{id}/update-status",
     *     summary="Update status submission edutrip",
     *     tags={"PaketEdutrip"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID submission edutrip",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status_id"},
     *             @OA\Property(property="status_id", type="integer", example=1, description="ID status transaksi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status submission edutrip berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Submission edutrip tidak ditemukan"
     *     )
     * )
     */
    public function updateStatusSubmissionEdutrip(Request $request, $id)
    {
        try {
            $userId = auth()->id();

            // Validasi input
            $validated = $request->validate([
                'status_id' => 'required|integer|exists:status_transaksi_m,id',
            ]);

            // Cari submission edutrip milik user yang login
            $transaksi = Transaksi::where('id', $id)
                ->where('akun_id', $userId)
                ->where('jenis_transaksi_id', 3) // Jenis transaksi edutrip
                ->where('is_active', true)
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Submission edutrip tidak ditemukan',
                ], 404);
            }

            // Update status
            $transaksi->status_transaksi_id = $validated['status_id'];
            $transaksi->save();

            return response()->json([
                'status' => true,
                'message' => 'Status submission edutrip berhasil diperbarui',
                'data' => [
                    'id' => $transaksi->id,
                    'status_transaksi_id' => $transaksi->status_transaksi_id,
                    'updated_at' => $transaksi->updated_at,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update status submission edutrip: ' . $th->getMessage(),
            ], 500);
        }
    }
}
