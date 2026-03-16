<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use App\Models\TujuanTambahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TujuanTambahanController extends Controller
{
    const LIST_CACHE_TTL = 60;
    const LIST_CACHE_KEY = 'tujuan_tambahan_admin_list';

    /**
     * Admin: list semua tujuan tambahan (untuk datatable).
     */
    public function getList(Request $request)
    {
        try {
            $data = Cache::remember(self::LIST_CACHE_KEY, self::LIST_CACHE_TTL, function () {
                return TujuanTambahan::orderBy('urutan')->orderBy('nama')->get();
            });

            return response()->json([
                'status'  => true,
                'message' => 'OK',
                'data'    => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data: ' . $th->getMessage(),
            ], 500);
        }
    }

    private function clearCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
    }

    /**
     * Admin: create tujuan tambahan.
     */
    public function create(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'urutan'    => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $maxUrutan = TujuanTambahan::max('urutan') ?? 0;
            $item = TujuanTambahan::create([
                'nama'      => $request->nama,
                'urutan'    => $request->urutan ?? $maxUrutan + 1,
                'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            ]);
            $this->clearCache();

            return response()->json([
                'status'  => true,
                'message' => 'Tujuan tambahan berhasil ditambah',
                'data'    => $item,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menambah: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: update tujuan tambahan.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'urutan'    => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $item = TujuanTambahan::findOrFail($id);
            $item->update([
                'nama'      => $request->nama,
                'urutan'    => $request->urutan ?? $item->urutan,
                'is_active' => $request->has('is_active') ? (bool) $request->is_active : $item->is_active,
            ]);
            $this->clearCache();

            return response()->json([
                'status'  => true,
                'message' => 'Tujuan tambahan berhasil diupdate',
                'data'    => $item,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal update: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: delete tujuan tambahan.
     */
    public function delete($id)
    {
        try {
            $item = TujuanTambahan::findOrFail($id);
            $item->delete();
            $this->clearCache();

            return response()->json([
                'status'  => true,
                'message' => 'Tujuan tambahan berhasil dihapus',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus: ' . $th->getMessage(),
            ], 500);
        }
    }
}
