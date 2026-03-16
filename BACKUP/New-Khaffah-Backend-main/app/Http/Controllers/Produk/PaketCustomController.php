<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use App\Models\PaketCustom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaketCustomController extends Controller
{
    /**
     * List paket custom untuk halaman product-request (public).
     * GET /api/paket-custom/list - tanpa auth, hanya paket aktif.
     */
    public function listForProductRequest()
    {
        try {
            $data = PaketCustom::where('is_active', true)
                ->orderBy('nama_paket')
                ->get(['id', 'nama_paket', 'tipe_paket', 'negara_liburan', 'jumlah_hari', 'estimasi_biaya', 'deskripsi']);

            return response()->json([
                'status'  => true,
                'message' => 'List paket request berhasil diambil',
                'data'    => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * List paket custom (admin). Optional filter by tglAwal, tglAkhir (created_at).
     */
    public function getPaketCustom(Request $request)
    {
        try {
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = PaketCustom::query()->orderBy('created_at', 'desc');

            if ($tglAwal && $tglAkhir) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$tglAwal, $tglAkhir]);
            }

            $data = $query->get();

            return response()->json([
                'status'  => true,
                'message' => 'List paket custom berhasil diambil',
                'data'    => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data paket custom: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Create paket custom.
     */
    public function createPaketCustom(Request $request)
    {
        $request->validate([
            'nama_paket'       => 'required|string|max:255',
            'tipe_paket'       => 'nullable|string|max:100',
            'negara_liburan'   => 'nullable|array',
            'negara_liburan.*' => 'nullable|string|max:100',
            'jumlah_hari'      => 'nullable|integer|min:1',
            'estimasi_biaya'   => 'nullable|numeric|min:0',
            'deskripsi'        => 'nullable|string',
            'catatan_internal' => 'nullable|string',
        ]);

        try {
            $negaraLiburan = $request->input('negara_liburan');
            if (is_array($negaraLiburan)) {
                $negaraLiburan = array_values(array_filter(array_map('trim', $negaraLiburan)));
            } else {
                $negaraLiburan = null;
            }

            $paket = PaketCustom::create([
                'nama_paket'       => $request->nama_paket,
                'tipe_paket'       => $request->tipe_paket,
                'negara_liburan'   => $negaraLiburan ?: null,
                'jumlah_hari'      => $request->jumlah_hari,
                'estimasi_biaya'   => $request->estimasi_biaya,
                'deskripsi'        => $request->deskripsi,
                'catatan_internal' => $request->catatan_internal,
                'is_active'        => true,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Paket custom berhasil dibuat',
                'data'    => $paket,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal membuat paket custom: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update paket custom.
     */
    public function updatePaketCustom(Request $request, $id)
    {
        $request->validate([
            'nama_paket'       => 'required|string|max:255',
            'tipe_paket'       => 'nullable|string|max:100',
            'negara_liburan'   => 'nullable|array',
            'negara_liburan.*' => 'nullable|string|max:100',
            'jumlah_hari'      => 'nullable|integer|min:1',
            'estimasi_biaya'   => 'nullable|numeric|min:0',
            'deskripsi'        => 'nullable|string',
            'catatan_internal' => 'nullable|string',
        ]);

        try {
            $negaraLiburan = $request->input('negara_liburan');
            if (is_array($negaraLiburan)) {
                $negaraLiburan = array_values(array_filter(array_map('trim', $negaraLiburan)));
            } else {
                $negaraLiburan = null;
            }

            $paket = PaketCustom::findOrFail($id);
            $paket->update([
                'nama_paket'       => $request->nama_paket,
                'tipe_paket'       => $request->tipe_paket,
                'negara_liburan'   => $negaraLiburan ?: null,
                'jumlah_hari'      => $request->jumlah_hari,
                'estimasi_biaya'   => $request->estimasi_biaya,
                'deskripsi'        => $request->deskripsi,
                'catatan_internal' => $request->catatan_internal,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Paket custom berhasil diupdate',
                'data'    => $paket,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengupdate paket custom: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete paket custom.
     */
    public function deletePaketCustom($id)
    {
        try {
            $paket = PaketCustom::findOrFail($id);
            $paket->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Paket custom berhasil dihapus',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menghapus paket custom: ' . $th->getMessage(),
            ], 500);
        }
    }
}
