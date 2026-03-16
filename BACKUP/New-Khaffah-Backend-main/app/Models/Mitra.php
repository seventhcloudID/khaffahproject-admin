<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mitra extends Model
{
    protected $table = 'mitra_m';

    protected $fillable = [
        'user_id',
        'is_active',
        'nama_lengkap',
        'nik',
        'provinsi_id',
        'kota_id',
        'kecamatan_id',
        'alamat_lengkap',
        'nomor_ijin_usaha',
        'masa_berlaku_ijin_usaha',
        'status_id',
        'mitra_level_id',
        'verified_by',
        'verified_at',
        'alasan_ditolak',
    ];

    protected $casts = [
        'masa_berlaku_ijin_usaha' => 'date',
        'verified_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kota(): BelongsTo
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(MitraLevel::class, 'mitra_level_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Semua dokumen milik mitra ini
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'owner_id')
            ->whereHas('ownerType', function ($q) {
                $q->where('kode', 'mitra');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS HELPERS (INI PENTING)
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status?->kode === 'pending';
    }

    public function isProcessed(): bool
    {
        return $this->status?->kode === 'diproses';
    }

    public function isApproved(): bool
    {
        return $this->status?->kode === 'disetujui';
    }

    public function isRejected(): bool
    {
        return $this->status?->kode === 'ditolak';
    }
}
