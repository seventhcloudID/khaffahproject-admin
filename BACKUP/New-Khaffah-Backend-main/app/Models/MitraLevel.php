<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MitraLevel extends Model
{
    protected $table = 'mitra_level_m';

    protected $fillable = [
        'nama_level',
        'persen_potongan',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'persen_potongan' => 'float',
        'is_active' => 'boolean',
    ];

    public function mitra(): HasMany
    {
        return $this->hasMany(Mitra::class, 'mitra_level_id');
    }
}
