<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $table = 'hotel_m';

    protected $fillable = [
        'is_active',
        'nama_hotel',
        'kota_id',
        'jarak_ke_masjid',
        'bintang',
        'alamat',
        'jam_checkin_mulai',
        'jam_checkin_berakhir',
        'jam_checkout_mulai',
        'jam_checkout_berakhir',
        'latitude',
        'longitude',
    ];

    public function kota()
    {
        return $this->belongsTo(\App\Models\Kota::class, 'kota_id');
    }

    public function foto()
    {
        return $this->hasMany(\App\Models\HotelFoto::class, 'hotel_id')->orderBy('urutan');
    }

    public function kamar()
    {
        return $this->hasMany(\App\Models\HotelKamar::class, 'hotel_id');
    }
}
