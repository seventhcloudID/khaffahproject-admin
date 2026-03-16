<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\JenisTransaksi;
use App\Models\StatusPembayaran;
use App\Models\StatusTransaksi;
use Illuminate\Support\Facades\DB;

class LandArrangementController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/mitra/land-arrangement/grup",
     *     summary="Request Land Arrangement untuk grup",
     *     tags={"LandArrangement"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap","no_whatsapp","alamat_lengkap","jumlah_pax","tanggal_keberangkatan","tanggal_kepulangan"},
     *                 @OA\Property(property="gelar_id", type="integer", example=1),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Ahmad Santoso"),
     *                 @OA\Property(property="no_whatsapp", type="string", example="08123456789"),
     *                 @OA\Property(property="alamat_lengkap", type="string", example="Jl. Merdeka No. 123"),
     *                 @OA\Property(property="provinsi_id", type="integer", example=1),
     *                 @OA\Property(property="kota_id", type="integer", example=10),
     *                 @OA\Property(property="kecamatan_id", type="integer", example=1),
     *                 @OA\Property(property="deskripsi", type="string", example="Request paket untuk grup sekolah"),
     *                 @OA\Property(property="jumlah_pax", type="integer", example=50),
     *                 @OA\Property(property="tanggal_keberangkatan", type="string", format="date", example="2025-10-15"),
     *                 @OA\Property(property="tanggal_kepulangan", type="string", format="date", example="2025-10-25"),
     *                 @OA\Property(property="kebutuhan_khusus", type="string", example="Butuh guide bahasa Indonesia"),
     *                 @OA\Property(property="jamaah_data", type="array", @OA\Items(
     *                     @OA\Property(property="nama", type="string"),
     *                     @OA\Property(property="nik", type="string"),
     *                     @OA\Property(property="no_paspor", type="string")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Request Land Arrangement grup berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function requestLandArrangementGrup(Request $request)
    {
        $request->validate([
            'gelar_id' => 'nullable|integer|exists:gelar_m,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_whatsapp' => 'required|string|max:20',
            'alamat_lengkap' => 'required|string',
            'provinsi_id' => 'nullable|integer|exists:provinsi_m,id',
            'kota_id' => 'nullable|integer|exists:kota_m,id',
            'kecamatan_id' => 'nullable|integer|exists:kecamatan_m,id',
            'deskripsi' => 'nullable|string',
            'jumlah_pax' => 'required|integer|min:1',
            'tanggal_keberangkatan' => 'required|date|after_or_equal:today',
            'tanggal_kepulangan' => 'required|date|after:tanggal_keberangkatan',
            'kebutuhan_khusus' => 'nullable|string',
            'jamaah_data' => 'nullable|array',
            'jamaah_data.*.nama' => 'required|string|max:255',
            'jamaah_data.*.nik' => 'nullable|string|max:16',
            'jamaah_data.*.no_paspor' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Ambil jenis transaksi REQUEST
            $jenisTransaksi = JenisTransaksi::where('kode', 'REQUEST')->firstOrFail();

            // Generate kode transaksi
            $tanggal = date('dmy');
            $lastTransaksi = Transaksi::where('jenis_transaksi_id', $jenisTransaksi->id)
                ->whereDate('created_at', today())
                ->count();
            $autoIncrement = str_pad($lastTransaksi + 1, 4, '0', STR_PAD_LEFT);
            $kodeTransaksi = 'LA-GRUP-' . $tanggal . '-' . $userId . '-' . $autoIncrement;

            // Snapshot produk untuk Land Arrangement Grup
            $snapshot = [
                'tipe' => 'LAND_ARRANGEMENT_GRUP',
                'jumlah_pax' => $request->jumlah_pax,
                'tanggal_keberangkatan' => $request->tanggal_keberangkatan,
                'tanggal_kepulangan' => $request->tanggal_kepulangan,
                'kebutuhan_khusus' => $request->kebutuhan_khusus ?? null,
            ];

            // Process jamaah data jika ada
            $jamaahData = [];
            if ($request->has('jamaah_data')) {
                foreach ($request->jamaah_data as $jamaah) {
                    $jamaahData[] = [
                        'nama' => $jamaah['nama'],
                        'nik' => $jamaah['nik'] ?? null,
                        'no_paspor' => $jamaah['no_paspor'] ?? null,
                    ];
                }
            }

            $transaksi = Transaksi::create([
                'is_active' => true,
                'akun_id' => $userId,
                'dibuat_sebagai_mitra' => true, // Land Arrangement hanya dari dashboard mitra
                'gelar_id' => $request->gelar_id ?? null,
                'nama_lengkap' => $request->nama_lengkap,
                'no_whatsapp' => $request->no_whatsapp,
                'provinsi_id' => $request->provinsi_id ?? null,
                'kota_id' => $request->kota_id ?? null,
                'kecamatan_id' => $request->kecamatan_id ?? null,
                'alamat_lengkap' => $request->alamat_lengkap,
                'deskripsi' => $request->deskripsi ?? null,
                'jenis_transaksi_id' => $jenisTransaksi->id,
                'produk_id' => null, // Land Arrangement tidak punya produk_id
                'snapshot_produk' => $snapshot,
                'jamaah_data' => !empty($jamaahData) ? $jamaahData : null,
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => false, // Non payment untuk request
                'total_biaya' => null, // Akan dihitung setelah admin memberikan penawaran
                'status_pembayaran_id' => StatusPembayaran::where('kode', 'NON_PAYMENT')->value('id'),
                'status_transaksi_id' => StatusTransaksi::where('kode', 'BELUM_DIHUBUNGI')->value('id'),
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Request Land Arrangement grup berhasil dibuat',
                'data' => [
                    'transaksi' => $transaksi,
                    'kode_transaksi' => $kodeTransaksi,
                    'jumlah_pax' => $request->jumlah_pax,
                ],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat request Land Arrangement grup: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/mitra/land-arrangement/private",
     *     summary="Request Land Arrangement untuk private",
     *     tags={"LandArrangement"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap","no_whatsapp","alamat_lengkap","jumlah_pax","tanggal_keberangkatan","tanggal_kepulangan"},
     *                 @OA\Property(property="gelar_id", type="integer", example=1),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Ahmad Santoso"),
     *                 @OA\Property(property="no_whatsapp", type="string", example="08123456789"),
     *                 @OA\Property(property="alamat_lengkap", type="string", example="Jl. Merdeka No. 123"),
     *                 @OA\Property(property="provinsi_id", type="integer", example=1),
     *                 @OA\Property(property="kota_id", type="integer", example=10),
     *                 @OA\Property(property="kecamatan_id", type="integer", example=1),
     *                 @OA\Property(property="deskripsi", type="string", example="Request paket private"),
     *                 @OA\Property(property="jumlah_pax", type="integer", example=2),
     *                 @OA\Property(property="tanggal_keberangkatan", type="string", format="date", example="2025-10-15"),
     *                 @OA\Property(property="tanggal_kepulangan", type="string", format="date", example="2025-10-25"),
     *                 @OA\Property(property="kebutuhan_khusus", type="string", example="Butuh hotel bintang 5"),
     *                 @OA\Property(property="jamaah_data", type="array", @OA\Items(
     *                     @OA\Property(property="nama", type="string"),
     *                     @OA\Property(property="nik", type="string"),
     *                     @OA\Property(property="no_paspor", type="string")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Request Land Arrangement private berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function requestLandArrangementPrivate(Request $request)
    {
        $request->validate([
            'gelar_id' => 'nullable|integer|exists:gelar_m,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_whatsapp' => 'required|string|max:20',
            'alamat_lengkap' => 'required|string',
            'provinsi_id' => 'nullable|integer|exists:provinsi_m,id',
            'kota_id' => 'nullable|integer|exists:kota_m,id',
            'kecamatan_id' => 'nullable|integer|exists:kecamatan_m,id',
            'deskripsi' => 'nullable|string',
            'jumlah_pax' => 'required|integer|min:1',
            'tanggal_keberangkatan' => 'required|date|after_or_equal:today',
            'tanggal_kepulangan' => 'required|date|after:tanggal_keberangkatan',
            'kebutuhan_khusus' => 'nullable|string',
            'jamaah_data' => 'nullable|array',
            'jamaah_data.*.nama' => 'required|string|max:255',
            'jamaah_data.*.nik' => 'nullable|string|max:16',
            'jamaah_data.*.no_paspor' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Ambil jenis transaksi REQUEST
            $jenisTransaksi = JenisTransaksi::where('kode', 'REQUEST')->firstOrFail();

            // Generate kode transaksi
            $tanggal = date('dmy');
            $lastTransaksi = Transaksi::where('jenis_transaksi_id', $jenisTransaksi->id)
                ->whereDate('created_at', today())
                ->count();
            $autoIncrement = str_pad($lastTransaksi + 1, 4, '0', STR_PAD_LEFT);
            $kodeTransaksi = 'LA-PRIVATE-' . $tanggal . '-' . $userId . '-' . $autoIncrement;

            // Snapshot produk untuk Land Arrangement Private
            $snapshot = [
                'tipe' => 'LAND_ARRANGEMENT_PRIVATE',
                'jumlah_pax' => $request->jumlah_pax,
                'tanggal_keberangkatan' => $request->tanggal_keberangkatan,
                'tanggal_kepulangan' => $request->tanggal_kepulangan,
                'kebutuhan_khusus' => $request->kebutuhan_khusus ?? null,
            ];

            // Process jamaah data jika ada
            $jamaahData = [];
            if ($request->has('jamaah_data')) {
                foreach ($request->jamaah_data as $jamaah) {
                    $jamaahData[] = [
                        'nama' => $jamaah['nama'],
                        'nik' => $jamaah['nik'] ?? null,
                        'no_paspor' => $jamaah['no_paspor'] ?? null,
                    ];
                }
            }

            $transaksi = Transaksi::create([
                'is_active' => true,
                'akun_id' => $userId,
                'dibuat_sebagai_mitra' => true,
                'gelar_id' => $request->gelar_id ?? null,
                'nama_lengkap' => $request->nama_lengkap,
                'no_whatsapp' => $request->no_whatsapp,
                'provinsi_id' => $request->provinsi_id ?? null,
                'kota_id' => $request->kota_id ?? null,
                'kecamatan_id' => $request->kecamatan_id ?? null,
                'alamat_lengkap' => $request->alamat_lengkap,
                'deskripsi' => $request->deskripsi ?? null,
                'jenis_transaksi_id' => $jenisTransaksi->id,
                'produk_id' => null, // Land Arrangement tidak punya produk_id
                'snapshot_produk' => $snapshot,
                'jamaah_data' => !empty($jamaahData) ? $jamaahData : null,
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => false, // Non payment untuk request
                'total_biaya' => null, // Akan dihitung setelah admin memberikan penawaran
                'status_pembayaran_id' => StatusPembayaran::where('kode', 'NON_PAYMENT')->value('id'),
                'status_transaksi_id' => StatusTransaksi::where('kode', 'BELUM_DIHUBUNGI')->value('id'),
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Request Land Arrangement private berhasil dibuat',
                'data' => [
                    'transaksi' => $transaksi,
                    'kode_transaksi' => $kodeTransaksi,
                    'jumlah_pax' => $request->jumlah_pax,
                ],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat request Land Arrangement private: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/mitra/land-arrangement/pemesanan-reguler",
     *     summary="Pemesanan Paket Land Arrangement Reguler",
     *     tags={"LandArrangement"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap","no_whatsapp","alamat_lengkap","paket_la_id","jumlah_pax","tanggal_keberangkatan"},
     *                 @OA\Property(property="gelar_id", type="integer", example=1),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Ahmad Santoso"),
     *                 @OA\Property(property="no_whatsapp", type="string", example="08123456789"),
     *                 @OA\Property(property="alamat_lengkap", type="string", example="Jl. Merdeka No. 123"),
     *                 @OA\Property(property="provinsi_id", type="integer", example=1),
     *                 @OA\Property(property="kota_id", type="integer", example=10),
     *                 @OA\Property(property="kecamatan_id", type="integer", example=1),
     *                 @OA\Property(property="deskripsi", type="string", example="Pemesanan paket LA reguler"),
     *                 @OA\Property(property="paket_la_id", type="integer", example=1, description="ID paket Land Arrangement dari admin"),
     *                 @OA\Property(property="jumlah_pax", type="integer", example=10),
     *                 @OA\Property(property="tanggal_keberangkatan", type="string", format="date", example="2025-10-15"),
     *                 @OA\Property(property="jamaah_data", type="array", @OA\Items(
     *                     @OA\Property(property="nama", type="string"),
     *                     @OA\Property(property="nik", type="string"),
     *                     @OA\Property(property="no_paspor", type="string")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pemesanan paket LA reguler berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function pesanPaketLAReguler(Request $request)
    {
        $request->validate([
            'gelar_id' => 'nullable|integer|exists:gelar_m,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_whatsapp' => 'required|string|max:20',
            'alamat_lengkap' => 'required|string',
            'provinsi_id' => 'nullable|integer|exists:provinsi_m,id',
            'kota_id' => 'nullable|integer|exists:kota_m,id',
            'kecamatan_id' => 'nullable|integer|exists:kecamatan_m,id',
            'deskripsi' => 'nullable|string',
            'paket_la_id' => 'required|integer', // ID paket LA dari admin (bisa dari tabel khusus atau paket_umrah_m)
            'jumlah_pax' => 'required|integer|min:1',
            'tanggal_keberangkatan' => 'required|date|after_or_equal:today',
            'jamaah_data' => 'nullable|array',
            'jamaah_data.*.nama' => 'required|string|max:255',
            'jamaah_data.*.nik' => 'nullable|string|max:16',
            'jamaah_data.*.no_paspor' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $userId = auth()->id();

            // Ambil jenis transaksi REQUEST
            $jenisTransaksi = JenisTransaksi::where('kode', 'REQUEST')->firstOrFail();

            // Generate kode transaksi
            $tanggal = date('dmy');
            $lastTransaksi = Transaksi::where('jenis_transaksi_id', $jenisTransaksi->id)
                ->whereDate('created_at', today())
                ->count();
            $autoIncrement = str_pad($lastTransaksi + 1, 4, '0', STR_PAD_LEFT);
            $kodeTransaksi = 'LA-REGULER-' . $tanggal . '-' . $userId . '-' . $autoIncrement;

            // Snapshot produk untuk Paket LA Reguler
            // Note: paket_la_id bisa merujuk ke paket_umrah_m atau tabel khusus paket LA
            $snapshot = [
                'tipe' => 'LAND_ARRANGEMENT_REGULER',
                'paket_la_id' => $request->paket_la_id,
                'jumlah_pax' => $request->jumlah_pax,
                'tanggal_keberangkatan' => $request->tanggal_keberangkatan,
            ];

            // Process jamaah data jika ada
            $jamaahData = [];
            if ($request->has('jamaah_data')) {
                foreach ($request->jamaah_data as $jamaah) {
                    $jamaahData[] = [
                        'nama' => $jamaah['nama'],
                        'nik' => $jamaah['nik'] ?? null,
                        'no_paspor' => $jamaah['no_paspor'] ?? null,
                    ];
                }
            }

            $transaksi = Transaksi::create([
                'is_active' => true,
                'akun_id' => $userId,
                'dibuat_sebagai_mitra' => true,
                'gelar_id' => $request->gelar_id ?? null,
                'nama_lengkap' => $request->nama_lengkap,
                'no_whatsapp' => $request->no_whatsapp,
                'provinsi_id' => $request->provinsi_id ?? null,
                'kota_id' => $request->kota_id ?? null,
                'kecamatan_id' => $request->kecamatan_id ?? null,
                'alamat_lengkap' => $request->alamat_lengkap,
                'deskripsi' => $request->deskripsi ?? null,
                'jenis_transaksi_id' => $jenisTransaksi->id,
                'produk_id' => $request->paket_la_id, // Paket LA ID
                'snapshot_produk' => $snapshot,
                'jamaah_data' => !empty($jamaahData) ? $jamaahData : null,
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => true, // Dengan payment untuk paket reguler
                'total_biaya' => null, // Akan dihitung berdasarkan paket dan jumlah pax
                'status_pembayaran_id' => StatusPembayaran::where('kode', 'MENUNGGU_PEMBAYARAN')->value('id'),
                'status_transaksi_id' => StatusTransaksi::where('kode', 'BELUM_DIHUBUNGI')->value('id'),
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pemesanan paket LA reguler berhasil dibuat',
                'data' => [
                    'transaksi' => $transaksi,
                    'kode_transaksi' => $kodeTransaksi,
                    'jumlah_pax' => $request->jumlah_pax,
                ],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat pemesanan paket LA reguler: ' . $th->getMessage(),
            ], 500);
        }
    }
}
