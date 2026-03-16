<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketHaji extends Model
{
    use HasFactory;

    protected $table = 'paket_haji_m';

    protected $fillable = [
        'is_active',
        'nama_paket',
        'biaya_per_pax',
        'akomodasi',
        'deskripsi_akomodasi',
        'waktu_tunggu_min',
        'waktu_tunggu_max',
        'deskripsi_waktu_tunggu',
        'fasilitas_tambahan',
        'deskripsi_fasilitas',
    ];

    protected $casts = [
        'akomodasi' => 'array',
        'fasilitas_tambahan' => 'array',
    ];
}
