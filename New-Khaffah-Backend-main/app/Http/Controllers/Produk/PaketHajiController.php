<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaketHaji;
use App\Models\Transaksi;
use App\Models\JenisTransaksi;
use App\Models\StatusPembayaran;
use App\Models\StatusTransaksi;
use Illuminate\Support\Facades\DB;

class PaketHajiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/haji/transaksi",
     *     summary="Daftar transaksi program haji (khusus superadmin)",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar transaksi program haji",
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
    public function indexTransaksiHaji(Request $request)
    {

        $transaksi = Transaksi::where('jenis_transaksi_id', 2)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar transaksi program haji',
            'data' => $transaksi,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/haji/paket",
     *     summary="Daftar paket haji aktif",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar paket haji berhasil diambil",
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
    public function getPaketHaji(Request $request)
    {
        try {
            $query = PaketHaji::query();
            // Hanya paket aktif untuk tampilan publik. Admin pakai show_all=1 untuk lihat semua.
            if ($request->input('show_all') != '1') {
                $query->where('is_active', true);
            }
            $paketHaji = $query->select([
                    'id',
                    'nama_paket',
                    'biaya_per_pax',
                    'akomodasi',
                    'deskripsi_akomodasi',
                    'waktu_tunggu_min',
                    'waktu_tunggu_max',
                    'deskripsi_waktu_tunggu',
                    'fasilitas_tambahan',
                    'deskripsi_fasilitas',
                ])
                ->orderBy('nama_paket')
                ->get();

            // Format respons sesuai dengan format seeder
            $data = $paketHaji->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_paket' => $item->nama_paket,
                    'biaya_per_pax' => $item->biaya_per_pax,
                    'akomodasi' => $item->akomodasi ?? [],
                    'deskripsi_akomodasi' => $item->deskripsi_akomodasi,
                    'waktu_tunggu' => [
                        'min' => $item->waktu_tunggu_min,
                        'max' => $item->waktu_tunggu_max,
                        'deskripsi' => $item->deskripsi_waktu_tunggu,
                    ],
                    'fasilitas_tambahan' => $item->fasilitas_tambahan ?? [],
                    'deskripsi_fasilitas' => $item->deskripsi_fasilitas,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Daftar paket haji berhasil diambil',
                'data' => $data,
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
     *     path="/api/haji/pesan",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     summary="Pesan paket haji (Non Payment)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap","no_whatsapp","alamat_lengkap","produk_id"},
     *                 @OA\Property(property="gelar_id", type="integer", example=1),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Ahmad Santoso"),
     *                 @OA\Property(property="no_whatsapp", type="string", example="08123456789"),
     *                 @OA\Property(property="alamat_lengkap", type="string", example="Jl. Merdeka No. 123"),
     *                 @OA\Property(property="deskripsi", type="string", example="Catatan tambahan"),
     *                 @OA\Property(property="produk_id", type="integer", example=1),
     *                 @OA\Property(property="provinsi_id", type="integer", example=1),
     *                 @OA\Property(property="kota_id", type="integer", example=10),
     *                 @OA\Property(property="kecamatan_id", type="integer", example=1),
     *                 @OA\Property(property="jamaah_data[0][nama]", type="string", example="Ahmad"),
     *                 @OA\Property(property="jamaah_data[0][nik]", type="string", example="1234567890123456"),
     *                 @OA\Property(property="jamaah_data[0][no_paspor]", type="string", example="A1234567"),
     *                 @OA\Property(property="jamaah_data[0][foto_ktp]", type="string", format="binary"),
     *                 @OA\Property(property="jamaah_data[0][foto_paspor]", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pesanan berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pemesanan paket haji berhasil dibuat"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function pesanPaketHaji(Request $request)
    {
        // Validasi data jamaah dengan file
        $request->validate([
            'gelar_id' => 'nullable|integer|exists:gelar_m,id',
            'nama_lengkap' => 'required|string|max:255',
            'no_whatsapp' => 'required|string|max:20',
            'alamat_lengkap' => 'required|string',
            'deskripsi' => 'nullable|string',
            'produk_id' => 'required|integer|exists:paket_haji_m,id',
            'jamaah_data' => 'nullable|array',
            'jamaah_data.*.nama' => 'required|string|max:255',
            'jamaah_data.*.nik' => 'required|string|max:16',
            'jamaah_data.*.no_paspor' => 'required|string|max:20',
            'jamaah_data.*.foto_ktp' => 'nullable|file|image|max:2048',
            'jamaah_data.*.foto_paspor' => 'nullable|file|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Ambil user yang sedang login
            $userId = auth()->id();

            // Ambil paket haji
            $paket = PaketHaji::findOrFail($request->produk_id);

            // Snapshot produk
            $snapshot = [
                'id' => $paket->id,
                'nama_paket' => $paket->nama_paket,
                'biaya_per_pax' => $paket->biaya_per_pax,
                'akomodasi' => $paket->akomodasi,
                'deskripsi_akomodasi' => $paket->deskripsi_akomodasi,
                'waktu_tunggu_min' => $paket->waktu_tunggu_min,
                'waktu_tunggu_max' => $paket->waktu_tunggu_max,
                'deskripsi_waktu_tunggu' => $paket->deskripsi_waktu_tunggu,
                'fasilitas_tambahan' => $paket->fasilitas_tambahan,
                'deskripsi_fasilitas' => $paket->deskripsi_fasilitas,
            ];

            // Ambil jenis transaksi
            $jenisTransaksi = JenisTransaksi::where('kode', 'HAJI')->firstOrFail();

            // Generate kode transaksi dengan format: KODE-DDMMYY-USERID-AUTOINCREMENT
            $tanggal = date('dmy'); // format: 120525
            $lastTransaksi = Transaksi::where('jenis_transaksi_id', $jenisTransaksi->id)
                ->whereDate('created_at', today())
                ->count();
            $autoIncrement = str_pad($lastTransaksi + 1, 4, '0', STR_PAD_LEFT);
            $kodeTransaksi = $jenisTransaksi->kode . '-' . $tanggal . '-' . $userId . '-' . $autoIncrement;

            // Process jamaah data dengan upload file
            $jamaahData = [];
            if ($request->has('jamaah_data')) {
                foreach ($request->jamaah_data as $index => $jamaah) {
                    $jamaahItem = [
                        'nama' => $jamaah['nama'],
                        'nik' => $jamaah['nik'],
                        'no_paspor' => $jamaah['no_paspor'],
                    ];

                    // Upload foto KTP ke storage private/ktp
                    if (isset($jamaah['foto_ktp']) && $jamaah['foto_ktp'] instanceof \Illuminate\Http\UploadedFile) {
                        $ktpPath = $jamaah['foto_ktp']->store('private/ktp', 'local');
                        $jamaahItem['foto_ktp_path'] = $ktpPath;
                    }

                    // Upload foto Paspor ke storage private/paspor
                    if (isset($jamaah['foto_paspor']) && $jamaah['foto_paspor'] instanceof \Illuminate\Http\UploadedFile) {
                        $pasporPath = $jamaah['foto_paspor']->store('private/paspor', 'local');
                        $jamaahItem['foto_paspor_path'] = $pasporPath;
                    }

                    $jamaahData[] = $jamaahItem;
                }
            }

            // Hitung total harga
            $jumlahJamaah = count($jamaahData) > 0 ? count($jamaahData) : 1;
            $totalHarga = $paket->biaya_per_pax * $jumlahJamaah;

            $transaksi = Transaksi::create([
                'is_active' => true,
                'akun_id' => $userId,
                'dibuat_sebagai_mitra' => (bool) $request->input('dibuat_sebagai_mitra', false),
                'gelar_id' => $request->gelar_id ?? null,
                'nama_lengkap' => $request->nama_lengkap,
                'no_whatsapp' => $request->no_whatsapp,
                'provinsi_id' => $request->provinsi_id,
                'kota_id' => $request->kota_id,
                'kecamatan_id' => $request->kecamatan_id,
                'alamat_lengkap' => $request->alamat_lengkap,
                'deskripsi' => $request->deskripsi ?? null,
                'jenis_transaksi_id' => $jenisTransaksi->id,
                'produk_id' => $paket->id,
                'snapshot_produk' => $snapshot,
                'jamaah_data' => $jamaahData,
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => false, // Non payment untuk order haji
                'total_biaya' => $totalHarga,
                'status_pembayaran_id' => StatusPembayaran::where('kode', 'NON_PAYMENT')->value('id'),
                'status_transaksi_id' => StatusTransaksi::where('kode', 'BELUM_DIHUBUNGI')->value('id'),
                'nomor_pembayaran' => null, // Null karena non payment
                'tanggal_transaksi' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pemesanan paket haji berhasil dibuat',
                'data' => [
                    'transaksi' => $transaksi,
                    'kode_transaksi' => $kodeTransaksi,
                    'total_biaya' => $totalHarga,
                    'jumlah_jamaah' => $jumlahJamaah,
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

    public function getPaketHajiById($id)
    {
        try {
            $paketHaji = PaketHaji::where('is_active', true)
                ->select([
                    'id',
                    'nama_paket',
                    'biaya_per_pax',
                    'akomodasi',
                    'deskripsi_akomodasi',
                    'waktu_tunggu_min',
                    'waktu_tunggu_max',
                    'deskripsi_waktu_tunggu',
                    'fasilitas_tambahan',
                    'deskripsi_fasilitas',
                ])
                ->where('id', $id)
                ->first();

            // Format respons sesuai dengan format seeder
            $data = $paketHaji ? [
                'id' => $paketHaji->id,
                'nama_paket' => $paketHaji->nama_paket,
                'biaya_per_pax' => $paketHaji->biaya_per_pax,
                'akomodasi' => $paketHaji->akomodasi ?? [],
                'deskripsi_akomodasi' => $paketHaji->deskripsi_akomodasi,
                'waktu_tunggu' => [
                    'min' => $paketHaji->waktu_tunggu_min,
                    'max' => $paketHaji->waktu_tunggu_max,
                    'deskripsi' => $paketHaji->deskripsi_waktu_tunggu,
                ],
                'fasilitas_tambahan' => $paketHaji->fasilitas_tambahan ?? [],
                'deskripsi_fasilitas' => $paketHaji->deskripsi_fasilitas,
            ] : null;

            return response()->json([
                'status' => true,
                'message' => 'Daftar paket haji berhasil diambil',
                'data' => $data,
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
     *     path="/api/haji/paket/store",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     summary="Simpan paket haji baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nama_paket","biaya_per_pax"},
     *                 @OA\Property(property="nama_paket", type="string", example="Haji Plus"),
     *                 @OA\Property(property="biaya_per_pax", type="number", format="decimal", example=111),
     *                 @OA\Property(property="deskripsi_akomodasi", type="string", example="Akomodasi bintang 5"),
     *                 @OA\Property(
     *                     property="akomodasi",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="kota", type="object"),
     *                         @OA\Property(property="hotel", type="object"),
     *                         @OA\Property(property="rating_hotel", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="waktu_tunggu",
     *                     type="object",
     *                     @OA\Property(property="min", type="integer", example=1),
     *                     @OA\Property(property="max", type="integer", example=2),
     *                     @OA\Property(property="deskripsi", type="string", example="Waktu tunggu 1-2 tahun")
     *                 ),
     *                 @OA\Property(
     *                     property="fasilitas_tambahan",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="deskripsi_fasilitas", type="string", example="Fasilitas lengkap")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paket haji berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paket haji berhasil dibuat"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal"
     *     )
     * )
     */
    public function createPaketHaji(Request $request)
    {
        // Validasi input - support both format frontend dan format seeder
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:paket_haji_m,nama_paket',
            'biaya_per_pax' => 'required|numeric|min:0',
            'deskripsi_akomodasi' => 'nullable|string',
            'deskripsi_fasilitas' => 'nullable|string',
            'akomodasi' => 'nullable|array',
            'akomodasi.*.kota' => 'nullable', // Bisa string atau array
            'akomodasi.*.hotel' => 'nullable', // Bisa string atau array
            'akomodasi.*.rating_hotel' => 'nullable',
            'akomodasi.*.id_kota' => 'nullable|integer',
            'akomodasi.*.id_hotel' => 'nullable|integer',
            'akomodasi.*.jarak_ke_masjid' => 'nullable|string',
            'akomodasi.*.fasilitas_hotel' => 'nullable|array',
            'waktu_tunggu' => 'nullable|array',
            'waktu_tunggu.min' => 'nullable|integer|min:0',
            'waktu_tunggu.max' => 'nullable|integer|min:0',
            'waktu_tunggu.deskripsi' => 'nullable|string',
            'fasilitas_tambahan' => 'nullable|array',
            'fasilitas_tambahan.*.nama_fasilitas' => 'nullable', // Bisa string atau array
            'fasilitas_tambahan.*.icon_id' => 'nullable|integer',
            'fasilitas_tambahan.*.fasilitas_id' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // Process akomodasi - handle both format frontend dan seeder
            $akomodasiProcessed = [];
            if ($request->has('akomodasi') && is_array($request->akomodasi)) {
                foreach ($request->akomodasi as $item) {
                    // Jika kota adalah array (format frontend), extract data
                    if (is_array($item['kota'])) {
                        $kotaValue = $item['kota']['value'] ?? null;
                        $kotaLabel = $item['kota']['nama_kota'] ?? $item['kota']['label'] ?? '';
                    } else {
                        // Format seeder (kota adalah string)
                        $kotaValue = $item['id_kota'] ?? null;
                        $kotaLabel = $item['kota'] ?? '';
                    }

                    // Jika hotel adalah array (format frontend), extract data
                    if (is_array($item['hotel'])) {
                        $hotelValue = $item['hotel']['value'] ?? null;
                        $hotelLabel = $item['hotel']['nama_hotel'] ?? '';
                        $bintang = $item['hotel']['bintang'] ?? $item['rating_hotel'] ?? 0;
                    } else {
                        // Format seeder (hotel adalah string)
                        $hotelValue = $item['id_hotel'] ?? null;
                        $hotelLabel = $item['hotel'] ?? '';
                        $bintang = $item['rating_hotel'] ?? 0;
                    }

                    $akomodasiProcessed[] = [
                        'id_kota' => $kotaValue,
                        'kota' => $kotaLabel,
                        'id_hotel' => $hotelValue,
                        'hotel' => $hotelLabel,
                        'rating_hotel' => intval($bintang),
                        'jarak_ke_masjid' => $item['jarak_ke_masjid'] ?? '',
                        'fasilitas_hotel' => $item['fasilitas_hotel'] ?? [],
                    ];
                }
            }

            // Process fasilitas_tambahan - handle both format frontend dan seeder
            $fasilitasProcessed = [];
            if ($request->has('fasilitas_tambahan') && is_array($request->fasilitas_tambahan)) {
                $counter = 1;
                foreach ($request->fasilitas_tambahan as $item) {
                    // Jika nama_fasilitas adalah array (format frontend), extract data
                    if (is_array($item['nama_fasilitas'])) {
                        $fasilitasId = $item['nama_fasilitas']['value'] ?? $counter;
                        $fasilitasLabel = $item['nama_fasilitas']['nama_fasilitas'] ?? '';
                    } else {
                        // Format seeder (nama_fasilitas adalah string)
                        $fasilitasId = $item['fasilitas_id'] ?? $counter;
                        $fasilitasLabel = $item['nama_fasilitas'] ?? '';
                    }

                    $fasilitasProcessed[] = [
                        'fasilitas_id' => $fasilitasId,
                        'nama_fasilitas' => $fasilitasLabel,
                        'icon_id' => $item['icon_id'] ?? null,
                    ];
                    $counter++;
                }
            }

            // Buat paket haji baru
            $paketHaji = PaketHaji::create([
                'is_active' => true,
                'nama_paket' => $validated['nama_paket'],
                'biaya_per_pax' => $validated['biaya_per_pax'],
                'akomodasi' => !empty($akomodasiProcessed) ? $akomodasiProcessed : null,
                'deskripsi_akomodasi' => $validated['deskripsi_akomodasi'] ?? null,
                'waktu_tunggu_min' => $request->waktu_tunggu['min'] ?? 0,
                'waktu_tunggu_max' => $request->waktu_tunggu['max'] ?? 0,
                'deskripsi_waktu_tunggu' => $request->waktu_tunggu['deskripsi'] ?? null,
                'fasilitas_tambahan' => !empty($fasilitasProcessed) ? $fasilitasProcessed : null,
                'deskripsi_fasilitas' => $validated['deskripsi_fasilitas'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Paket haji berhasil dibuat',
                'data' => $paketHaji,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat paket haji: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/sistem-admin/paket-haji/update-paket-haji/{id}",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update paket haji",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID paket haji",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nama_paket","biaya_per_pax"},
     *                 @OA\Property(property="nama_paket", type="string", example="Haji Plus"),
     *                 @OA\Property(property="biaya_per_pax", type="number", format="decimal", example=111),
     *                 @OA\Property(property="deskripsi_akomodasi", type="string", example="Akomodasi bintang 5"),
     *                 @OA\Property(
     *                     property="akomodasi",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="kota", type="object"),
     *                         @OA\Property(property="hotel", type="object"),
     *                         @OA\Property(property="rating_hotel", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="waktu_tunggu",
     *                     type="object",
     *                     @OA\Property(property="min", type="integer", example=1),
     *                     @OA\Property(property="max", type="integer", example=2),
     *                     @OA\Property(property="deskripsi", type="string", example="Waktu tunggu 1-2 tahun")
     *                 ),
     *                 @OA\Property(
     *                     property="fasilitas_tambahan",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="deskripsi_fasilitas", type="string", example="Fasilitas lengkap")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paket haji berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paket haji berhasil diupdate"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paket haji tidak ditemukan"
     *     )
     * )
     */
    public function updatePaketHaji(Request $request, $id)
    {
        // Validasi input - support both format frontend dan format seeder
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:paket_haji_m,nama_paket,' . $id,
            'biaya_per_pax' => 'required|numeric|min:0',
            'deskripsi_akomodasi' => 'nullable|string',
            'deskripsi_fasilitas' => 'nullable|string',
            'akomodasi' => 'nullable|array',
            'akomodasi.*.kota' => 'nullable',  // Bisa string atau array
            'akomodasi.*.hotel' => 'nullable', // Bisa string atau array
            'akomodasi.*.rating_hotel' => 'nullable',
            'akomodasi.*.id_kota' => 'nullable|integer',
            'akomodasi.*.id_hotel' => 'nullable|integer',
            'akomodasi.*.jarak_ke_masjid' => 'nullable|string',
            'akomodasi.*.fasilitas_hotel' => 'nullable|array',
            'waktu_tunggu' => 'nullable|array',
            'waktu_tunggu.min' => 'nullable|integer|min:0',
            'waktu_tunggu.max' => 'nullable|integer|min:0',
            'waktu_tunggu.deskripsi' => 'nullable|string',
            'fasilitas_tambahan' => 'nullable|array',
            'fasilitas_tambahan.*.nama_fasilitas' => 'nullable', // Bisa string atau array
            'fasilitas_tambahan.*.icon_id' => 'nullable|integer',
            'fasilitas_tambahan.*.fasilitas_id' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // Cari paket haji berdasarkan ID
            $paketHaji = PaketHaji::findOrFail($id);

            // Process akomodasi - handle both format frontend dan seeder
            $akomodasiProcessed = [];
            if ($request->has('akomodasi') && is_array($request->akomodasi)) {
                foreach ($request->akomodasi as $item) {
                    // Jika kota adalah array (format frontend), extract data
                    if (is_array($item['kota'])) {
                        $kotaValue = $item['kota']['value'] ?? null;
                        $kotaLabel = $item['kota']['nama_kota'] ?? $item['kota']['label'] ?? '';
                    } else {
                        // Format seeder (kota adalah string)
                        $kotaValue = $item['id_kota'] ?? null;
                        $kotaLabel = $item['kota'] ?? '';
                    }

                    // Jika hotel adalah array (format frontend), extract data
                    if (is_array($item['hotel'])) {
                        $hotelValue = $item['hotel']['value'] ?? null;
                        $hotelLabel = $item['hotel']['nama_hotel'] ?? '';
                        $bintang = $item['hotel']['bintang'] ?? $item['rating_hotel'] ?? 0;
                    } else {
                        // Format seeder (hotel adalah string)
                        $hotelValue = $item['id_hotel'] ?? null;
                        $hotelLabel = $item['hotel'] ?? '';
                        $bintang = $item['rating_hotel'] ?? 0;
                    }

                    $akomodasiProcessed[] = [
                        'id_kota' => $kotaValue,
                        'kota' => $kotaLabel,
                        'id_hotel' => $hotelValue,
                        'hotel' => $hotelLabel,
                        'rating_hotel' => intval($bintang),
                        'jarak_ke_masjid' => $item['jarak_ke_masjid'] ?? '',
                        'fasilitas_hotel' => $item['fasilitas_hotel'] ?? [],
                    ];
                }
            }

            // Process fasilitas_tambahan - handle both format frontend dan seeder
            $fasilitasProcessed = [];
            if ($request->has('fasilitas_tambahan') && is_array($request->fasilitas_tambahan)) {
                $counter = 1;
                foreach ($request->fasilitas_tambahan as $item) {
                    // Jika nama_fasilitas adalah array (format frontend), extract data
                    if (is_array($item['nama_fasilitas'])) {
                        $fasilitasId = $item['nama_fasilitas']['value'] ?? $counter;
                        $fasilitasLabel = $item['nama_fasilitas']['nama_fasilitas'] ?? '';
                    } else {
                        // Format seeder (nama_fasilitas adalah string)
                        $fasilitasId = $item['fasilitas_id'] ?? $counter;
                        $fasilitasLabel = $item['nama_fasilitas'] ?? '';
                    }

                    $fasilitasProcessed[] = [
                        'fasilitas_id' => $fasilitasId,
                        'nama_fasilitas' => $fasilitasLabel,
                        'icon_id' => $item['icon_id'] ?? null,
                    ];
                    $counter++;
                }
            }

            // Update paket haji
            $paketHaji->update([
                'nama_paket' => $validated['nama_paket'],
                'biaya_per_pax' => $validated['biaya_per_pax'],
                'akomodasi' => !empty($akomodasiProcessed) ? $akomodasiProcessed : null,
                'deskripsi_akomodasi' => $validated['deskripsi_akomodasi'] ?? null,
                'waktu_tunggu_min' => $request->waktu_tunggu['min'] ?? 0,
                'waktu_tunggu_max' => $request->waktu_tunggu['max'] ?? 0,
                'deskripsi_waktu_tunggu' => $request->waktu_tunggu['deskripsi'] ?? null,
                'fasilitas_tambahan' => !empty($fasilitasProcessed) ? $fasilitasProcessed : null,
                'deskripsi_fasilitas' => $validated['deskripsi_fasilitas'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Paket haji berhasil diupdate',
                'data' => $paketHaji,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate paket haji: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/sistem-admin/paket-haji/delete-paket-haji/{id}",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     summary="Hapus paket haji",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID paket haji",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paket haji berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paket haji berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Paket haji tidak ditemukan"
     *     )
     * )
     */
    public function deletePaketHaji($id)
    {
        try {
            $paketHaji = PaketHaji::findOrFail($id);
            $paketHaji->delete();

            return response()->json([
                'status' => true,
                'message' => 'Paket haji berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus paket haji: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/haji/submission",
     *     summary="Daftar submission haji milik user/mitra yang login",
     *     tags={"PaketHaji"},
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
     *         description="Daftar submission haji berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */
    public function getListSubmissionHaji(Request $request)
    {
        try {
            $userId = auth()->id();
            $status = $request->input('status');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('transaksi_m as tm')
                ->leftjoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftjoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftjoin('paket_haji_m as phm', 'phm.id', '=', 'tm.produk_id')
                ->leftjoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftjoin('provinsi_m as pvm', 'pvm.id', '=', 'tm.provinsi_id')
                ->leftjoin('kota_m as km', 'km.id', '=', 'tm.kota_id')
                ->leftjoin('kecamatan_m as kcm', 'kcm.id', '=', 'tm.kecamatan_id')
                ->leftjoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->select(
                    'tm.id',
                    'gm.gelar',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.provinsi_id',
                    'tm.deskripsi',
                    'tm.alamat_lengkap',
                    'phm.nama_paket',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'tm.tanggal_transaksi',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'stm.kode as status_kode',
                    'tm.snapshot_produk',
                    'pvm.nama_provinsi',
                    'km.nama_kota',
                    'kcm.nama_kecamatan',
                    'tm.jamaah_data',
                    'tm.created_at as tgl_pemesanan',
                )
                ->where('tm.is_active', true)
                ->where('jm.id', 2) // Jenis transaksi haji
                ->where('tm.akun_id', $userId); // Hanya submission milik user yang login
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }

            // Filter berdasarkan status
            if ($status) {
                switch ($status) {
                    case 'belum':
                        $query->whereIn('stm.kode', ['BELUM_DIHUBUNGI', 'DIHUBUNGI']);
                        break;
                    case 'diproses':
                        $query->whereIn('stm.kode', ['MENUNGGU_PEMBAYARAN', 'DIPROSES', 'TERKONFIRMASI']);
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
                $item->jamaah_data = json_decode($item->jamaah_data);
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'List submission haji berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data submission haji: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/haji/submission/{id}",
     *     summary="Detail submission haji berdasarkan ID",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID submission haji",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail submission haji berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Submission haji tidak ditemukan"
     *     )
     * )
     */
    public function getDetailSubmissionHaji($id)
    {
        try {
            $userId = auth()->id();

            $submission = DB::table('transaksi_m as tm')
                ->leftjoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftjoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftjoin('paket_haji_m as phm', 'phm.id', '=', 'tm.produk_id')
                ->leftjoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftjoin('provinsi_m as pvm', 'pvm.id', '=', 'tm.provinsi_id')
                ->leftjoin('kota_m as km', 'km.id', '=', 'tm.kota_id')
                ->leftjoin('kecamatan_m as kcm', 'kcm.id', '=', 'tm.kecamatan_id')
                ->leftjoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->select(
                    'tm.id',
                    'gm.gelar',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.provinsi_id',
                    'tm.kota_id',
                    'tm.kecamatan_id',
                    'tm.deskripsi',
                    'tm.alamat_lengkap',
                    'phm.nama_paket',
                    'phm.id as paket_haji_id',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'tm.tanggal_transaksi',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'stm.kode as status_kode',
                    'tm.snapshot_produk',
                    'pvm.nama_provinsi',
                    'km.nama_kota',
                    'kcm.nama_kecamatan',
                    'tm.jamaah_data',
                    'tm.created_at as tgl_pemesanan',
                    'tm.updated_at',
                )
                ->where('tm.is_active', true)
                ->where('jm.id', 2) // Jenis transaksi haji
                ->where('tm.akun_id', $userId) // Hanya submission milik user yang login
                ->where('tm.id', $id)
                ->first();

            if (!$submission) {
                return response()->json([
                    'status' => false,
                    'message' => 'Submission haji tidak ditemukan',
                ], 404);
            }

            $submission->snapshot_produk = json_decode($submission->snapshot_produk);
            $submission->jamaah_data = json_decode($submission->jamaah_data);

            return response()->json([
                'status' => true,
                'message' => 'Detail submission haji berhasil diambil',
                'data' => $submission,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil detail submission haji: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/haji/submission/{id}/update-status",
     *     summary="Update status submission haji",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID submission haji",
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
     *         description="Status submission haji berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Submission haji tidak ditemukan"
     *     )
     * )
     */
    public function updateStatusSubmissionHaji(Request $request, $id)
    {
        try {
            $userId = auth()->id();

            // Validasi input
            $validated = $request->validate([
                'status_id' => 'required|integer|exists:status_transaksi_m,id',
            ]);

            // Cari submission haji milik user yang login
            $transaksi = Transaksi::where('id', $id)
                ->where('akun_id', $userId)
                ->where('jenis_transaksi_id', 2) // Jenis transaksi haji
                ->where('is_active', true)
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Submission haji tidak ditemukan',
                ], 404);
            }

            // Update status
            $transaksi->status_transaksi_id = $validated['status_id'];
            $transaksi->save();

            return response()->json([
                'status' => true,
                'message' => 'Status submission haji berhasil diperbarui',
                'data' => [
                    'id' => $transaksi->id,
                    'status_transaksi_id' => $transaksi->status_transaksi_id,
                    'updated_at' => $transaksi->updated_at,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update status submission haji: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/mitra/haji/submit-form",
     *     summary="Handle Submission Form Haji untuk Mitra",
     *     tags={"PaketHaji"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap","no_whatsapp","alamat_lengkap","produk_id"},
     *                 @OA\Property(property="gelar_id", type="integer", example=1),
     *                 @OA\Property(property="nama_lengkap", type="string", example="Ahmad Santoso"),
     *                 @OA\Property(property="no_whatsapp", type="string", example="08123456789"),
     *                 @OA\Property(property="alamat_lengkap", type="string", example="Jl. Merdeka No. 123"),
     *                 @OA\Property(property="deskripsi", type="string", example="Catatan tambahan"),
     *                 @OA\Property(property="produk_id", type="integer", example=1),
     *                 @OA\Property(property="provinsi_id", type="integer", example=1),
     *                 @OA\Property(property="kota_id", type="integer", example=10),
     *                 @OA\Property(property="kecamatan_id", type="integer", example=1),
     *                 @OA\Property(property="jamaah_data[0][nama]", type="string", example="Ahmad"),
     *                 @OA\Property(property="jamaah_data[0][nik]", type="string", example="1234567890123456"),
     *                 @OA\Property(property="jamaah_data[0][no_paspor]", type="string", example="A1234567"),
     *                 @OA\Property(property="jamaah_data[0][foto_ktp]", type="string", format="binary"),
     *                 @OA\Property(property="jamaah_data[0][foto_paspor]", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Submission form haji berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Submission form haji berhasil dibuat"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function handleSubmissionFormHaji(Request $request)
    {
        // Endpoint ini sama dengan pesanPaketHaji, tapi khusus untuk mitra
        // Bisa digunakan untuk tracking atau logging khusus mitra
        return $this->pesanPaketHaji($request);
    }
}
