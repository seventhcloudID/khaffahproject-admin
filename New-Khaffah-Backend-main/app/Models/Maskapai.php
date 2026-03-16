<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maskapai extends Model
{
    use HasFactory;

    protected $table = 'maskapai_m';

    protected $fillable = [
        'is_active',
        'kode_iata',
        'nama_maskapai',
        'negara_asal',
        'logo_url',
        'jam_keberangkatan',
        'jam_sampai',
        'kelas_penerbangan_id',
        'harga_tiket_per_orang',
    ];

    protected $casts = [
        'harga_tiket_per_orang' => 'decimal:2',
    ];

    public function kelasPenerbangan()
    {
        return $this->belongsTo(KelasPenerbangan::class, 'kelas_penerbangan_id');
    }
}
