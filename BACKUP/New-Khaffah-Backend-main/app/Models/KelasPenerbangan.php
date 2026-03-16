<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasPenerbangan extends Model
{
    protected $table = 'kelas_penerbangan_m';

    protected $fillable = [
        'is_active',
        'kelas_penerbangan',
    ];
}
