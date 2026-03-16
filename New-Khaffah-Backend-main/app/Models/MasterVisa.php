<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterVisa extends Model
{
    protected $table = 'master_visa_m';

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
