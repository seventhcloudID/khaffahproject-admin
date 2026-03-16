<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubRole extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'subrole_m';

    // Kolom yang boleh diisi
    protected $fillable = [
        'nama_role',
        'deskripsi',
    ];

    /**
     * Relasi ke User
     * 1 subrole dimiliki banyak user
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sub_roles_s', 'sub_role_id', 'user_id');
    }

    public function modulAplikasi()
    {
        return $this->belongsToMany(ModulAplikasi::class, 'subrole_modul_aplikasi_s', 'sub_role_id', 'modul_id');
    }
}
