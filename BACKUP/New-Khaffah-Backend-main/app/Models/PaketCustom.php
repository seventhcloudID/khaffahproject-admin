<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketCustom extends Model
{
    use HasFactory;

    protected $table = 'paket_custom_m';

    protected $fillable = [
        'nama_paket',
        'tipe_paket',
        'negara_liburan',
        'jumlah_hari',
        'estimasi_biaya',
        'deskripsi',
        'catatan_internal',
        'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'jumlah_hari'    => 'integer',
        'estimasi_biaya' => 'decimal:2',
        'negara_liburan' => 'array', // ['Turki', 'Mesir', 'Yordania'] — hanya untuk tipe Umrah Plus Liburan
    ];
}
