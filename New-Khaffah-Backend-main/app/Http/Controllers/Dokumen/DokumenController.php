<?php

namespace App\Http\Controllers\Dokumen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Models\Dokumen;


class DokumenController extends Controller
{
    public function preview(Request $request, int $id)
    {
        $user = $request->user();

        $dokumen = DB::table('dokumen_m')->where('id', $id)->first();

        if (! $dokumen) {
            abort(404);
        }

        // =========================
        // AUTHORIZATION (PENTING)
        // =========================
        // contoh:
        // - superadmin boleh
        // - mitra hanya milik sendiri
        if (
            ! $user->hasRole('superadmin') &&
            ! $this->userOwnsDocument($user, $dokumen)
        ) {
            abort(403);
        }

        if (! Storage::disk('private')->exists($dokumen->file_path)) {
            abort(404);
        }

        // =========================
        // DECRYPT FILE
        // =========================
        $encrypted = Storage::disk('private')->get($dokumen->file_path);
        $content = Crypt::decrypt($encrypted);

        return response($content, 200, [
            'Content-Type' => $dokumen->mime_type,
            'Content-Disposition' => 'inline'
        ]);
    }

    public function setStatusDokumen(Request $request)
    {
        $user = $request->user();


        $validated = $request->validate([
            'id_dokumen' => 'required|integer|exists:dokumen_m,id',
            'status'     => 'required|in:disetujui,ditolak,sedang_direview',
            'alasan'     => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $dokumen = Dokumen::findOrFail($validated['id_dokumen']);


            if (! $user->hasRole('superadmin')) {
                abort(403, 'Tidak memiliki akses mengubah status dokumen');
            }


            switch ($validated['status']) {
                case 'disetujui':
                    $dokumen->approve($user);
                    break;

                case 'ditolak':
                    $dokumen->reject($user, $validated['alasan'] ?? null);
                    break;

                case 'sedang_direview':
                    $dokumen->review($user);
                    break;
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Status dokumen berhasil diperbarui',
                'data'    => [
                    'id'          => $dokumen->id,
                    'status'      => $validated['status'],
                    'verified_by' => $user->id,
                    'verified_at' => $dokumen->verified_at,
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
