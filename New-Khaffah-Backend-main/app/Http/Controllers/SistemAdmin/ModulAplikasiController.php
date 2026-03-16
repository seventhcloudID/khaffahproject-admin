<?php

namespace App\Http\Controllers\SistemAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModulAplikasiController extends Controller
{

    public function getModulDinamis(Request $request)
    {
        try {
            $subroleNames = $request->input('subroles'); // array of text

            if (!$subroleNames || !is_array($subroleNames)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subrole tidak valid atau tidak dikirim'
                ], 400);
            }

            // --- Ambil subrole record (id + dashboard url)
            $subroles = DB::table('subrole_m')
                ->whereIn('nama_role', $subroleNames)
                ->get();

            if ($subroles->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subrole tidak ditemukan'
                ], 404);
            }

            $isSuperadmin = $subroles->contains('nama_role', 'superadmin');

            $subroleIds = $subroles->pluck('id')->toArray();

            // --- Ambil modul yang diijinkan
            if ($isSuperadmin) {
                // Jika superadmin, ambil SEMUA modul aktif
                $modul = DB::table('modul_aplikasi_s')
                    ->where('is_active', true)
                    ->orderBy('urutan')
                    ->get();

                $modulAllowed = $modul->pluck('id')->toArray();

                $sub = DB::table('sub_modul_aplikasi_s')
                    ->where('is_active', true)
                    ->whereIn('modul_id', $modulAllowed)
                    ->orderBy('urutan')
                    ->get();
            } else {
                // Jika bukan superadmin, ambil berdasarkan mapping
                $modulAllowed = DB::table('subrole_modul_aplikasi_s')
                    ->whereIn('sub_role_id', $subroleIds)
                    ->pluck('modul_id')
                    ->unique()
                    ->toArray();

                $modul = DB::table('modul_aplikasi_s')
                    ->where('is_active', true)
                    ->whereIn('id', $modulAllowed)
                    ->orderBy('urutan')
                    ->get();

                $sub = DB::table('sub_modul_aplikasi_s')
                    ->where('is_active', true)
                    ->whereIn('modul_id', $modulAllowed)
                    ->orderBy('urutan')
                    ->get();
            }

            // Dashboard tidak ditambahkan dari API agar tidak dobel dengan link Dashboard tetap di sidebar

            $modulData = $modul->map(function ($m) use ($sub) {
                return [
                    'modul_id'   => $m->id,
                    'nama_modul' => $m->nama_modul,
                    'urutan'     => $m->urutan,
                    'icon_url'   => $m->icon_id,
                    'icon_class' => $m->fa_icon_class,
                    'child'      => $sub->where('modul_id', $m->id)->map(function ($s) {
                        return [
                            'modul_id'        => $s->modul_id,
                            'sub_modul_id'    => $s->id,
                            'urutan'          => $s->urutan,
                            'nama_sub_modul'  => $s->nama_sub_modul,
                            'url'             => $s->url,
                            'icon_url'        => $s->icon_id,
                            'icon_class'      => $s->fa_icon_class,
                        ];
                    })->values(),
                ];
            });

            $finalData = $modulData->values()->all();

            return response()->json([
                'status'  => true,
                'message' => 'List modul dinamis berhasil diambil',
                'data'    => $finalData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data modul: ' . $th->getMessage(),
            ], 500);
        }
    }


    public function getMasterModul()
    {
        try {
            // ambil modul
            $data = DB::table('modul_aplikasi_s')
                ->where('is_active', true)
                ->orderBy('urutan')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'List modul berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data modul: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getSubMasterModul(Request $request)
    {
        try {

            $modul_id = $request->input('modul_id');

            $data = DB::table('sub_modul_aplikasi_s')
                ->where('modul_id', '=', $modul_id)
                ->where('is_active', true)
                ->orderBy('urutan')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'List modul berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data modul: ' . $th->getMessage(),
            ], 500);
        }
    }
}
