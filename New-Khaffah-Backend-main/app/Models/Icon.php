<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icon extends Model
{
    use HasFactory;

    protected $table = 'icon_m'; // nama tabel sesuai migration kamu

    protected $fillable = [
        'is_active',
        'nama_icon',
        'kode',
        'url',
        'class',
    ];

    /**
     * URL icon selalu dikembalikan sebagai full URL ke storage backend
     * (storage/app/public/icon/) agar frontend bisa load dari domain backend.
     */
    public function getUrlAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return $value;
        }
        // Path di DB: /storage/icon/Shirt.svg -> full URL ke backend
        return asset(ltrim($value, '/'));
    }
}
