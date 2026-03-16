<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use App\Models\LayananPaketRequest;
use App\Models\LayananSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LayananPaketRequestController extends Controller
{
    /** TTL cache get-list admin (detik). Invalidasi setelah create/update/delete. */
    const LIST_CACHE_TTL = 60;
    const LIST_CACHE_KEY = 'layanan_paket_request_admin_list';
    /**
     * List untuk tampilan user (public): section + layanan aktif, urut.
     * GET /api/layanan-paket-request/list
     */
    public function listForDisplay()
    {
        try {
            $sections = LayananSection::orderBy('jenis')->get(['id', 'jenis', 'judul', 'deskripsi']);
            $items = LayananPaketRequest::where('is_active', true)
                ->orderBy('jenis')
                ->orderBy('urutan')
                ->get(['id', 'nama', 'harga', 'satuan', 'jenis', 'urutan', 'deskripsi']);

            $wajib = $items->where('jenis', LayananPaketRequest::JENIS_WAJIB)->values();
            $tambahan = $items->where('jenis', LayananPaketRequest::JENIS_TAMBAHAN)->values();
            $sectionWajib = $sections->where('jenis', 'wajib')->first();
            $sectionTambahan = $sections->where('jenis', 'tambahan')->first();

            return response()->json([
                'status'  => true,
                'message' => 'OK',
                'data'    => [
                    'section_wajib'    => $sectionWajib,
                    'section_tambahan' => $sectionTambahan,
                    'layanan_wajib'    => $wajib,
                    'layanan_tambahan' => $tambahan,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: list semua section + layanan (dengan cache singkat).
     */
    public function getList(Request $request)
    {
        try {
            $data = Cache::remember(self::LIST_CACHE_KEY, self::LIST_CACHE_TTL, function () {
                $sections = LayananSection::orderBy('jenis')->get();
                $items = LayananPaketRequest::orderBy('jenis')->orderBy('urutan')->get();
                return ['sections' => $sections, 'items' => $items];
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

    private function clearLayananListCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
    }

    /**
     * Admin: update section (judul + deskripsi).
     */
    public function updateSection(Request $request, $id)
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $section = LayananSection::findOrFail($id);
            $section->update([
                'judul'     => $request->judul,
                'deskripsi' => $request->deskripsi,
            ]);
            $this->clearLayananListCache();

            return response()->json([
                'status'  => true,
                'message' => 'Section berhasil diupdate',
                'data'    => $section,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal update: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Admin: create layanan item.
     */
    public function createLayanan(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'harga'     => 'required|numeric|min:0',
            'satuan'    => 'nullable|string|max:50',
            'jenis'     => 'required|in:wajib,tambahan',
            'urutan'    => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $maxUrutan = LayananPaketRequest::where('jenis', $request->jenis)->max('urutan') ?? 0;
            $item = LayananPaketRequest::create([
                'nama'      => $request->nama,
                'harga'     => (int) $request->harga,
                'satuan'    => $request->satuan ?: '/pax',
                'jenis'     => $request->jenis,
                'urutan'    => $request->urutan ?? $maxUrutan + 1,
                'deskripsi' => $request->deskripsi,
                'is_active' => true,
            ]);
            $this->clearLayananListCache();

            return response()->json([
                'status'  => true,
                'message' => 'Layanan berhasil ditambah',
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
     * Admin: update layanan item.
     */
    public function updateLayanan(Request $request, $id)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'harga'     => 'required|numeric|min:0',
            'satuan'    => 'nullable|string|max:50',
            'jenis'     => 'required|in:wajib,tambahan',
            'urutan'    => 'nullable|integer|min:0',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $item = LayananPaketRequest::findOrFail($id);
            $item->update([
                'nama'      => $request->nama,
                'harga'     => (int) $request->harga,
                'satuan'    => $request->satuan ?: '/pax',
                'jenis'     => $request->jenis,
                'urutan'    => $request->urutan ?? $item->urutan,
                'deskripsi' => $request->deskripsi,
                'is_active' => $request->has('is_active') ? (bool) $request->is_active : $item->is_active,
            ]);
            $this->clearLayananListCache();

            return response()->json([
                'status'  => true,
                'message' => 'Layanan berhasil diupdate',
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
     * Admin: delete layanan item.
     */
    public function deleteLayanan($id)
    {
        try {
            $item = LayananPaketRequest::findOrFail($id);
            $item->delete();
            $this->clearLayananListCache();

            return response()->json([
                'status'  => true,
                'message' => 'Layanan berhasil dihapus',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus: ' . $th->getMessage(),
            ], 500);
        }
    }
}
