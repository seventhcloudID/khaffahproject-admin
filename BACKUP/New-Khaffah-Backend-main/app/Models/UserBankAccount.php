<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBankAccount extends Model
{
    use HasFactory;

    protected $table = 'user_bank_accounts_m';

    protected $fillable = [
        'akun_id',
        'bank_name',
        'account_number',
        'account_holder_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'akun_id');
    }
}
