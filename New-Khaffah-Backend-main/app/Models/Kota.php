<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kota extends Model
{
    use HasFactory;

    protected $table = 'kota_m';

    protected $fillable = [
        'nama_kota',
        'provinsi_id',
        'negara_id',
        'kode_iata',
        'zona_waktu',
    ];
}
