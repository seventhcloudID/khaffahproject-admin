<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MitraLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterLevelMitraController extends Controller
{
    /**
     * List level mitra (paginated, filter by is_active & search).
     */
    public function index(Request $request)
    {
        $query = MitraLevel::query();

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where('nama_level', 'like', $q);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->orderBy('urutan')->orderBy('nama_level')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Daftar level mitra berhasil diambil',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * List sederhana untuk dropdown (tanpa pagination).
     */
    public function list(Request $request)
    {
        $query = MitraLevel::where('is_active', true)->orderBy('urutan')->orderBy('nama_level');

        if ($request->filled('search')) {
            $query->where('nama_level', 'like', '%' . $request->search . '%');
        }

        $limit = min((int) $request->get('limit', 50), 200);
        $items = $query->limit($limit)->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar level mitra berhasil diambil',
            'data' => $items->map(fn ($l) => ['id' => $l->id, 'nama_level' => $l->nama_level, 'persen_potongan' => $l->persen_potongan]),
        ]);
    }

    /**
     * Detail satu level mitra.
     */
    public function show($id)
    {
        $item = MitraLevel::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Level mitra tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail level mitra berhasil diambil',
            'data' => $item,
        ]);
    }

    /**
     * Simpan level mitra baru.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_level' => 'required|string|max:100',
            'persen_potongan' => 'required|numeric|min:0|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = MitraLevel::create([
            'nama_level' => $request->nama_level,
            'persen_potongan' => (float) $request->persen_potongan,
            'urutan' => (int) ($request->urutan ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Level mitra berhasil ditambahkan',
            'data' => $item,
        ], 201);
    }

    /**
     * Update level mitra.
     */
    public function update(Request $request, $id)
    {
        $item = MitraLevel::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Level mitra tidak ditemukan'], 404);
        }

        $rules = [
            'nama_level' => 'sometimes|string|max:100',
            'persen_potongan' => 'sometimes|numeric|min:0|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('nama_level')) {
            $item->nama_level = $request->nama_level;
        }
        if ($request->has('persen_potongan')) {
            $item->persen_potongan = (float) $request->persen_potongan;
        }
        if (array_key_exists('urutan', $request->all())) {
            $item->urutan = (int) $request->urutan;
        }
        if ($request->has('is_active')) {
            $item->is_active = $request->boolean('is_active');
        }
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Level mitra berhasil diupdate',
            'data' => $item,
        ]);
    }

    /**
     * Hapus level mitra.
     */
    public function destroy($id)
    {
        $item = MitraLevel::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Level mitra tidak ditemukan'], 404);
        }

        $item->delete();
        return response()->json([
            'status' => true,
            'message' => 'Level mitra berhasil dihapus',
        ]);
    }
}
