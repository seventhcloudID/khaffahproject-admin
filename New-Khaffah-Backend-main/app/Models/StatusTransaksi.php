<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusTransaksi extends Model
{
    protected $table = 'status_transaksi_m';
    protected $fillable = ['kode', 'nama'];
}
