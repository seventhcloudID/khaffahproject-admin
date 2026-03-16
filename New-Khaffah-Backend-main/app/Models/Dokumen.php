<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    use HasFactory;

    protected $table = 'dokumen_m';

    protected $fillable = [
        'is_active',
        'tipe_owner_id',
        'owner_id',
        'tipe_dokumen_id',
        'file_path',
        'file_hash',
        'mime_type',
        'status_id',
        'uploaded_by',
        'verified_by',
        'verified_at',
        'alasan_ditolak',
        'extra_data',
        'superseded_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'superseded_at' => 'datetime',
        'extra_data' => 'array',
    ];

    // Relationships
    public function tipeOwner()
    {
        return $this->belongsTo(TipeOwner::class, 'tipe_owner_id');
    }

    public function tipeDokumen()
    {
        return $this->belongsTo(TipeDokumen::class, 'tipe_dokumen_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Polymorphic relationship untuk owner
    public function owner()
    {
        // Anda perlu mengimplementasikan logika polymorphic manual
        // karena menggunakan tipe_owner_id dan owner_id
        // Contoh implementasi ada di method getOwnerAttribute
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $statusKode)
    {
        return $query->whereHas('status', function ($q) use ($statusKode) {
            $q->where('kode', $statusKode);
        });
    }

    public function scopePending($query)
    {
        return $query->byStatus('pending');
    }

    public function scopeSedangDireview($query)
    {
        return $query->byStatus('sedang_direview');
    }

    public function scopeDisetujui($query)
    {
        return $query->byStatus('disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->byStatus('ditolak');
    }

    public function scopeNotSuperseded($query)
    {
        return $query->whereNull('superseded_at');
    }

    // Helper methods
    public function setStatus(string $statusKode, ?User $user = null, ?string $notes = null)
    {
        $status = Status::where('kode', $statusKode)->first();
        
        if (!$status) {
            throw new \Exception("Status dengan kode '{$statusKode}' tidak ditemukan");
        }

        $data = ['status_id' => $status->id];

        if ($user) {
            $data['verified_by'] = $user->id;
            $data['verified_at'] = now();
        }

        if ($notes) {
            $data['notes'] = $notes;
        }

        $this->update($data);
    }

    public function approve(User $verifier, ?string $notes = null)
    {
        $this->setStatus('disetujui', $verifier, $notes);
    }

    public function reject(User $verifier, ?string $notes = null)
    {
        $this->setStatus('ditolak', $verifier, $notes);
    }

    public function review(?User $verifier = null)
    {
        $this->setStatus('sedang_direview', $verifier);
    }

    public function supersede()
    {
        $this->update([
            'superseded_at' => now(),
        ]);
    }

    public function isPending()
    {
        return $this->status?->kode === 'pending';
    }

    public function isSedangDireview()
    {
        return $this->status?->kode === 'sedang_direview';
    }

    public function isDisetujui()
    {
        return $this->status?->kode === 'disetujui';
    }

    public function isDitolak()
    {
        return $this->status?->kode === 'ditolak';
    }

    public function isSuperseded()
    {
        return !is_null($this->superseded_at);
    }
}