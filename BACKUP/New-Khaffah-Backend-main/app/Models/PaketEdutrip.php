<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketEdutrip extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     *
     * @var string
     */
    protected $table = 'paket_edutrip_m';

    /**
     * Kolom yang bisa diisi mass assignment
     *
     * @var array
     */
    protected $fillable = [
        'nama_paket',
        'jumlah_hari',
        'deskripsi',
    ];

    /**
     * Tipe data casting untuk kolom tertentu
     *
     * @var array
     */
    protected $casts = [
        'jumlah_hari' => 'integer',
    ];
}