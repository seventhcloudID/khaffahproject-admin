<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TujuanTambahan extends Model
{
    protected $table = 'tujuan_tambahan_m';

    protected $fillable = [
        'nama',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'urutan'    => 'integer',
        'is_active' => 'boolean',
    ];
}
