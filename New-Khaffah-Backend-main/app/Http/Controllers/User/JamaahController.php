<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jamaah;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Concerns\StoresDocuments;
use Illuminate\Support\Facades\DB;
use App\Models\TipeDokumen;

class JamaahController extends Controller
{

    use StoresDocuments;

    /**
     * @OA\Get(
     *     path="/api/jamaah",
     *     summary="Daftar jamaah (user sendiri atau semua untuk superadmin)",
     *     tags={"Jamaah"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar jamaah berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasAnyRole(['superadmin'])) {
            $items = Jamaah::where('is_active', true)->get();
        } else {
            $items = Jamaah::where('akun_id', $user->id)->where('is_active', true)->get();
        }

        $ownerTypeId = $this->getOwnerTypeId('jamaah');
        $data = $items->map(function (Jamaah $j) use ($ownerTypeId) {
            $row = $j->toArray();
            $row['dokumen_ktp_id'] = null;
            $row['dokumen_paspor_id'] = null;
            $docs = $this->getDocumentsByOwner($ownerTypeId, (int) $j->id);
            foreach ($docs as $d) {
                if (($d->tipe_dokumen ?? '') === 'ktp') {
                    $row['dokumen_ktp_id'] = $d->id;
                }
                if (($d->tipe_dokumen ?? '') === 'paspor') {
                    $row['dokumen_paspor_id'] = $d->id;
                }
            }
            return $row;
        })->values()->all();

        return response()->json([
            'status' => true,
            'message' => 'Daftar jamaah',
            'data' => $data,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/jamaah",
     *     summary="Buat jamaah baru",
     *     tags={"Jamaah"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap"},
     *                 @OA\Property(property="nama_lengkap", type="string", example="Ahmad Santoso"),
     *                 @OA\Property(property="nomor_identitas", type="string", example="1234567890123456"),
     *                 @OA\Property(property="foto_identitas", type="string", format="binary"),
     *                 @OA\Property(property="nomor_passpor", type="string", example="A1234567"),
     *                 @OA\Property(property="foto_passpor", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Jamaah berhasil dibuat"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nomor_identitas' => 'nullable|string|max:50',
            'foto_identitas' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'nomor_passpor' => 'nullable|string|max:50',
            'foto_passpor' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        DB::beginTransaction();

        try {
            // 1️⃣ Simpan jamaah (TANPA file)
            $jamaah = Jamaah::create([
                'akun_id' => $user->id,
                'nama_lengkap' => $request->nama_lengkap,
                'nomor_identitas' => $request->nomor_identitas,
                'nomor_passpor' => $request->nomor_passpor,
                'is_active' => true,
            ]);

            // 2️⃣ Simpan dokumen
            $ownerTypeId = $this->getOwnerTypeId('jamaah');

            if ($request->hasFile('foto_identitas')) {
                $this->storeDocument(
                    $request->file('foto_identitas'),
                    $ownerTypeId,
                    $jamaah->id,
                    $this->getDocumentTypeId('ktp'),
                    $user->id
                );
            }

            if ($request->hasFile('foto_passpor')) {
                $this->storeDocument(
                    $request->file('foto_passpor'),
                    $ownerTypeId,
                    $jamaah->id,
                    $this->getDocumentTypeId('paspor'),
                    $user->id
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Jamaah berhasil dibuat',
                'data' => $jamaah
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan jamaah'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/jamaah/{jamaah}",
     *     summary="Ambil detail jamaah berdasarkan ID",
     *     tags={"Jamaah"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="jamaah", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail jamaah berhasil diambil"),
     *     @OA\Response(response=403, description="Akses ditolak")
     * )
     */
    public function show(Request $request, Jamaah $jamaah)
    {
        $user = $request->user();

        // Hanya pemilik jamaah atau superadmin yang boleh akses (mitra hanya akses jamaah milik sendiri)
        if ((int) $jamaah->akun_id !== (int) $user->id && ! $user->hasAnyRole(['superadmin'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak'], 403);
        }

        $data = $jamaah->toArray();
        $ownerTypeId = $this->getOwnerTypeId('jamaah');
        $docs = $this->getDocumentsByOwner($ownerTypeId, (int) $jamaah->id);
        $data['dokumen_ktp_id'] = null;
        $data['dokumen_paspor_id'] = null;
        foreach ($docs as $d) {
            if (($d->tipe_dokumen ?? '') === 'ktp') {
                $data['dokumen_ktp_id'] = $d->id;
            }
            if (($d->tipe_dokumen ?? '') === 'paspor') {
                $data['dokumen_paspor_id'] = $d->id;
            }
        }

        return response()->json(['status' => true, 'message' => 'Detail jamaah', 'data' => $data]);
    }

    /**
     * @OA\Put(
     *     path="/api/jamaah/{jamaah}",
     *     summary="Perbarui data jamaah",
     *     tags={"Jamaah"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="jamaah", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nama_lengkap", type="string"),
     *                 @OA\Property(property="nomor_identitas", type="string"),
     *                 @OA\Property(property="foto_identitas", type="string", format="binary"),
     *                 @OA\Property(property="nomor_passpor", type="string"),
     *                 @OA\Property(property="foto_passpor", type="string", format="binary"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Jamaah berhasil diperbarui"),
     *     @OA\Response(response=403, description="Akses ditolak"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function update(Request $request, Jamaah $jamaah)
    {
        $user = $request->user();

        // Hanya pemilik jamaah atau superadmin yang boleh update (mitra hanya jamaah milik sendiri)
        if ((int) $jamaah->akun_id !== (int) $user->id && ! $user->hasAnyRole(['superadmin'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak'], 403);
        }

        $rules = [
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'nomor_identitas' => 'sometimes|nullable|string|max:50',
            'foto_identitas' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'nomor_passpor' => 'sometimes|nullable|string|max:50',
            'foto_passpor' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'is_active' => 'sometimes|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // update data jamaah
            $jamaah->update($request->only([
                'nama_lengkap',
                'nomor_identitas',
                'nomor_passpor',
                'is_active'
            ]));

            $ownerTypeId = $this->getOwnerTypeId('jamaah');

            if ($request->hasFile('foto_identitas')) {
                $this->replaceDocument(
                    $request->file('foto_identitas'),
                    $ownerTypeId,
                    $jamaah->id,
                    $this->getDocumentTypeId('ktp'),
                    $user->id
                );
            }

            if ($request->hasFile('foto_passpor')) {
                $this->replaceDocument(
                    $request->file('foto_passpor'),
                    $ownerTypeId,
                    $jamaah->id,
                    $this->getDocumentTypeId('paspor'),
                    $user->id
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Jamaah berhasil diperbarui'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan jamaah'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/jamaah/{jamaah}",
     *     summary="Hapus (soft delete) jamaah",
     *     tags={"Jamaah"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="jamaah", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Jamaah berhasil dihapus"),
     *     @OA\Response(response=403, description="Akses ditolak")
     * )
     */
    public function destroy(Request $request, Jamaah $jamaah)
    {
        $user = $request->user();

        // Hanya pemilik jamaah atau superadmin yang boleh hapus (mitra hanya jamaah milik sendiri)
        if ((int) $jamaah->akun_id !== (int) $user->id && ! $user->hasAnyRole(['superadmin'])) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak'], 403);
        }

        $jamaah->update(['is_active' => false]);

        return response()->json(['status' => true, 'message' => 'Jamaah berhasil dihapus']);
    }
}
