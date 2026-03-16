<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandaraKeberangkatan extends Model
{
    protected $table = 'bandara_keberangkatan_m';

    protected $fillable = [
        'is_active',
        'kode',
        'nama',
        'urutan',
    ];
}
