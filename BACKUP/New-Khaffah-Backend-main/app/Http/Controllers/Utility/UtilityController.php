<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Maskapai;
use App\Models\TujuanTambahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/utility/gelar",
     *     summary="Daftar gelar",
     *     tags={"Utility"},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar gelar berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getGelar()
    {
        try {
            $items = DB::table('gelar_m')
                ->select('id', 'gelar as nama_gelar')
                ->orderBy('gelar')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Daftar gelar berhasil diambil',
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/utility/provinsi",
     *     summary="Daftar provinsi",
     *     tags={"Utility"},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar provinsi berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    /**
     * Hanya mengembalikan provinsi Indonesia. Provinsi luar (Arab Saudi, UEA, Turki, Mesir) di-exclude
     * agar form alamat jemaah hanya menampilkan provinsi sesuai data Indonesia / locations.csv.
     * Mendukung query ?search= untuk filter nama.
     */
    public function getProvinsi(Request $request)
    {
        try {
            $provinsiLuar = [
                'Provinsi Arab Saudi',
                'Provinsi Uni Emirat Arab',
                'Provinsi Turki',
                'Provinsi Mesir',
            ];

            $query = DB::table('provinsi_m')
                ->select('id', 'nama_provinsi as name')
                ->whereNotIn('nama_provinsi', $provinsiLuar)
                ->orderBy('nama_provinsi');

            $search = $request->query('search');
            if ($search && trim($search) !== '') {
                $query->where('nama_provinsi', 'like', '%' . trim($search) . '%');
            }

            $items = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Daftar provinsi berhasil diambil',
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/utility/kota",
     *     summary="Daftar kota dalam provinsi",
     *     tags={"Utility"},
     *     @OA\Parameter(
     *         name="provinsi_id",
     *         in="query",
     *         description="ID provinsi",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kota berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request - missing provinsi_id"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getKota(Request $request)
    {
        $provinsiId = $request->query('provinsi_id');
        if (! $provinsiId) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter provinsi_id wajib diisi',
            ], 400);
        }

        try {
            $items = DB::table('kota_m')
                ->select('id', 'nama_kota as name')
                ->where('provinsi_id', $provinsiId)
                ->orderBy('nama_kota')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Daftar kota berhasil diambil',
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/utility/kecamatan",
     *     summary="Daftar kecamatan dalam kota",
     *     tags={"Utility"},
     *     @OA\Parameter(
     *         name="kota_id",
     *         in="query",
     *         description="ID kota",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar kecamatan berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request - missing kota_id"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getKecamatan(Request $request)
    {
        $kotaId = $request->query('kota_id');
        if (! $kotaId) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter kota_id wajib diisi',
            ], 400);
        }

        try {
            $items = DB::table('kecamatan_m')
                ->select('id', 'nama_kecamatan as name')
                ->where('kota_id', $kotaId)
                ->orderBy('nama_kecamatan')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Daftar kecamatan berhasil diambil',
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/utility/keberangkatan",
     *     summary="Daftar keberangkatan dalam paket umrah",
     *     tags={"Utility"},
     *     @OA\Parameter(
     *         name="paket_umrah_id",
     *         in="query",
     *         description="ID paket umrah",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar keberangkatan berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object", @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string")))
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request - missing kota_id"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getKeberangkatan(Request $request)
    {
        $paket_umrah_id = $request->query('paket_umrah_id');
        if (! $paket_umrah_id) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter paket_umrah_id wajib diisi',
            ], 400);
        }

        try {
            $items = DB::table('paket_umrah_keberangkatan_t')
                ->select('id', 'tanggal_berangkat', 'jam_berangkat', 'tanggal_pulang', 'jam_pulang')
                ->where('paket_umrah_id', $paket_umrah_id)
                ->orderBy('tanggal_berangkat')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Daftar keberangkatan berhasil diambil',
                'data' => $items,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar bandara Indonesia untuk form product-request (Umrah Custom / LA Custom).
     * GET /api/utility/bandara
     */
    public function getBandara()
    {
        try {
            $bandara = [
                ['value' => 'CGK', 'label' => 'Soekarno-Hatta (CGK) - Jakarta'],
                ['value' => 'SUB', 'label' => 'Juanda (SUB) - Surabaya'],
                ['value' => 'DPS', 'label' => 'Ngurah Rai (DPS) - Bali'],
                ['value' => 'UPG', 'label' => 'Hasanuddin (UPG) - Makassar'],
                ['value' => 'KNO', 'label' => 'Kualanamu (KNO) - Medan'],
                ['value' => 'MDC', 'label' => 'Sam Ratulangi (MDC) - Manado'],
                ['value' => 'PLM', 'label' => 'Sultan Mahmud Badaruddin II (PLM) - Palembang'],
                ['value' => 'BTH', 'label' => 'Hang Nadim (BTH) - Batam'],
                ['value' => 'SRG', 'label' => 'Achmad Yani (SRG) - Semarang'],
                ['value' => 'JOG', 'label' => 'Adisutjipto (JOG) - Yogyakarta'],
                ['value' => 'BTJ', 'label' => 'Sultan Iskandar Muda (BTJ) - Banda Aceh'],
                ['value' => 'PDG', 'label' => 'Minangkabau (PDG) - Padang'],
            ];

            return response()->json([
                'status' => true,
                'message' => 'Daftar bandara berhasil diambil',
                'data' => $bandara,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar hotel aktif (Mekkah & Madinah) untuk form product-request / modal Cari Hotel.
     * GET /api/utility/hotels
     * Mengembalikan: id, nama_hotel, kota, bintang, jarak_ke_masjid, url_foto (foto pertama), harga_mulai (min harga_per_malam dari kamar).
     */
    public function getHotels()
    {
        try {
            $baseUrl = rtrim(config('app.url'), '/');
            $items = Hotel::with(['kota', 'foto' => fn ($q) => $q->where('is_active', true)->orderBy('urutan')->limit(1), 'kamar'])
                ->where('is_active', true)
                ->orderBy('nama_hotel')
                ->get();

            $data = $items->map(function ($h) use ($baseUrl) {
                $firstFoto = $h->foto->first();
                $urlFoto = null;
                if ($firstFoto && ! empty($firstFoto->url_foto)) {
                    $path = trim(str_replace('\\', '/', $firstFoto->url_foto));
                    $urlFoto = $baseUrl . '/' . (str_starts_with($path, 'storage/') ? $path : 'storage/' . $path);
                }
                // Harga terendah dari semua kamar (yang punya harga_per_malam), agar "Mulai Dari" selalu keluar jika data ada
                $hargaMulai = $h->kamar->filter(fn ($k) => $k->harga_per_malam !== null && $k->harga_per_malam > 0)->min('harga_per_malam');
                return [
                    'id' => $h->id,
                    'nama_hotel' => $h->nama_hotel,
                    'kota_id' => $h->kota_id,
                    'kota' => $h->kota ? $h->kota->nama_kota : null,
                    'bintang' => $h->bintang,
                    'jarak_ke_masjid' => $h->jarak_ke_masjid,
                    'url_foto' => $urlFoto,
                    'harga_mulai' => $hargaMulai !== null ? (float) $hargaMulai : null,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Daftar hotel berhasil diambil',
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
     * Daftar tujuan tambahan (negara/kota liburan) untuk form Umrah Plus Liburan.
     * GET /api/utility/tujuan-tambahan
     */
    public function getTujuanTambahan()
    {
        try {
            $items = TujuanTambahan::where('is_active', true)
                ->orderBy('urutan')
                ->orderBy('nama')
                ->get(['id', 'nama']);

            $data = $items->map(function ($t) {
                return [
                    'value' => $t->nama,
                    'label' => $t->nama,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'Daftar tujuan tambahan berhasil diambil',
                'data'    => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar maskapai aktif untuk form product-request.
     * GET /api/utility/maskapai
     */
    public function getMaskapai()
    {
        try {
            $items = Maskapai::where('is_active', true)
                ->orderBy('nama_maskapai')
                ->get(['id', 'nama_maskapai', 'kode_iata', 'negara_asal']);

            $data = $items->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nama_maskapai' => $m->nama_maskapai,
                    'kode_iata' => $m->kode_iata,
                    'negara_asal' => $m->negara_asal,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Daftar maskapai berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function dropdown(Request $r, $table)
    {
        try {
            /**
             * Khusus master status transaksi, kembalikan hanya 4 status utama:
             * - Belum di Proses
             * - Di Proses
             * - Selesai
             * - Batal
             *
             * Frontend tetap menerima bentuk { value, label, ... } seperti sebelumnya,
             * tetapi opsi yang muncul sudah diseragamkan.
             */
            if ($table === 'status_transaksi_m') {
                $rows = DB::table('status_transaksi_m')
                    ->where('is_active', true)
                    ->whereIn('kode', [
                        'BELUM_DIHUBUNGI', // dipakai sebagai "Belum di Proses"
                        'DIPROSES',        // dipakai sebagai "Di Proses"
                        'SELESAI',         // dipakai sebagai "Selesai"
                        'DIBATALKAN',      // dipakai sebagai "Batal"
                    ])
                    ->orderByRaw("
                        CASE kode
                            WHEN 'BELUM_DIHUBUNGI' THEN 1
                            WHEN 'DIPROSES' THEN 2
                            WHEN 'SELESAI' THEN 3
                            WHEN 'DIBATALKAN' THEN 4
                            ELSE 99
                        END
                    ")
                    ->get();

                $data = $rows->map(function ($row) {
                    $label = match ($row->kode) {
                        'BELUM_DIHUBUNGI' => 'Belum di Proses',
                        'DIPROSES'        => 'Di Proses',
                        'SELESAI'         => 'Selesai',
                        'DIBATALKAN'      => 'Batal',
                        default           => $row->nama_status,
                    };

                    return [
                        'value'       => $row->id,
                        'label'       => $label,
                        'id'          => $row->id,
                        'kode'        => $row->kode,
                        'nama_status' => $row->nama_status,
                    ];
                });

                return response()->json([
                    'status'  => true,
                    'message' => 'Daftar status transaksi utama berhasil diambil',
                    'data'    => $data,
                ]);
            }

            $query = DB::table($table);

            if ($r->has('select') && !empty($r->select)) {
                $fields = array_map('trim', explode(',', $r->select));

                if (count($fields) >= 2) {
                    $valueField = $fields[0];          // id
                    $mainLabelField = $fields[1];      // nama_hotel
                    $extraFields = array_slice($fields, 2); // sisanya

                    // Label utama (tanpa nama field)
                    $labelParts = [$mainLabelField];

                    // Field tambahan → pakai nama field
                    foreach ($extraFields as $field) {
                        $labelParts[] = "'{$field}: ' || {$field}";
                    }

                    $labelExpression = implode(" || ' | ' || ", $labelParts);

                    // Select value & label
                    $query->selectRaw("{$valueField} as value, {$labelExpression} as label");

                    // Tambahkan semua field (kecuali id)
                    foreach (array_slice($fields, 1) as $field) {
                        $query->addSelect($field);
                    }
                } else {
                    $field = $fields[0];
                    $query->selectRaw("{$field} as value, {$field} as label");
                }
            }

            // Handle SEARCH/FILTER
            if ($r->has('param_search') && !empty($r->param_search) && $r->has('query')) {
                $searchFields = explode(',', $r->param_search);
                $searchValue = $r->input('query');

                $query->where(function ($q) use ($searchFields, $searchValue) {
                    foreach ($searchFields as $field) {
                        $q->orWhere(trim($field), 'LIKE', '%' . $searchValue . '%');
                    }
                });
            }

            // Handle WHERE CONDITIONS (untuk filter tambahan)
            if ($r->has('where') && !empty($r->where)) {
                $whereConditions = json_decode($r->where, true);
                if (is_array($whereConditions)) {
                    foreach ($whereConditions as $field => $value) {
                        if (is_array($value)) {
                            $query->whereIn($field, $value);
                        } else {
                            $query->where($field, $value);
                        }
                    }
                }
            }

            if ($r->has('include_disabled') && $r->include_disabled == 'true') {
            } else {
                $query->where('is_active', true);
            }

            // Handle ORDER BY
            if ($r->has('orderby') && !empty($r->orderby)) {
                $orderFields = explode(',', $r->orderby);
                foreach ($orderFields as $orderField) {
                    $parts = explode(':', $orderField);
                    $field = trim($parts[0]);
                    $direction = isset($parts[1]) ? strtoupper(trim($parts[1])) : 'ASC';
                    $query->orderBy($field, $direction);
                }
            }

            // Handle LIMIT
            $limit = $r->has('limit') && !empty($r->limit) ? (int)$r->limit : 10;
            $query->limit($limit);

            // Handle OFFSET (untuk pagination)
            if ($r->has('offset') && !empty($r->offset)) {
                $query->offset((int)$r->offset);
            }

            $data = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Daftar berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
