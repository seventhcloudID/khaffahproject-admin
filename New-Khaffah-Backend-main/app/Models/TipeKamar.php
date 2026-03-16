<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeKamar extends Model
{
    protected $table = 'tipe_kamar_m';

    protected $fillable = [
        'is_active',
        'tipe_kamar',
        'kapasitas',
    ];
}
