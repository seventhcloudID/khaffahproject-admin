<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelKamar extends Model
{
    protected $table = 'hotel_kamar_m';

    protected $fillable = [
        'hotel_id',
        'tipe_kamar_id',
        'nama_kamar',
        'kapasitas',
        'harga_per_malam',
        'is_active',
    ];

    protected $casts = [
        'harga_per_malam' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function tipeKamar()
    {
        return $this->belongsTo(TipeKamar::class, 'tipe_kamar_id');
    }
}
