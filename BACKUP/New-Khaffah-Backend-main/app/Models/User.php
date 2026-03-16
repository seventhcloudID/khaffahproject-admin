<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lengkap',
        'is_active',
        'jenis_kelamin',
        'tgl_lahir',
        'email',
        'no_handphone',
        'password',
        'foto_profile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function subroles()
    {
        return $this->belongsToMany(SubRole::class, 'user_sub_roles_s', 'user_id', 'sub_role_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(UserBankAccount::class, 'akun_id');
    }

    public function mitra()
    {
        return $this->hasOne(Mitra::class);
    }
}
