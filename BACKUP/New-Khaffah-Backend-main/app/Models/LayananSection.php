<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananSection extends Model
{
    protected $table = 'layanan_section_m';

    protected $fillable = ['jenis', 'judul', 'deskripsi'];

    public const JENIS_WAJIB = 'wajib';
    public const JENIS_TAMBAHAN = 'tambahan';
}
