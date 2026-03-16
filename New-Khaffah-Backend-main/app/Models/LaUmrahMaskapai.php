<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaUmrahMaskapai extends Model
{
    protected $table = 'la_umrah_maskapai_t';

    protected $fillable = ['maskapai_id', 'urutan', 'is_active'];

    protected $casts = [
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function maskapai(): BelongsTo
    {
        return $this->belongsTo(Maskapai::class, 'maskapai_id');
    }
}
