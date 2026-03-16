<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provinsi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provinsi_m';

    protected $fillable = [
        'is_active',
        'kode',
        'nama_provinsi',
        'slug',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function kotas()
    {
        return $this->hasMany(Kota::class, 'provinsi_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}