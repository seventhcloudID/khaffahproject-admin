<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananPaketRequest extends Model
{
    protected $table = 'layanan_paket_request_m';

    protected $fillable = [
        'nama',
        'harga',
        'satuan',
        'jenis',
        'urutan',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'harga'     => 'decimal:0',
        'urutan'    => 'integer',
        'is_active' => 'boolean',
    ];

    public const JENIS_WAJIB = 'wajib';
    public const JENIS_TAMBAHAN = 'tambahan';
}
