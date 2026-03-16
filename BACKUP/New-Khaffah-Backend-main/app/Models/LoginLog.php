<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginLog extends Model
{
    use HasFactory;

    protected $table = 'login_logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'platform',
        'browser',
        'logged_in_at',
        'logged_out_at',
    ];

    protected $casts = [
        'logged_in_at'  => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
