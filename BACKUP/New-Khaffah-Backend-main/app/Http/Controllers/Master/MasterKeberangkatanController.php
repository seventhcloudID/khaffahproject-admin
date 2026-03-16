<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\BandaraKeberangkatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterKeberangkatanController extends Controller
{
    public function index(Request $request)
    {
        $query = BandaraKeberangkatan::query();

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($qry) use ($q) {
                $qry->where('kode', 'like', $q)->orWhere('nama', 'like', $q);
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->orderBy('urutan')->orderBy('kode')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Daftar bandara keberangkatan berhasil diambil',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /** List untuk dropdown (filter paket, pilih tiket, dll) */
    public function list(Request $request)
    {
        $items = BandaraKeberangkatan::where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return response()->json([
            'status' => true,
            'message' => 'Daftar bandara keberangkatan berhasil diambil',
            'data' => $items->map(fn ($x) => ['value' => $x->id, 'label' => $x->kode . ' - ' . $x->nama, 'kode' => $x->kode, 'nama' => $x->nama]),
        ]);
    }

    public function show($id)
    {
        $item = BandaraKeberangkatan::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Bandara keberangkatan tidak ditemukan'], 404);
        }
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $item]);
    }

    public function store(Request $request)
    {
        $rules = [
            'kode' => 'required|string|max:10',
            'nama' => 'required|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }
        $item = BandaraKeberangkatan::create($validator->validated());
        return response()->json(['status' => true, 'message' => 'Bandara keberangkatan berhasil ditambahkan', 'data' => $item], 201);
    }

    public function update(Request $request, $id)
    {
        $item = BandaraKeberangkatan::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Bandara keberangkatan tidak ditemukan'], 404);
        }
        $rules = [
            'kode' => 'sometimes|string|max:10',
            'nama' => 'sometimes|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }
        $item->update($validator->validated());
        return response()->json(['status' => true, 'message' => 'Bandara keberangkatan berhasil diubah', 'data' => $item->fresh()]);
    }

    public function destroy($id)
    {
        $item = BandaraKeberangkatan::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Bandara keberangkatan tidak ditemukan'], 404);
        }
        $item->update(['is_active' => false]);
        return response()->json(['status' => true, 'message' => 'Bandara keberangkatan berhasil dinonaktifkan']);
    }
}
