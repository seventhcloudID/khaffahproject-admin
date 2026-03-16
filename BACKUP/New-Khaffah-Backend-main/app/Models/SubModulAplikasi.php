<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubModulAplikasi extends Model
{
    protected $table = 'sub_modul_aplikasi_s';

    protected $fillable = [
        'modul_id',
        'is_active',
        'urutan',
        'nama_sub_modul',
        'url',
        'icon_id',
        'fa_icon_class',
    ];

    public function modul()
    {
        return $this->belongsTo(ModulAplikasi::class, 'modul_id');
    }
}
