<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelFoto extends Model
{
    protected $table = 'hotel_foto_m';

    protected $fillable = [
        'hotel_id',
        'url_foto',
        'urutan',
        'is_active',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }
}
