<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandaraKepulangan extends Model
{
    protected $table = 'bandara_kepulangan_m';

    protected $fillable = [
        'is_active',
        'kode',
        'nama',
        'urutan',
    ];
}
