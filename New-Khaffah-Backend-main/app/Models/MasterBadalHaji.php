<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBadalHaji extends Model
{
    protected $table = 'master_badal_haji_m';

    protected $fillable = [
        'nama_layanan',
        'slug',
        'deskripsi',
        'harga',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'harga' => 'integer',
        'is_active' => 'boolean',
    ];
}
