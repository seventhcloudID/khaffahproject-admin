<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mitra;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Concerns\StoresDocuments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Status;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class PendaftaranMitraController extends Controller
{

    use StoresDocuments;

    /**
     * @OA\Post(
     *     path="/api/mitra/daftar-mitra",
     *     summary="Pendaftaran mitra baru",
     *     tags={"Mitra"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap", "jenis_kelamin", "tgl_lahir", "email", "no_handphone", "password", "nik", "provinsi_id", "kota_id", "kecamatan_id", "alamat_lengkap", "foto_ktp"},
     *                 @OA\Property(property="nama_lengkap", type="string", maxLength=255, example="Ahmad Maulana"),
     *                 @OA\Property(property="jenis_kelamin", type="string", enum={"laki-laki", "perempuan"}, example="laki-laki"),
     *                 @OA\Property(property="tgl_lahir", type="string", format="date", example="1990-05-15"),
     *                 @OA\Property(property="email", type="string", format="email", example="ahmad.mitra@example.com"),
     *                 @OA\Property(property="no_handphone", type="string", example="081234567890"),
     *                 @OA\Property(property="password", type="string", format="password", minLength=6, example="password123"),
     *                 @OA\Property(property="nik", type="string", maxLength=50, example="3201234567890001"),
     *                 @OA\Property(property="provinsi_id", type="integer", example=1),
     *                 @OA\Property(property="kota_id", type="integer", example=1),
     *                 @OA\Property(property="kecamatan_id", type="integer", example=1),
     *                 @OA\Property(property="alamat_lengkap", type="string", example="Jl. Merdeka No. 123, RT 01/RW 05"),
     *                 @OA\Property(property="nomor_ijin_usaha", type="string", maxLength=100, example="SIUP/123/2024"),
     *                 @OA\Property(property="masa_berlaku_ijin_usaha", type="string", format="date", example="2025-12-31"),
     *                 @OA\Property(property="foto_ktp", type="string", format="binary", description="File KTP (jpg, jpeg, png, pdf, max 5MB)"),
     *                 @OA\Property(property="dokumen_ppiu", type="string", format="binary", description="Dokumen PPIU (pdf, max 5MB)"),
     *                 @OA\Property(property="dokumen_pihk", type="string", format="binary", description="Dokumen PIHK (pdf, max 5MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pendaftaran mitra berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pendaftaran mitra berhasil, menunggu verifikasi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email has already been taken."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Gagal daftar mitra",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Gagal daftar mitra"),
     *             @OA\Property(property="error", type="string", example="Error message details")
     *         )
     *     )
     * )
     */
    public function daftarMitra(Request $request)
    {


        DB::beginTransaction();

        try {
            $request->validate([
                // user
                'nama_lengkap'  => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:laki-laki,perempuan',
                'tgl_lahir'     => 'required|date',
                'email'         => 'required|email|unique:users,email',
                'no_handphone'  => 'required|string|unique:users,no_handphone',
                'password'      => 'required|string|min:6',

                // mitra
                'nik' => 'required|string|max:50',
                'provinsi_id' => 'required|integer',
                'kota_id' => 'required|integer',
                'kecamatan_id' => 'required|integer',
                'alamat_lengkap' => 'required|string',

                'nomor_ijin_usaha' => 'nullable|string|max:100',
                'masa_berlaku_ijin_usaha' => 'nullable|date',

                // dokumen
                'foto_ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'dokumen_ppiu' => 'nullable|file|mimes:pdf|max:5120',
                'dokumen_pihk' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            // 1. user
            $user = User::create([
                'nama_lengkap'  => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tgl_lahir'     => $request->tgl_lahir,
                'email'         => $request->email,
                'no_handphone'  => $request->no_handphone,
                'password'      => Hash::make($request->password),
            ]);

            $user->assignRole('mitra');

            // 2. mitra
            $mitra = Mitra::create([
                'user_id' => $user->id,
                'nama_lengkap' => $request->nama_lengkap,
                'nik' => $request->nik,
                'provinsi_id' => $request->provinsi_id,
                'kota_id' => $request->kota_id,
                'kecamatan_id' => $request->kecamatan_id,
                'alamat_lengkap' => $request->alamat_lengkap,
                'nomor_ijin_usaha' => $request->nomor_ijin_usaha,
                'masa_berlaku_ijin_usaha' => $request->masa_berlaku_ijin_usaha,
                'status_id' => Status::getIdByKode('pending'),
            ]);

            // 3. dokumen
            $ownerTypeId = $this->getOwnerTypeId('mitra');

            $this->storeDocument(
                $request->file('foto_ktp'),
                $ownerTypeId,
                $mitra->id,
                $this->getDocumentTypeId('ktp'),
                $user->id
            );

            if ($request->hasFile('dokumen_ppiu')) {
                $this->storeDocument(
                    $request->file('dokumen_ppiu'),
                    $ownerTypeId,
                    $mitra->id,
                    $this->getDocumentTypeId('ppiu'),
                    $user->id
                );
            }

            if ($request->hasFile('dokumen_pihk')) {
                $this->storeDocument(
                    $request->file('dokumen_pihk'),
                    $ownerTypeId,
                    $mitra->id,
                    $this->getDocumentTypeId('pihk'),
                    $user->id
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pendaftaran mitra berhasil, menunggu verifikasi',
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan sistem',
            ], 500);
        }
    }

    public function prosesMitra(Request $request)
    {
        $request->validate([
            'id_mitra' => 'required|integer|exists:mitra_m,id',
        ]);

        DB::beginTransaction();

        try {
            // Cari mitra
            $mitra = Mitra::findOrFail($request->id_mitra);

            // Validasi: pastikan mitra masih pending
            if (!$mitra->isPending()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mitra tidak dalam status pending',
                ], 400);
            }

            $statusDiproses = Status::getIdByKode('diproses');

            $statusSedangDireview = Status::getIdByKode('sedang_direview');

            // Update status mitra
            $mitra->update([
                'status_id' => $statusDiproses,
            ]);

            // Update semua dokumen mitra
            $ownerTypeId = $this->getOwnerTypeId('mitra');

            $totalDokumen = DB::table('dokumen_m')
                ->where('tipe_owner_id', $ownerTypeId)
                ->where('owner_id', $mitra->id)
                ->where('is_active', true)
                ->whereNull('superseded_at')
                ->update([
                    'status_id' => $statusSedangDireview,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Mitra berhasil diproses',
                'data' => [
                    'mitra_id' => $mitra->id,
                    'nama_lengkap' => $mitra->nama_lengkap,
                    'total_dokumen_updated' => $totalDokumen,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memproses mitra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getListPendaftaranMitra(Request $request)
    {
        try {
            $status = $request->input('status');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('mitra_m as mm')
                ->leftjoin('provinsi_m as pvm', 'pvm.id', '=', 'mm.provinsi_id')
                ->leftjoin('kota_m as km', 'km.id', '=', 'mm.kota_id')
                ->leftjoin('kecamatan_m as kcm', 'kcm.id', '=', 'mm.kecamatan_id')
                ->join('status_m as sm', 'sm.id', '=', 'mm.status_id')
                ->join('users as us', 'us.id', '=', 'mm.user_id')
                ->select(
                    'mm.id as id_mitra',
                    'mm.created_at as tanggal_pendaftaran',
                    'mm.nama_lengkap',
                    'mm.nik',
                    'mm.nomor_ijin_usaha',
                    'mm.masa_berlaku_ijin_usaha',
                    'sm.kode as status_kode',
                    'sm.nama_status',
                    'us.no_handphone',
                    'pvm.nama_provinsi',
                    'km.nama_kota',
                    'kcm.nama_kecamatan',
                )
                ->where('mm.is_active', true)
                ->whereBetween('mm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);

            $status = $request->query('status');

            if ($status) {
                switch ($status) {
                    case 'pending':
                        $query->where('sm.kode', 'pending');
                        break;
                    case 'diproses':
                        $query->where('sm.kode', 'diproses');
                        break;
                    case 'disetujui':
                        $query->where('sm.kode', 'disetujui');
                        break;
                    case 'ditolak':
                        $query->where('sm.kode', 'ditolak');
                        break;
                }
            }

            $data = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'List Pendaftar Mitra berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data Pendaftar Mitra: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar semua akun mitra (untuk halaman admin Data Mitra) beserta level.
     * Query parameter: status (opsional) = disetujui | pending | diproses | ditolak
     */
    public function getListSemuaMitra(Request $request)
    {
        try {
            $query = DB::table('mitra_m as mm')
                ->leftJoin('provinsi_m as pvm', 'pvm.id', '=', 'mm.provinsi_id')
                ->leftJoin('kota_m as km', 'km.id', '=', 'mm.kota_id')
                ->leftJoin('mitra_level_m as ml', 'ml.id', '=', 'mm.mitra_level_id')
                ->join('status_m as sm', 'sm.id', '=', 'mm.status_id')
                ->join('users as us', 'us.id', '=', 'mm.user_id')
                ->select(
                    'mm.id as id_mitra',
                    'mm.user_id',
                    'mm.nama_lengkap',
                    'mm.nik',
                    'mm.created_at as tanggal_daftar',
                    'mm.mitra_level_id',
                    'ml.nama_level as level_nama',
                    'ml.persen_potongan as level_persen_potongan',
                    'sm.kode as status_kode',
                    'sm.nama_status',
                    'us.email',
                    'us.no_handphone',
                    'us.is_active as user_is_active',
                    'pvm.nama_provinsi',
                    'km.nama_kota',
                )
                ->where('mm.is_active', true);

            $status = $request->query('status');
            if ($status && in_array($status, ['pending', 'diproses', 'disetujui', 'ditolak'], true)) {
                $query->where('sm.kode', $status);
            }

            $data = $query->orderBy('mm.created_at', 'desc')->get();

            // Hitung jumlah jemaah per mitra dari master jemaah (jamaah_m), sama seperti yang tampil di /mitra/jamaah
            $userIds = $data->pluck('user_id')->unique()->filter()->values()->all();
            $countsByUser = [];
            if (!empty($userIds)) {
                $counts = DB::table('jamaah_m')
                    ->where('is_active', true)
                    ->whereIn('akun_id', $userIds)
                    ->selectRaw('akun_id, COUNT(*) as total')
                    ->groupBy('akun_id')
                    ->pluck('total', 'akun_id');
                $countsByUser = $counts->all();
            }
            foreach ($data as $row) {
                $row->jumlah_jemaah = (int) ($countsByUser[$row->user_id] ?? 0);
            }

            return response()->json([
                'status' => true,
                'message' => 'Daftar mitra berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data mitra: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getDetailMitra(Request $request)
    {
        try {
            $id_mitra = $request->input('id_mitra');

            $data = DB::table('mitra_m as mm')
                ->leftJoin('provinsi_m as pvm', 'pvm.id', '=', 'mm.provinsi_id')
                ->leftJoin('kota_m as km', 'km.id', '=', 'mm.kota_id')
                ->leftJoin('kecamatan_m as kcm', 'kcm.id', '=', 'mm.kecamatan_id')
                ->leftJoin('mitra_level_m as ml', 'ml.id', '=', 'mm.mitra_level_id')
                ->join('status_m as sm', 'sm.id', '=', 'mm.status_id')
                ->join('users as us', 'us.id', '=', 'mm.user_id')
                ->select(
                    'mm.id as id_mitra',
                    'mm.created_at as tanggal_pendaftaran',
                    'mm.nama_lengkap',
                    'mm.nik',
                    'mm.nomor_ijin_usaha',
                    'mm.masa_berlaku_ijin_usaha',
                    'mm.alamat_lengkap',
                    'mm.mitra_level_id',
                    'ml.nama_level as level_nama',
                    'ml.persen_potongan as level_persen_potongan',
                    'sm.kode as status_kode',
                    'sm.nama_status',
                    'us.no_handphone',
                    'pvm.nama_provinsi',
                    'km.nama_kota',
                    'kcm.nama_kecamatan',
                )
                ->where('mm.id', $id_mitra)
                ->first();

            if (! $data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data mitra tidak ditemukan',
                ], 404);
            }

            $dokumen = DB::table('dokumen_m as dm')
                ->join('tipe_dokumen_m as tdm', 'tdm.id', '=', 'dm.tipe_dokumen_id')
                ->join('status_m as sm', 'sm.id', '=', 'dm.status_id')
                ->join('tipe_owner_m as tom', 'tom.id', '=', 'dm.tipe_owner_id')
                ->select(
                    'dm.id',
                    'tdm.kode as kode_dokumen',
                    'sm.kode as status_kode',
                    'sm.nama_status'
                )
                ->where('tom.kode', 'mitra')
                ->where('dm.owner_id', $id_mitra)
                ->get();

            $dokumenMapped = [];

            foreach ($dokumen as $row) {
                $dokumenMapped[$row->kode_dokumen] = [
                    'id' => $row->id,
                    'status' => $row->status_kode,
                    'label_status' => $row->nama_status,
                ];
            }

            // inject ke response
            $data->dokumen = $dokumenMapped;

            return response()->json([
                'status' => true,
                'message' => 'Detail Pendaftar Mitra berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data Pendaftar Mitra: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getCountPendaftaranMitra(Request $request)
    {
        $tglAwal = $request->input('tglAwal');
        $tglAkhir = $request->input('tglAkhir');

        try {
            $query = DB::table('mitra_m as mm')
                ->join('status_m as sm', 'sm.id', '=', 'mm.status_id')
                ->where('mm.is_active', true);
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('mm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }
            
            $data = $query->selectRaw("
                    COALESCE(SUM(CASE 
                        WHEN sm.kode = 'pending' 
                        THEN 1 ELSE 0 END), 0) AS pending,

                    COALESCE(SUM(CASE 
                        WHEN sm.kode = 'diproses' 
                        THEN 1 ELSE 0 END), 0) AS diproses,

                    COALESCE(SUM(CASE 
                        WHEN sm.kode = 'disetujui' 
                        THEN 1 ELSE 0 END), 0) AS disetujui,

                    COALESCE(SUM(CASE 
                        WHEN sm.kode = 'ditolak' 
                        THEN 1 ELSE 0 END), 0) AS ditolak
                ")
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'Count Pendaftaran Haji berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil count: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function setujuiMitra(Request $request)
    {
        $request->validate([
            'id_mitra' => 'required|integer|exists:mitra_m,id',
            'mitra_level_id' => 'nullable|integer|exists:mitra_level_m,id',
        ]);

        DB::beginTransaction();

        try {
            $mitra = Mitra::findOrFail($request->id_mitra);

            // Boleh disetujui dari status pending ATAU diproses (tanpa harus klik Proses dulu)
            if (! $mitra->isPending() && ! $mitra->isProcessed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mitra sudah dalam status final (disetujui/ditolak). Tidak bisa disetujui lagi.',
                ], 400);
            }

            $statusDisetujui = Status::getIdByKode('disetujui');
            $updateData = ['status_id' => $statusDisetujui];
            if ($request->filled('mitra_level_id')) {
                $updateData['mitra_level_id'] = $request->mitra_level_id;
            }

            $mitra->update($updateData);

            // Assign role "mitra" ke user agar bisa akses fitur mitra & dapat diskon
            $user = $mitra->user;
            if ($user) {
                $roleMitra = Role::findByName('mitra', 'api');
                if ($roleMitra && ! $user->hasRole('mitra', 'api')) {
                    $user->assignRole($roleMitra);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Mitra berhasil disetujui',
                'data' => [
                    'mitra_id' => $mitra->id,
                    'nama_lengkap' => $mitra->nama_lengkap,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal setujui mitra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update level mitra (untuk atur potongan harga).
     */
    public function updateLevelMitra(Request $request)
    {
        $request->validate([
            'id_mitra' => 'required|integer|exists:mitra_m,id',
            'mitra_level_id' => 'nullable|integer|exists:mitra_level_m,id',
        ]);

        $mitra = Mitra::findOrFail($request->id_mitra);
        $mitra->mitra_level_id = $request->mitra_level_id ?: null;
        $mitra->save();

        return response()->json([
            'status' => true,
            'message' => 'Level mitra berhasil diupdate',
            'data' => [
                'mitra_id' => $mitra->id,
                'mitra_level_id' => $mitra->mitra_level_id,
            ],
        ]);
    }

    public function tolakMitra(Request $request)
    {
        $request->validate([
            'id_mitra' => 'required|integer|exists:mitra_m,id',
            'alasan_penolakan' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Cari mitra
            $mitra = Mitra::findOrFail($request->id_mitra);

            if (! $mitra->isPending() && ! $mitra->isProcessed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mitra sudah dalam status final. Tidak bisa ditolak.',
                ], 400);
            }

            $statusDitolak = Status::getIdByKode('ditolak');

            // Update status mitra
            $mitra->update([
                'status_id' => $statusDitolak,
                'alasan_penolakan' => $request->alasan_penolakan,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Mitra berhasil ditolak',
                'data' => [
                    'mitra_id' => $mitra->id,
                    'nama_lengkap' => $mitra->nama_lengkap,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal tolak mitra',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
