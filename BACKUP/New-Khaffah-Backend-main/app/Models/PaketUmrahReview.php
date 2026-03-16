<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaketUmrahReview extends Model
{
    protected $table = 'paket_umrah_review_t';

    protected $fillable = [
        'is_active',
        'paket_umrah_id',
        'user_id',
        'rating',
        'komentar',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
    ];

    public function paketUmrah(): BelongsTo
    {
        return $this->belongsTo(PaketUmrah::class, 'paket_umrah_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
