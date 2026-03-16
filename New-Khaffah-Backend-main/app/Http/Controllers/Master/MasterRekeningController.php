<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterRekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterRekeningController extends Controller
{
    /**
     * List rekening (paginated, filter by is_active & search).
     */
    public function index(Request $request)
    {
        $query = MasterRekening::query();

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($w) use ($q) {
                $w->where('bank_name', 'like', $q)
                    ->orWhere('account_number', 'like', $q)
                    ->orWhere('account_holder_name', 'like', $q);
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->orderBy('urutan')->orderBy('bank_name')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Daftar rekening berhasil diambil',
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
        $query = MasterRekening::where('is_active', true)->orderBy('urutan')->orderBy('bank_name');

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($w) use ($q) {
                $w->where('bank_name', 'like', $q)
                    ->orWhere('account_holder_name', 'like', $q);
            });
        }

        $limit = min((int) $request->get('limit', 50), 200);
        $items = $query->limit($limit)->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar rekening berhasil diambil',
            'data' => $items->map(fn ($r) => [
                'id' => $r->id,
                'bank_name' => $r->bank_name,
                'account_number' => $r->account_number,
                'account_holder_name' => $r->account_holder_name,
            ]),
        ]);
    }

    /**
     * Detail satu rekening.
     */
    public function show($id)
    {
        $item = MasterRekening::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Rekening tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail rekening berhasil diambil',
            'data' => $item,
        ]);
    }

    /**
     * Simpan rekening baru.
     */
    public function store(Request $request)
    {
        $rules = [
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:30|regex:/^[0-9]+$/',
            'account_holder_name' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = MasterRekening::create([
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_holder_name' => $request->account_holder_name,
            'keterangan' => $request->keterangan,
            'urutan' => (int) ($request->urutan ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Rekening berhasil ditambahkan',
            'data' => $item,
        ], 201);
    }

    /**
     * Update rekening.
     */
    public function update(Request $request, $id)
    {
        $item = MasterRekening::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Rekening tidak ditemukan'], 404);
        }

        $rules = [
            'bank_name' => 'sometimes|string|max:100',
            'account_number' => 'sometimes|string|max:30|regex:/^[0-9]+$/',
            'account_holder_name' => 'sometimes|string|max:150',
            'keterangan' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('bank_name')) {
            $item->bank_name = $request->bank_name;
        }
        if ($request->has('account_number')) {
            $item->account_number = $request->account_number;
        }
        if ($request->has('account_holder_name')) {
            $item->account_holder_name = $request->account_holder_name;
        }
        if (array_key_exists('keterangan', $request->all())) {
            $item->keterangan = $request->keterangan;
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
            'message' => 'Rekening berhasil diupdate',
            'data' => $item,
        ]);
    }

    /**
     * Hapus rekening.
     */
    public function destroy($id)
    {
        $item = MasterRekening::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Rekening tidak ditemukan'], 404);
        }

        $item->delete();
        return response()->json([
            'status' => true,
            'message' => 'Rekening berhasil dihapus',
        ]);
    }
}
