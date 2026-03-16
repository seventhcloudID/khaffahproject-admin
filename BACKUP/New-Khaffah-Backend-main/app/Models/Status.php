<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'status_m';

    protected $fillable = [
        'is_active',
        'kode',
        'nama_status',
        'deskripsi',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKode($query, $kode)
    {
        return $query->where('kode', $kode);
    }

    // Helper method untuk mendapatkan status berdasarkan kode
    public static function getByKode($kode)
    {
        return static::where('kode', $kode)->first();
    }

    public static function getIdByKode($kode)
    {
        $result = static::active()
            ->where('kode', $kode)
            ->value('id');

        return $result ? (int) $result : null;
    }
}
