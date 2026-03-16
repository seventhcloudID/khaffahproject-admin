<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketUmrah extends Model
{
    use HasFactory;

    protected $table = 'paket_umrah_m';

    protected $fillable = [
        'nama_paket',
        'deskripsi',
        'musim_id',
        'lokasi_keberangkatan_id',
        'lokasi_tujuan_id',
        'durasi_total',
        'jumlah_pax',
        'harga_termurah',
        'harga_termahal',
        'ringkasan_maskapai',
        'ringkasan_hotel'
    ];

    public function maskapai()
    {
        return $this->belongsToMany(Maskapai::class, 'paket_umrah_maskapai_t');
    }

    public function hotel()
    {
        return $this->belongsToMany(Hotel::class, 'paket_umrah_hotel_t', 'paket_umrah_id', 'hotel_id');
    }

    public function musim()
    {
        return $this->belongsTo(Musim::class, 'musim_id');
    }

    public function reviews()
    {
        return $this->hasMany(PaketUmrahReview::class, 'paket_umrah_id');
    }
}
