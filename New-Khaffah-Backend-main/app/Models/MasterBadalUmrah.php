<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBadalUmrah extends Model
{
    protected $table = 'master_badal_umrah_m';

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

