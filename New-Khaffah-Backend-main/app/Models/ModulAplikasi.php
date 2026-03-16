<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulAplikasi extends Model
{
    protected $table = 'modul_aplikasi_s';

    protected $fillable = [
        'is_active',
        'urutan',
        'nama_modul',
        'icon_id',
        'fa_icon_class',
    ];

    public function subModul()
    {
        return $this->hasMany(SubModulAplikasi::class, 'modul_id');
    }

    public function subroles()
    {
        return $this->belongsToMany(SubRole::class, 'subrole_modul_aplikasi_s', 'modul_id', 'sub_role_id')
            ->withTimestamps();
    }
}
