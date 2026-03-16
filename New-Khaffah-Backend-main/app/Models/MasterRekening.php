<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRekening extends Model
{
    protected $table = 'master_rekening_m';

    protected $fillable = [
        'bank_name',
        'account_number',
        'account_holder_name',
        'keterangan',
        'is_active',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
