<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeOwner extends Model
{
    use HasFactory;

    protected $table = 'tipe_owner_m';

    public $timestamps = false;

    protected $fillable = [
        'kode',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function dokumens()
    {
        return $this->hasMany(Dokumen::class, 'tipe_owner_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKode($query, string $kode)
    {
        return $query->where('kode', $kode);
    }

    // Helper method
    public static function getByKode(string $kode)
    {
        return static::where('kode', $kode)->first();
    }
}