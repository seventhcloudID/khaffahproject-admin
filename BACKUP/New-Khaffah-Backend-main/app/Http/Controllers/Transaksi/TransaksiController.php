<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\JenisTransaksi;
use App\Models\PembayaranTransaksi;
use App\Models\StatusTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransaksiController extends Controller
{

    public function getListPeminatEdutrip(Request $request)
    {
        try {

            $status = $request->input('status');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->join('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->join('paket_edutrip_m as pem', 'pem.id', '=', 'tm.produk_id')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftjoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->select(
                    'tm.id',
                    'gm.gelar',
                    'jm.nama_jenis',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.deskripsi',
                    'pem.nama_paket',
                    'pem.jumlah_hari',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'tm.tanggal_transaksi',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'tm.snapshot_produk',
                )
                ->where('tm.is_active', true)
                ->where('jm.id', 3);
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween(DB::raw("DATE(tm.snapshot_produk->>'tanggal_kunjungan')"), [$tglAwal, $tglAkhir]);
            }

            // Filter berdasarkan status
            $status = $request->query('status');

            if ($status) {
                switch ($status) {
                    case 'belum':
                        $query->whereIn('stm.kode', ['MENUNGGU_PEMBAYARAN', 'BELUM_DIHUBUNGI', 'DIHUBUNGI']);
                        break;
                    case 'diproses':
                        $query->whereIn('stm.kode', ['DIPROSES', 'TERKONFIRMASI']);
                        break;
                    case 'selesai':
                        $query->where('stm.kode', 'SELESAI');
                        break;
                    case 'batal':
                        $query->whereIn('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN']);
                        break;
                }
            }

            $data = $query->orderBy('tm.created_at', 'desc')->get();

            $data = $data->map(function ($item) {
                $item->snapshot_produk = json_decode($item->snapshot_produk);
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'List Peminat Edutrip berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data Peminat Edutrip: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getCountPeminatEdutrip(Request $request)
    {
        $tglAwal = $request->input('tglAwal');
        $tglAkhir = $request->input('tglAkhir');

        try {
            $query = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 3);
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween(DB::raw("DATE(tm.snapshot_produk->>'tanggal_kunjungan')"), [$tglAwal, $tglAkhir]);
            }
            
            $data = $query->selectRaw("
                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('MENUNGGU_PEMBAYARAN','BELUM_DIHUBUNGI','DIHUBUNGI') 
                    THEN 1 ELSE 0 END), 0) AS belum_diproses,

                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('DIPROSES','TERKONFIRMASI') 
                    THEN 1 ELSE 0 END), 0) AS diproses,

                COALESCE(SUM(CASE 
                    WHEN stm.kode = 'SELESAI'
                    THEN 1 ELSE 0 END), 0) AS selesai,

                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') 
                    THEN 1 ELSE 0 END), 0) AS dibatalkan
            ")
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'Count Peminat Edutrip berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil count: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getListPendaftaranHaji(Request $request)
    {
        try {
            $status = $request->input('status');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('transaksi_m as tm')
                ->leftjoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftjoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftjoin('paket_haji_m as phm', 'phm.id', '=', 'tm.produk_id')
                ->leftjoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftjoin('provinsi_m as pvm', 'pvm.id', '=', 'tm.provinsi_id')
                ->leftjoin('kota_m as km', 'km.id', '=', 'tm.kota_id')
                ->leftjoin('kecamatan_m as kcm', 'kcm.id', '=', 'tm.kecamatan_id')
                ->leftjoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->select(
                    'tm.id',
                    'gm.gelar',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.provinsi_id',
                    'tm.deskripsi',
                    'tm.alamat_lengkap',
                    'phm.nama_paket',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'tm.tanggal_transaksi',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'stm.kode as status_kode',
                    'tm.snapshot_produk',
                    'pvm.nama_provinsi',
                    'km.nama_kota',
                    'kcm.nama_kecamatan',
                    'tm.jamaah_data',
                    'tm.created_at as tgl_pemesanan',
                )
                ->where('tm.is_active', true)
                ->where('jm.id', 2);
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }

            // Filter berdasarkan status
            $status = $request->query('status');

            if ($status) {
                switch ($status) {
                    case 'belum':
                        $query->whereIn('stm.kode', ['BELUM_DIHUBUNGI', 'DIHUBUNGI']);
                        break;
                    case 'diproses':
                        $query->whereIn('stm.kode', ['MENUNGGU_PEMBAYARAN', 'DIPROSES', 'TERKONFIRMASI']);
                        break;
                    case 'selesai':
                        $query->where('stm.kode', 'SELESAI');
                        break;
                    case 'batal':
                        $query->whereIn('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN']);
                        break;
                }
            }

            $data = $query->orderBy('tm.created_at', 'desc')->get();

            $data = $data->map(function ($item) {
                $item->snapshot_produk = json_decode($item->snapshot_produk);
                $item->jamaah_data = json_decode($item->jamaah_data);
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'List Pendaftar Haji berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data Pendaftar Haji: ' . $th->getMessage(),
            ], 500);
        }
    }


    public function getCountPendaftaranHaji(Request $request)
    {
        $tglAwal = $request->input('tglAwal');
        $tglAkhir = $request->input('tglAkhir');

        try {
            $query = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 2);
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }
            
            $data = $query->selectRaw("
                    COALESCE(SUM(CASE 
                        WHEN stm.kode IN ('BELUM_DIHUBUNGI','DIHUBUNGI') 
                        THEN 1 ELSE 0 END), 0) AS belum_diproses,

                    COALESCE(SUM(CASE 
                        WHEN stm.kode IN ('MENUNGGU_PEMBAYARAN','DIPROSES','TERKONFIRMASI') 
                        THEN 1 ELSE 0 END), 0) AS diproses,

                    COALESCE(SUM(CASE 
                        WHEN stm.kode = 'SELESAI' 
                        THEN 1 ELSE 0 END), 0) AS selesai,

                    COALESCE(SUM(CASE 
                        WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') 
                        THEN 1 ELSE 0 END), 0) AS dibatalkan
                ")
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'Count Pendaftaran Haji berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil count: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getListPemesananUmrah(Request $request)
    {
        try {
            $jenisUmrah = JenisTransaksi::where('kode', 'PAKET_UMRAH')->first();
            if (! $jenisUmrah) {
                return response()->json([
                    'status' => true,
                    'message' => 'List Pemesanan Umrah berhasil diambil',
                    'data' => [],
                ], 200);
            }

            $status = $request->input('status');
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('transaksi_m as tm')
                ->leftjoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftjoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftjoin('paket_umrah_m as pum', 'pum.id', '=', 'tm.produk_id')
                ->leftjoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftjoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->leftjoin('paket_umrah_keberangkatan_t as puk', 'puk.id', '=', 'tm.keberangkatan_id')
                ->select(
                    'tm.id',
                    'tm.dibuat_sebagai_mitra',
                    'tm.keberangkatan_id',
                    'gm.gelar',
                    'jm.nama_jenis',
                    'tm.nama_lengkap',
                    'tm.created_at as tgl_pemesanan',
                    'tm.tanggal_transaksi',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.deskripsi',
                    'pum.nama_paket',
                    'pum.durasi_total as paket_durasi_total',
                    'pum.deskripsi as paket_deskripsi',
                    'tm.snapshot_produk',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'stm.kode',
                    'tm.jamaah_data',
                    'puk.tanggal_berangkat',
                    'puk.tanggal_pulang',
                    'puk.jam_berangkat',
                    'puk.jam_pulang',
                )
                ->where('tm.is_active', true)
                ->where('tm.jenis_transaksi_id', $jenisUmrah->id);

            // Filter sumber: user = hanya pemesanan dari user, mitra = hanya dari mitra, semua = tampilkan semua
            $sumber = $request->query('sumber');
            if ($sumber === 'user') {
                $query->where(function ($q) {
                    $q->where('tm.dibuat_sebagai_mitra', false)->orWhereNull('tm.dibuat_sebagai_mitra');
                });
            } elseif ($sumber === 'mitra') {
                $query->where('tm.dibuat_sebagai_mitra', true);
            }
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }
            
            $query->orderBy('tm.created_at', 'desc');

            // Filter berdasarkan status
            $status = $request->query('status');

            if ($status) {
                switch ($status) {
                    case 'belum':
                        $query->wherein('stm.kode', ['BELUM_DIHUBUNGI', 'DIHUBUNGI', 'MENUNGGU_PEMBAYARAN']);
                        break;
                    case 'pembayaran':
                        $query->wherein('stm.kode', ['MENUNGGU_VERIFIKASI_PEMBAYARAN', 'PEMBAYARAN_DITOLAK']);
                        break;
                    case 'diproses':
                        $query->wherein('stm.kode', ['DIPROSES', 'TERKONFIRMASI', 'SIAP_BERANGKAT']);
                        break;
                    case 'berlangsung':
                        $query->wherein('stm.kode', ['BERANGKAT', 'PULANG']);
                        break;
                    case 'selesai':
                        $query->wherein('stm.kode', ['SELESAI']);
                        break;
                    case 'batal':
                        $query->wherein('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN']);
                        break;
                }
            }

            $data = $query->get();

            $data = $data->map(function ($item) {
                $snap = json_decode($item->snapshot_produk, true);
                if (! is_array($snap)) {
                    $snap = [];
                }
                // Isi durasi & jadwal dari master jika belum ada di snapshot (pesanan lama)
                if (empty($snap['durasi_total']) && isset($item->paket_durasi_total)) {
                    $snap['durasi_total'] = $item->paket_durasi_total;
                }
                if (empty($snap['deskripsi']) && ! empty($item->paket_deskripsi)) {
                    $snap['deskripsi'] = $item->paket_deskripsi;
                }
                if (empty($snap['keberangkatan']) && ! empty($item->tanggal_berangkat)) {
                    $snap['keberangkatan'] = [
                        'id' => $item->keberangkatan_id,
                        'tanggal_berangkat' => $item->tanggal_berangkat,
                        'tanggal_pulang' => $item->tanggal_pulang,
                        'jam_berangkat' => $item->jam_berangkat ?? null,
                        'jam_pulang' => $item->jam_pulang ?? null,
                    ];
                }
                $item->snapshot_produk = $snap;
                $item->jamaah_data = json_decode($item->jamaah_data);
                unset($item->paket_durasi_total, $item->paket_deskripsi, $item->tanggal_berangkat, $item->tanggal_pulang, $item->jam_berangkat, $item->jam_pulang, $item->keberangkatan_id);
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'List Pemesanan Umrah berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data Pemesanan Umrah: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function getCountPemesananUmrah(Request $request)
    {
        $jenisUmrah = JenisTransaksi::where('kode', 'PAKET_UMRAH')->first();
        if (! $jenisUmrah) {
            return response()->json([
                'status' => true,
                'message' => 'Count Pemesanan Umrah berhasil diambil',
                'data' => (object) [
                    'belum_diproses' => 0,
                    'pembayaran' => 0,
                    'diproses' => 0,
                    'berlangsung' => 0,
                    'selesai' => 0,
                    'dibatalkan' => 0,
                ],
            ], 200);
        }

        $tglAwal = $request->input('tglAwal');
        $tglAkhir = $request->input('tglAkhir');
        $sumber = $request->query('sumber');

        try {
            $query = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->where('tm.is_active', true)
                ->where('tm.jenis_transaksi_id', $jenisUmrah->id);

            if ($sumber === 'user') {
                $query->where(function ($q) {
                    $q->where('tm.dibuat_sebagai_mitra', false)->orWhereNull('tm.dibuat_sebagai_mitra');
                });
            } elseif ($sumber === 'mitra') {
                $query->where('tm.dibuat_sebagai_mitra', true);
            }
            
            // Filter tanggal hanya jika ada parameter tanggal
            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }
            
            $data = $query->selectRaw("
                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('BELUM_DIHUBUNGI','DIHUBUNGI','MENUNGGU_PEMBAYARAN') 
                    THEN 1 ELSE 0 END), 0) AS belum_diproses,

                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('MENUNGGU_VERIFIKASI_PEMBAYARAN', 'PEMBAYARAN_DITOLAK') 
                    THEN 1 ELSE 0 END), 0) AS pembayaran,

                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('DIPROSES','TERKONFIRMASI','SIAP_BERANGKAT') 
                    THEN 1 ELSE 0 END), 0) AS diproses,

                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('BERANGKAT','PULANG') 
                    THEN 1 ELSE 0 END), 0) AS berlangsung,

                COALESCE(SUM(CASE 
                    WHEN stm.kode = 'SELESAI' 
                    THEN 1 ELSE 0 END), 0) AS selesai,

                COALESCE(SUM(CASE 
                    WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') 
                    THEN 1 ELSE 0 END), 0) AS dibatalkan
            ")
                ->first();

            return response()->json([
                'status' => true,
                'message' => 'Count Pemesanan Umrah berhasil diambil',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil count: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Cek jumlah transaksi per jenis (untuk debug/verifikasi).
     * GET ?tglAwal=...&tglAkhir=... opsional.
     */
    public function getCountLaporanKeuangan(Request $request)
    {
        try {
            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $qUmrah = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 1);
            if ($tglAwal && $tglAkhir) {
                $qUmrah->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }
            $umrah = $qUmrah->count();

            $qHaji = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 2);
            if ($tglAwal && $tglAkhir) {
                $qHaji->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }
            $haji = $qHaji->count();

            $qEdutrip = DB::table('transaksi_m as tm')
                ->join('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->where('tm.is_active', true)
                ->where('jm.id', 3);
            if ($tglAwal && $tglAkhir) {
                $qEdutrip->whereBetween(DB::raw("DATE(tm.snapshot_produk->>'tanggal_kunjungan')"), [$tglAwal, $tglAkhir]);
            }
            $edutrip = $qEdutrip->count();

            return response()->json([
                'status' => true,
                'message' => 'Count laporan keuangan',
                'data' => [
                    'umrah' => $umrah,
                    'haji' => $haji,
                    'edutrip' => $edutrip,
                    'total' => $umrah + $haji + $edutrip,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Satu endpoint untuk halaman Laporan Keuangan: mengembalikan list transaksi
     * Umrah, Haji, dan Edutrip dalam satu response. Parameter opsional: tglAwal, tglAkhir.
     */
    public function getLaporanKeuangan(Request $request)
    {
        try {
            $umrahRes = $this->getListPemesananUmrah($request);
            $hajiRes = $this->getListPendaftaranHaji($request);
            $edutripRes = $this->getListPeminatEdutrip($request);
            $customRes = $this->getListPermintaanCustom($request);

            $umrahData = $umrahRes->getData(true);
            $hajiData = $hajiRes->getData(true);
            $edutripData = $edutripRes->getData(true);
            $customData = $customRes->getData(true);

            return response()->json([
                'status' => true,
                'message' => 'Data laporan keuangan berhasil diambil',
                'data' => [
                    'umrah' => $umrahData['data'] ?? [],
                    'haji' => $hajiData['data'] ?? [],
                    'edutrip' => $edutripData['data'] ?? [],
                    'custom' => $customData['data'] ?? [],
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data laporan keuangan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * List transaksi Permintaan Custom (jenis_transaksi REQUEST).
     * Mengakomodir data dari halaman product-request / custom umrah / land arrangement.
     */
    public function getListPermintaanCustom(Request $request)
    {
        try {
            // Pastikan jenis transaksi REQUEST (paket request / custom umrah / LA) ada
            $jenisRequest = JenisTransaksi::where('kode', 'REQUEST')->first();
            if (! $jenisRequest) {
                return response()->json([
                    'status'  => true,
                    'message' => 'List Permintaan Custom berhasil diambil',
                    'data'    => [],
                ], 200);
            }

            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');
            $status = $request->query('status');

            $query = DB::table('transaksi_m as tm')
                ->leftJoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftJoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftJoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->leftJoin('status_pembayaran_m as spm', 'spm.id', '=', 'tm.status_pembayaran_id')
                ->select(
                    'tm.id',
                    'gm.gelar',
                    'jm.nama_jenis',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.kode_transaksi',
                    'tm.deskripsi',
                    'tm.total_biaya',
                    'tm.status_pembayaran_id',
                    'spm.kode as status_pembayaran_kode',
                    'spm.nama_status as status_pembayaran_nama',
                    'tm.tanggal_transaksi',
                    'tm.created_at as tgl_pemesanan',
                    'stm.id as status_id',
                    'stm.nama_status',
                    'stm.kode as status_kode',
                    'tm.snapshot_produk',
                    'tm.jamaah_data',
                )
                ->where('tm.is_active', true)
                ->where('tm.jenis_transaksi_id', $jenisRequest->id);

            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }

            if ($status) {
                switch ($status) {
                    case 'belum':
                        $query->whereIn('stm.kode', ['BELUM_DIHUBUNGI', 'DIHUBUNGI', 'MENUNGGU_PEMBAYARAN']);
                        break;
                    case 'diproses':
                        $query->whereIn('stm.kode', ['DIPROSES', 'TERKONFIRMASI', 'MENUNGGU_VERIFIKASI_PEMBAYARAN']);
                        break;
                    case 'selesai':
                        $query->where('stm.kode', 'SELESAI');
                        break;
                    case 'batal':
                        $query->whereIn('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN']);
                        break;
                }
            }

            $data = $query->orderBy('tm.created_at', 'desc')->get();

            $data = $data->map(function ($item) {
                // Kolom jsonb bisa sudah array dari driver; json_decode(string) saja
                $item->snapshot_produk = is_string($item->snapshot_produk)
                    ? json_decode($item->snapshot_produk, true)
                    : (is_object($item->snapshot_produk)
                        ? (array) $item->snapshot_produk
                        : (is_array($item->snapshot_produk) ? $item->snapshot_produk : []));
                $item->jamaah_data = is_string($item->jamaah_data)
                    ? json_decode($item->jamaah_data)
                    : $item->jamaah_data;

                $snap = $item->snapshot_produk ?? [];

                // Nama paket / tipe permintaan
                $item->nama_paket = $snap && (array_key_exists('kategori_paket', $snap) || array_key_exists('tipe', $snap) || array_key_exists('nama_paket', $snap))
                    ? (data_get($snap, 'kategori_paket') ?? data_get($snap, 'tipe') ?? data_get($snap, 'nama_paket') ?? 'Permintaan Custom')
                    : 'Permintaan Custom';

                // Total biaya: kalau kolom total_biaya di transaksi 0, coba ambil dari snapshot
                $currentTotal = (float) ($item->total_biaya ?? 0);
                if ($currentTotal <= 0 && is_array($snap)) {
                    // 1) Coba baca langsung field total dari snapshot (order baru)
                    $candidates = [
                        data_get($snap, 'totalBiaya'),
                        data_get($snap, 'total_biaya'),
                        data_get($snap, 'total_biaya_order'),
                    ];
                    foreach ($candidates as $val) {
                        if ($val === null || $val === '') {
                            continue;
                        }
                        $num = is_numeric($val)
                            ? (float) $val
                            : (float) preg_replace('/[^0-9.]/', '', (string) $val);
                        if ($num > 0) {
                            $item->total_biaya = $num;
                            $currentTotal = $num;
                            break;
                        }
                    }

                    // 2) Fallback legacy: hitung ulang dari data_hotel + layanan_wajib + layanan_tambahan
                    if ($currentTotal <= 0) {
                        $jamaahRaw = $item->jamaah_data ?? [];
                        $jamaahCount = is_array($jamaahRaw) ? max(count($jamaahRaw), 1) : 1;

                        $tanggalProgram = data_get($snap, 'tanggal_program_umrah', []);
                        $dep = data_get($tanggalProgram, 'departureDate');
                        $ret = data_get($tanggalProgram, 'returnDate');
                        $depDate = $dep ? new \DateTime($dep) : new \DateTime();
                        $retDate = $ret ? new \DateTime($ret) : (clone $depDate)->modify('+9 days');
                        $duration = max((int) $depDate->diff($retDate)->format('%a') ?: 9, 1);

                        $dataHotel = data_get($snap, 'data_hotel', []);
                        $hotelMekkahHarga = (float) ($dataHotel['hotelMekkahHarga'] ?? 0);
                        $hotelMadinahHarga = (float) ($dataHotel['hotelMadinahHarga'] ?? 0);
                        $kamar = max((int) ($dataHotel['kuotaKamar'] ?? 1), 1);

                        $totalHotel = 0;
                        if ($hotelMekkahHarga > 0) {
                            $totalHotel += $hotelMekkahHarga * $kamar * $duration;
                        }
                        if ($hotelMadinahHarga > 0) {
                            $totalHotel += $hotelMadinahHarga * $kamar * $duration;
                        }

                        $toLayananValue = function (array $itemL) use ($jamaahCount, $duration) {
                            $base = isset($itemL['harga']) ? (float) $itemL['harga'] : 0;
                            if ($base <= 0) {
                                return 0;
                            }
                            $satuan = strtolower((string) ($itemL['satuan'] ?? ''));
                            $perPax = preg_match('/pax|orang/', $satuan);
                            $perHari = preg_match('/hari/', $satuan);
                            $mult = $perPax ? $jamaahCount : ($perHari ? $duration : 1);
                            return $base * $mult;
                        };

                        $totalLayanan = 0;
                        $layananWajibSnap = data_get($snap, 'layanan_wajib', []);
                        if (is_array($layananWajibSnap)) {
                            foreach ($layananWajibSnap as $lw) {
                                if (is_array($lw)) {
                                    $totalLayanan += $toLayananValue($lw);
                                }
                            }
                        }
                        $layananTambahanSnap = data_get($snap, 'layanan_tambahan', []);
                        if (is_array($layananTambahanSnap)) {
                            foreach ($layananTambahanSnap as $lt) {
                                if (is_array($lt)) {
                                    $totalLayanan += $toLayananValue($lt);
                                }
                            }
                        }

                        $recalcTotal = $totalHotel + $totalLayanan;
                        if ($recalcTotal > 0) {
                            $item->total_biaya = $recalcTotal;
                        }
                    }
                }

                // Total pembayaran yang sudah dikonfirmasi (verified) oleh admin.
                // Digunakan untuk laporan keuangan / jurnal agar nominal mengikuti uang yang benar-benar sudah masuk.
                $verifiedTotal = DB::table('pembayaran_transaksi_m')
                    ->where('transaksi_id', $item->id)
                    ->where(function ($q) {
                        $q->where('is_active', true)->orWhereNull('is_active');
                    })
                    ->where('status', 'verified')
                    ->sum('nominal_transfer');
                $item->total_pembayaran_verified = (float) $verifiedTotal;

                return $item;
            });

            return response()->json([
                'status'  => true,
                'message' => 'List Permintaan Custom berhasil diambil',
                'data'    => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data Permintaan Custom: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Count per status untuk Permintaan Custom.
     */
    public function getCountPermintaanCustom(Request $request)
    {
        $tglAwal = $request->input('tglAwal');
        $tglAkhir = $request->input('tglAkhir');

        try {
            $jenisRequest = JenisTransaksi::where('kode', 'REQUEST')->first();
            if (! $jenisRequest) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Count Permintaan Custom berhasil diambil',
                    'data'    => [
                        'belum_diproses' => 0,
                        'diproses'       => 0,
                        'selesai'        => 0,
                        'dibatalkan'     => 0,
                    ],
                ], 200);
            }

            $query = DB::table('transaksi_m as tm')
                ->join('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->where('tm.is_active', true)
                ->where('tm.jenis_transaksi_id', $jenisRequest->id);

            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.created_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }

            $data = $query->selectRaw("
                COALESCE(SUM(CASE WHEN stm.kode IN ('BELUM_DIHUBUNGI','DIHUBUNGI','MENUNGGU_PEMBAYARAN') THEN 1 ELSE 0 END), 0) AS belum_diproses,
                COALESCE(SUM(CASE WHEN stm.kode IN ('DIPROSES','TERKONFIRMASI','MENUNGGU_VERIFIKASI_PEMBAYARAN') THEN 1 ELSE 0 END), 0) AS diproses,
                COALESCE(SUM(CASE WHEN stm.kode = 'SELESAI' THEN 1 ELSE 0 END), 0) AS selesai,
                COALESCE(SUM(CASE WHEN stm.kode IN ('DIBATALKAN','REFUND_DIAJUKAN') THEN 1 ELSE 0 END), 0) AS dibatalkan
            ")->first();

            return response()->json([
                'status'  => true,
                'message' => 'Count Permintaan Custom berhasil diambil',
                'data'    => [
                    'belum_diproses' => (int) ($data->belum_diproses ?? 0),
                    'diproses'       => (int) ($data->diproses ?? 0),
                    'selesai'        => (int) ($data->selesai ?? 0),
                    'dibatalkan'     => (int) ($data->dibatalkan ?? 0),
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil count Permintaan Custom: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * List transaksi dengan status REFUND_DIAJUKAN (untuk admin daftar refund).
     */
    public function getListRefund(Request $request)
    {
        try {
            $statusRefund = StatusTransaksi::where('kode', 'REFUND_DIAJUKAN')->first();
            if (! $statusRefund) {
                return response()->json([
                    'status'  => true,
                    'message' => 'List Refund berhasil diambil',
                    'data'    => [],
                ], 200);
            }

            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('transaksi_m as tm')
                ->leftJoin('users as u', 'u.id', '=', 'tm.akun_id')
                ->leftJoin('gelar_m as gm', 'gm.id', '=', 'tm.gelar_id')
                ->leftJoin('jenis_transaksi_m as jm', 'jm.id', '=', 'tm.jenis_transaksi_id')
                ->leftJoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                ->select(
                    'tm.id',
                    'tm.jenis_transaksi_id',
                    'tm.kode_transaksi',
                    'tm.nama_lengkap',
                    'tm.no_whatsapp',
                    'tm.total_biaya',
                    'tm.refund_alasan',
                    'tm.refund_requested_at',
                    'tm.created_at as tgl_pemesanan',
                    'tm.snapshot_produk',
                    'tm.dibuat_sebagai_mitra',
                    'gm.gelar',
                    'jm.nama_jenis',
                    'jm.kode as jenis_kode',
                    'stm.nama_status as status_nama',
                    'stm.kode as status_kode',
                    'u.nama_lengkap as pemesan_nama',
                    'u.email as pemesan_email',
                )
                ->where('tm.is_active', true)
                ->where('tm.status_transaksi_id', $statusRefund->id);

            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.refund_requested_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }

            $data = $query->orderByDesc('tm.refund_requested_at')->get();

            $data = $data->map(function ($item) {
                $item->snapshot_produk = is_string($item->snapshot_produk)
                    ? json_decode($item->snapshot_produk, true)
                    : (is_object($item->snapshot_produk)
                        ? (array) $item->snapshot_produk
                        : (is_array($item->snapshot_produk) ? $item->snapshot_produk : []));
                $snap = $item->snapshot_produk ?? [];
                $item->nama_paket = $snap['nama_paket'] ?? $snap['kategori_paket'] ?? 'Paket';
                return $item;
            });

            return response()->json([
                'status'  => true,
                'message' => 'List Refund berhasil diambil',
                'data'    => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil data Refund: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Count transaksi REFUND_DIAJUKAN (untuk badge/tab admin).
     */
    public function getCountRefund(Request $request)
    {
        try {
            $statusRefund = StatusTransaksi::where('kode', 'REFUND_DIAJUKAN')->first();
            if (! $statusRefund) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Count Refund berhasil diambil',
                    'data'    => ['diajukan' => 0],
                ], 200);
            }

            $tglAwal = $request->input('tglAwal');
            $tglAkhir = $request->input('tglAkhir');

            $query = DB::table('transaksi_m as tm')
                ->where('tm.is_active', true)
                ->where('tm.status_transaksi_id', $statusRefund->id);

            if ($tglAwal && $tglAkhir) {
                $query->whereBetween('tm.refund_requested_at', [$tglAwal . ' 00:00:00', $tglAkhir . ' 23:59:59']);
            }

            $count = $query->count();

            return response()->json([
                'status'  => true,
                'message' => 'Count Refund berhasil diambil',
                'data'    => ['diajukan' => (int) $count],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal mengambil count Refund: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete transaksi pendaftaran haji (jenis_transaksi_id = 2).
     */
    public function deletePendaftaranHaji($id)
    {
        try {
            $transaksi = Transaksi::where('id', $id)->where('jenis_transaksi_id', 2)->first();
            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pendaftaran haji tidak ditemukan.',
                ], 404);
            }
            $transaksi->is_active = false;
            $transaksi->save();
            return response()->json([
                'status' => true,
                'message' => 'Pendaftaran haji berhasil dihapus',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete transaksi pemesanan umrah (jenis_transaksi_id = 1).
     */
    public function deletePemesananUmrah($id)
    {
        try {
            $transaksi = Transaksi::where('id', $id)->where('jenis_transaksi_id', 1)->first();
            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pemesanan umrah tidak ditemukan.',
                ], 404);
            }
            $transaksi->is_active = false;
            $transaksi->save();
            return response()->json([
                'status' => true,
                'message' => 'Pemesanan umrah berhasil dihapus',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete transaksi peminat edutrip (jenis_transaksi_id = 3).
     */
    public function deletePeminatEdutrip($id)
    {
        try {
            $transaksi = Transaksi::where('id', $id)->where('jenis_transaksi_id', 3)->first();
            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data peminat edutrip tidak ditemukan.',
                ], 404);
            }
            $transaksi->is_active = false;
            $transaksi->save();
            return response()->json([
                'status' => true,
                'message' => 'Peminat edutrip berhasil dihapus',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function updateStatusTransaksi(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'id' => 'required|integer|exists:transaksi_m,id',
                'status_id' => 'required|integer|exists:status_transaksi_m,id',
            ]);

            $transaksi = Transaksi::find($validated['id']);

            if (!$transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data transaksi tidak ditemukan.',
                ], 404);
            }

            // Update status
            $transaksi->status_transaksi_id = $validated['status_id'];
            $transaksi->save();

            return response()->json([
                'status' => true, // << sama seperti API lain
                'message' => 'Status transaksi berhasil diperbarui',
                'data' => [
                    'id' => $transaksi->id,
                    'status_transaksi_id' => $transaksi->status_transaksi_id,
                    'updated_at' => $transaksi->updated_at,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update status: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update status pembayaran transaksi (Belum Bayar, Lunas, dll).
     * POST id, status_pembayaran_id
     */
    public function updateStatusPembayaran(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:transaksi_m,id',
                'status_pembayaran_id' => 'required|integer|exists:status_pembayaran_m,id',
            ]);

            $transaksi = Transaksi::find($validated['id']);
            if (! $transaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data transaksi tidak ditemukan.',
                ], 404);
            }

            $transaksi->status_pembayaran_id = $validated['status_pembayaran_id'];
            $transaksi->save();

            return response()->json([
                'status' => true,
                'message' => 'Status pembayaran berhasil diperbarui',
                'data' => [
                    'id' => $transaksi->id,
                    'status_pembayaran_id' => $transaksi->status_pembayaran_id,
                    'updated_at' => $transaksi->updated_at,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal update status pembayaran: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview file KTP/Paspor dari storage private (untuk admin).
     * Query: ?path=private/ktp/xxx.png atau path=private/paspor/xxx.png
     */
    public function previewFile(Request $request)
    {
        $path = $request->query('path');
        if (! $path || ! is_string($path)) {
            abort(404);
        }
        // Izinkan path: private/ktp, private/paspor, private/bukti_pembayaran
        $path = ltrim($path, '/');
        if (
            ! (str_starts_with($path, 'private/ktp/')
                || str_starts_with($path, 'private/paspor/')
                || str_starts_with($path, 'private/bukti_pembayaran/'))
        ) {
            abort(403, 'Path tidak diizinkan');
        }
        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }
        $mime = Storage::disk('local')->mimeType($path);
        $content = Storage::disk('local')->get($path);
        return response($content, 200, [
            'Content-Type' => $mime ?: 'image/png',
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Verifikasi bukti pembayaran oleh admin (setujui / tolak).
     * Hanya untuk role superadmin. Mengubah status pembayaran jadi verified atau rejected.
     */
    public function verifikasiBuktiPembayaran(Request $request)
    {
        $validated = $request->validate([
            'pembayaran_id' => 'required|integer|exists:pembayaran_transaksi_m,id',
            'status' => 'required|string|in:verified,rejected',
        ]);

        $pembayaran = PembayaranTransaksi::find($validated['pembayaran_id']);
        if (! $pembayaran || ! $pembayaran->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran tidak ditemukan.',
            ], 404);
        }

        $user = Auth::user();
        $pembayaran->status = $validated['status'];
        $pembayaran->verified_by = $user->id;
        $pembayaran->verified_at = now();
        $pembayaran->save();

        return response()->json([
            'status' => true,
            'message' => $validated['status'] === 'verified'
                ? 'Bukti pembayaran disetujui.'
                : 'Bukti pembayaran ditolak.',
            'data' => [
                'id' => $pembayaran->id,
                'status' => $pembayaran->status,
            ],
        ], 200);
    }
}
