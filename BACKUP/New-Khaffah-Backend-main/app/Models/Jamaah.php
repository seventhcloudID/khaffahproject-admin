<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jamaah extends Model
{
    use HasFactory;

    protected $table = 'jamaah_m';

    protected $fillable = [
        'akun_id',
        'is_active',
        'nama_lengkap',
        'nomor_identitas',
        'nomor_passpor',
    ];

    protected $hidden = ['akun_id', 'created_at', 'updated_at'];
}
