<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi_m';

    protected $fillable = [
        'is_active',
        'akun_id',
        'dibuat_sebagai_mitra',
        'gelar_id',
        'nama_lengkap',
        'no_whatsapp',
        'provinsi_id',
        'kota_id',
        'kecamatan_id',
        'alamat_lengkap',
        'keberangkatan_id',
        'deskripsi',
        'jenis_transaksi_id',
        'produk_id',
        'snapshot_produk',
        'jamaah_data',
        'kode_transaksi',
        'is_with_payment',
        'total_biaya',
        'nomor_pembayaran',
        'tanggal_transaksi',
        'status_pembayaran_id',
        'status_transaksi_id',
        'refund_alasan',
        'refund_requested_at',
    ];

    protected $casts = [
        'snapshot_produk' => 'array',
        'jamaah_data' => 'array',
        'refund_requested_at' => 'datetime',
        'dibuat_sebagai_mitra' => 'boolean',
    ];

    // === RELATION ===
    public function jenisTransaksi()
    {
        return $this->belongsTo(JenisTransaksi::class, 'jenis_transaksi_id');
    }

    public function statusPembayaran()
    {
        return $this->belongsTo(StatusPembayaran::class, 'status_pembayaran_id');
    }

    public function statusTransaksi()
    {
        return $this->belongsTo(StatusTransaksi::class, 'status_transaksi_id');
    }
}
