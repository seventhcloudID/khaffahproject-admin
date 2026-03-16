<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisTransaksi extends Model
{
    protected $table = 'jenis_transaksi_m';
    protected $fillable = ['kode', 'nama', 'deskripsi'];
}
