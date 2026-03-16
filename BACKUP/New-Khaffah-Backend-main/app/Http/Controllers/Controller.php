<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getOwnerTypeId(string $kode): int
    {
        $id = DB::table('tipe_owner_m')->where('kode', $kode)->value('id');

        if (!$id) {
            throw new \Exception("Tipe owner dengan kode '{$kode}' tidak ditemukan");
        }

        return $id;
    }

    protected function getDocumentTypeId(string $kode): int
    {
        $id = DB::table('tipe_dokumen_m')->where('kode', $kode)->value('id');

        if (!$id) {
            throw new \Exception("Tipe dokumen dengan kode '{$kode}' tidak ditemukan");
        }
        return $id;
    }

    protected function userOwnsDocument($user, $dokumen): bool
    {
        // Cast ke int agar perbandingan konsisten (DB bisa mengembalikan string)
        $tipeOwnerId = (int) $dokumen->tipe_owner_id;
        $ownerId = (int) $dokumen->owner_id;

        if ($tipeOwnerId === $this->getOwnerTypeId('mitra')) {
            return DB::table('mitra_m')
                ->where('id', $ownerId)
                ->where('user_id', $user->id)
                ->exists();
        }

        if ($tipeOwnerId === $this->getOwnerTypeId('jamaah')) {
            return DB::table('jamaah_m')
                ->where('id', $ownerId)
                ->where('akun_id', $user->id)
                ->exists();
        }

        return false;
    }
}
