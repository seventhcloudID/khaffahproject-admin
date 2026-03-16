<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeDokumen extends Model
{
    use HasFactory;

    protected $table = 'tipe_dokumen_m';

    public $timestamps = false;

    protected $fillable = [
        'kode',
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dokumens()
    {
        return $this->hasMany(Dokumen::class, 'tipe_dokumen_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKode($query, string $kode)
    {
        return $query->where('kode', $kode);
    }

    public static function getByKode(string $kode)
    {
        return static::where('kode', $kode)->first();
    }

}