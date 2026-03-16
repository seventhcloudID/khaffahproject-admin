<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Status;

trait StoresDocuments
{
    /**
     *
     * @param UploadedFile $file
     * @param int $ownerTypeId   -> owner_type_m.id
     * @param int $ownerId       -> jamaah_id / mitra_id / dll
     * @param int $documentTypeId-> document_type_m.id
     * @param int|null $uploadedBy
     * @param array|null $extraData
     *
     * @return int document_id
     */
    protected function storeDocument(
        UploadedFile $file,
        int $ownerTypeId,
        int $ownerId,
        int $documentTypeId,
        ?int $uploadedBy = null,
        ?array $extraData = null
    ): int {
        // 1. Prepare folder & filename
        $folder = 'documents/' . $ownerTypeId . '/' . $ownerId;
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        // 2. Read & hash original content
        $content = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $content);

        // 4. Save encrypted file
        Storage::disk('private')->put(
            $path,
            encrypt($content)
        );
        // 4. Insert DB record
        $statusId = Status::getIdByKode('pending');
        return DB::table('dokumen_m')->insertGetId([
            'tipe_owner_id'    => $ownerTypeId,
            'owner_id'         => $ownerId,
            'tipe_dokumen_id'  => $documentTypeId,
            'file_path'        => $path,
            'file_hash'        => $hash,
            'mime_type'        => $file->getClientMimeType(),
            'status_id'        => Status::getIdByKode('pending'),
            'uploaded_by'      => $uploadedBy,
            'extra_data'       => $extraData ? json_encode($extraData) : null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
    protected function replaceDocument(
        UploadedFile $file,
        int $ownerTypeId,
        int $ownerId,
        int $documentTypeId,
        ?int $uploadedBy = null,
        ?array $extraData = null
    ): int {
        // 1. Nonaktifkan dokumen aktif sebelumnya
        DB::table('dokumen_m')
            ->where('tipe_owner_id', $ownerTypeId)
            ->where('owner_id', $ownerId)
            ->where('tipe_dokumen_id', $documentTypeId)
            ->where('is_active', true)
            ->update([
                'is_active'      => false,
                'superseded_at'  => now(),
                'updated_at'     => now(),
            ]);

        // 2. Simpan dokumen baru
        return $this->storeDocument(
            $file,
            $ownerTypeId,
            $ownerId,
            $documentTypeId,
            $uploadedBy,
            $extraData
        );
    }
    protected function getDocumentsByOwner(
        int $ownerTypeId,
        int $ownerId,
        bool $onlyActive = true
    ) {
        $query = DB::table('dokumen_m as d')
            ->join('tipe_dokumen_m as td', 'td.id', '=', 'd.tipe_dokumen_id')
            ->select(
                'd.id',
                'td.kode as tipe_dokumen',
                'td.nama as nama_dokumen',
                'd.status_id',
                'd.mime_type',
                'd.created_at',
                'd.uploaded_by'
            )
            ->where('d.tipe_owner_id', $ownerTypeId)
            ->where('d.owner_id', $ownerId);

        if ($onlyActive) {
            $query->where('d.is_active', true);
        }

        return $query->orderBy('d.created_at')->get();
    }
}
