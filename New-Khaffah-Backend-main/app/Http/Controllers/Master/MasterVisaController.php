<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterVisa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MasterVisaController extends Controller
{
    /**
     * List visa (paginated, filter by is_active & search).
     */
    public function index(Request $request)
    {
        $query = MasterVisa::query();

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($qry) use ($q) {
                $qry->where('nama_layanan', 'like', $q)
                    ->orWhere('slug', 'like', $q);
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->orderBy('urutan')->orderBy('nama_layanan')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Daftar layanan visa berhasil diambil',
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
     * List sederhana untuk dropdown / halaman mitra (tanpa pagination).
     */
    public function list(Request $request)
    {
        $query = MasterVisa::where('is_active', true)->orderBy('urutan')->orderBy('nama_layanan');

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($qry) use ($q) {
                $qry->where('nama_layanan', 'like', $q)->orWhere('slug', 'like', $q);
            });
        }

        $limit = min((int) $request->get('limit', 50), 200);
        $items = $query->limit($limit)->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar layanan visa berhasil diambil',
            'data' => $items->map(fn ($v) => [
                'id' => $v->id,
                'nama_layanan' => $v->nama_layanan,
                'slug' => $v->slug,
                'deskripsi' => $v->deskripsi,
                'harga' => (int) $v->harga,
                'urutan' => $v->urutan,
            ]),
        ]);
    }

    /**
     * Public API: list layanan visa aktif untuk halaman /mitra/komponen/visa (no auth).
     */
    public function listPublic()
    {
        $items = MasterVisa::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama_layanan')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $items->map(fn ($v) => [
                'id' => $v->id,
                'nama_layanan' => $v->nama_layanan,
                'slug' => $v->slug,
                'deskripsi' => $v->deskripsi,
                'harga' => (int) $v->harga,
                'urutan' => $v->urutan,
            ]),
        ], 200);
    }

    /**
     * Detail satu layanan visa.
     */
    public function show($id)
    {
        $item = MasterVisa::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Layanan visa tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail layanan visa berhasil diambil',
            'data' => $item,
        ]);
    }

    /**
     * Simpan layanan visa baru.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_layanan' => 'required|string|max:150',
            'slug' => 'nullable|string|max:80|unique:master_visa_m,slug',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
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

        $slug = $request->filled('slug')
            ? $request->slug
            : Str::slug($request->nama_layanan);

        if (MasterVisa::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $item = MasterVisa::create([
            'nama_layanan' => $request->nama_layanan,
            'slug' => $slug,
            'deskripsi' => $request->deskripsi,
            'harga' => (int) round((float) $request->harga),
            'urutan' => (int) ($request->urutan ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Layanan visa berhasil ditambahkan',
            'data' => $item,
        ], 201);
    }

    /**
     * Update layanan visa.
     */
    public function update(Request $request, $id)
    {
        $item = MasterVisa::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Layanan visa tidak ditemukan'], 404);
        }

        $rules = [
            'nama_layanan' => 'sometimes|string|max:150',
            'slug' => 'sometimes|string|max:80|unique:master_visa_m,slug,' . $id,
            'deskripsi' => 'nullable|string',
            'harga' => 'sometimes|numeric|min:0',
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

        if ($request->has('nama_layanan')) {
            $item->nama_layanan = $request->nama_layanan;
        }
        if ($request->has('slug')) {
            $item->slug = $request->slug;
        }
        if (array_key_exists('deskripsi', $request->all())) {
            $item->deskripsi = $request->deskripsi;
        }
        if ($request->has('harga')) {
            $item->harga = (int) round((float) $request->harga);
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
            'message' => 'Layanan visa berhasil diupdate',
            'data' => $item,
        ]);
    }

    /**
     * Hapus layanan visa.
     */
    public function destroy($id)
    {
        $item = MasterVisa::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Layanan visa tidak ditemukan'], 404);
        }

        $item->delete();
        return response()->json([
            'status' => true,
            'message' => 'Layanan visa berhasil dihapus',
        ]);
    }
}
