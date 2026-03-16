<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner_m';

    protected $fillable = [
        'judul',
        'teks',
        'gambar',
        'lokasi',
        'urutan',
        'is_aktif',
        'link',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'urutan' => 'integer',
    ];
}
