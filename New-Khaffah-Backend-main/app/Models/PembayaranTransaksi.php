<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranTransaksi extends Model
{
    protected $table = 'pembayaran_transaksi_m';

    protected $fillable = [
        'is_active',
        'transaksi_id',
        'nomor_pembayaran',
        'kode_unik',
        'nominal_asli',
        'nominal_transfer',
        'moota_reference',
        'bank_pengirim',
        'nama_pengirim',
        'bank_tujuan',
        'tanggal_transfer',
        'status',
        'bukti_pembayaran',
        'validasi_manual',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'validasi_manual' => 'boolean',
        'nominal_asli' => 'decimal:2',
        'nominal_transfer' => 'decimal:2',
        'tanggal_transfer' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
