<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KelasPenerbangan;
use App\Models\Maskapai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterMaskapaiController extends Controller
{
    /**
     * List maskapai (paginated, filter by is_active & search).
     */
    public function index(Request $request)
    {
        $query = Maskapai::query();

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($qry) use ($q) {
                $qry->where('nama_maskapai', 'like', $q)
                    ->orWhere('kode_iata', 'like', $q)
                    ->orWhere('negara_asal', 'like', $q);
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->orderBy('nama_maskapai')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Daftar maskapai berhasil diambil',
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
        $query = Maskapai::where('is_active', true)->orderBy('nama_maskapai');

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where('nama_maskapai', 'like', $q)->orWhere('kode_iata', 'like', $q);
        }

        $limit = min((int) $request->get('limit', 50), 200);
        $items = $query->limit($limit)->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar maskapai berhasil diambil',
            'data' => $items,
        ]);
    }

    /**
     * Daftar kelas penerbangan untuk dropdown.
     */
    public function kelasPenerbanganOptions()
    {
        $items = KelasPenerbangan::where('is_active', true)->orderBy('kelas_penerbangan')->get(['id', 'kelas_penerbangan']);

        return response()->json([
            'status' => true,
            'message' => 'Daftar kelas penerbangan berhasil diambil',
            'data' => $items->map(fn ($k) => ['value' => $k->id, 'label' => $k->kelas_penerbangan]),
        ]);
    }

    /**
     * Detail satu maskapai (dengan kelas penerbangan).
     */
    public function show($id)
    {
        $item = Maskapai::with('kelasPenerbangan')->find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Maskapai tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail maskapai berhasil diambil',
            'data' => $item,
        ]);
    }

    /**
     * Simpan maskapai baru.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_maskapai' => 'required|string|max:255',
            'kode_iata' => 'nullable|string|max:10',
            'negara_asal' => 'nullable|string|max:100',
            'logo_url' => 'nullable|string',
            'jam_keberangkatan' => 'nullable|date_format:H:i',
            'jam_sampai' => 'nullable|date_format:H:i',
            'kelas_penerbangan_id' => 'nullable|exists:kelas_penerbangan_m,id',
            'harga_tiket_per_orang' => 'nullable|numeric|min:0',
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

        $item = Maskapai::create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil ditambahkan',
            'data' => $item,
        ], 201);
    }

    /**
     * Update maskapai.
     */
    public function update(Request $request, $id)
    {
        $item = Maskapai::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Maskapai tidak ditemukan'], 404);
        }

        $rules = [
            'nama_maskapai' => 'sometimes|string|max:255',
            'kode_iata' => 'nullable|string|max:10',
            'negara_asal' => 'nullable|string|max:100',
            'logo_url' => 'nullable|string',
            'jam_keberangkatan' => 'nullable|date_format:H:i',
            'jam_sampai' => 'nullable|date_format:H:i',
            'kelas_penerbangan_id' => 'nullable|exists:kelas_penerbangan_m,id',
            'harga_tiket_per_orang' => 'nullable|numeric|min:0',
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

        $item->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil diubah',
            'data' => $item->fresh(),
        ]);
    }

    /**
     * Nonaktifkan maskapai.
     */
    public function destroy($id)
    {
        $item = Maskapai::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Maskapai tidak ditemukan'], 404);
        }

        $item->update(['is_active' => false]);

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil dinonaktifkan',
        ]);
    }
}
