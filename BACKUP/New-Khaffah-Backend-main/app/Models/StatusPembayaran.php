<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusPembayaran extends Model
{
    protected $table = 'status_pembayaran_m';
    protected $fillable = ['kode', 'nama'];
}
