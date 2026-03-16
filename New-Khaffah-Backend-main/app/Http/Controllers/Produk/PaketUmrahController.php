<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaketUmrah;
use App\Models\PaketUmrahReview;
use App\Models\Transaksi;
use App\Models\Mitra;
use App\Models\JenisTransaksi;
use App\Models\StatusPembayaran;
use App\Models\StatusTransaksi;
use App\Models\PembayaranTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class PaketUmrahController extends Controller
{
    /**
     * Ambil persen potongan mitra untuk user yang login (dari Bearer token atau auth).
     * Return 0 jika bukan mitra atau tidak punya level.
     */
    private function getMitraDiscountPercent(?Request $request = null): float
    {
        $user = auth()->user();
        if (! $user && $request?->bearerToken()) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            if ($token) {
                $tokenable = $token->tokenable;
                if ($tokenable instanceof \App\Models\User) {
                    $user = $tokenable;
                }
            }
        }
        if (! $user) {
            return 0;
        }
        $mitra = Mitra::where('user_id', $user->id)->with('level')->first();
        if (! $mitra || ! $mitra->level || ! $mitra->level->is_active) {
            return 0;
        }
        $persen = (float) $mitra->level->persen_potongan;
        return $persen > 0 ? $persen : 0;
    }

    /**
     * Hitung harga setelah potongan mitra.
     */
    private function applyMitraDiscount(float $harga, float $persenPotongan): float
    {
        if ($persenPotongan <= 0) {
            return $harga;
        }
        return round($harga * (1 - $persenPotongan / 100), 2);
    }

    /**
     * Daftar paket umrah untuk halaman buat pesanan / list paket.
     * GET /api/paket-umrah/list-paket
     */
    public function getListPaketUmrah(Request $request)
    {
        try {
            $persen = $this->getMitraDiscountPercent($request);
            $showAll = $request->input('show_all') === '1';
            $query = DB::table('paket_umrah_m as pum')
                ->leftJoin('musim_m as mm', 'mm.id', '=', 'pum.musim_id')
                ->where(function ($q) use ($showAll) {
                    if (! $showAll) {
                        $q->where('pum.is_active', true);
                    }
                });

            $pakets = $query->select('pum.id', 'pum.nama_paket', 'pum.durasi_total', 'pum.jumlah_pax',
                'pum.harga_termurah', 'pum.harga_termahal',
                'mm.nama_musim')
                ->orderBy('pum.created_at', 'desc')
                ->get();

            $data = [];
            foreach ($pakets as $p) {
                $id = (int) $p->id;
                $foto = DB::table('paket_umrah_foto_m')
                    ->where('paket_umrah_id', $id)
                    ->where('is_active', true)
                    ->orderBy('urutan')
                    ->value('url_foto');

                $maskapaiRow = DB::table('paket_umrah_maskapai_t as pumt')
                    ->join('maskapai_m as m', 'm.id', '=', 'pumt.maskapai_id')
                    ->leftJoin('kelas_penerbangan_m as kpm', 'kpm.id', '=', 'pumt.kelas_penerbangan_id')
                    ->where('pumt.paket_umrah_id', $id)
                    ->where('pumt.is_active', true)
                    ->select('m.nama_maskapai', 'kpm.kelas_penerbangan')
                    ->first();

                $bintang = DB::table('paket_umrah_hotel_t as puht')
                    ->join('hotel_m as h', 'h.id', '=', 'puht.hotel_id')
                    ->where('puht.paket_umrah_id', $id)
                    ->where('puht.is_active', true)
                    ->max('h.bintang');

                $destinasi = DB::table('paket_umrah_destinasi_t as pudt')
                    ->join('kota_m as k', 'k.id', '=', 'pudt.kota_id')
                    ->where('pudt.paket_umrah_id', $id)
                    ->where('pudt.is_active', true)
                    ->select('k.nama_kota as nama_kota', 'pudt.durasi')
                    ->get()
                    ->map(fn ($d) => ['nama_kota' => $d->nama_kota ?? '', 'durasi' => (int) ($d->durasi ?? 0)])
                    ->all();

                $kapasitasTotal = (int) ($p->jumlah_pax ?? 0);
                $terpakai = 0;
                $jenisUmrahId = \App\Models\JenisTransaksi::where('kode', 'PAKET_UMRAH')->value('id');
                if ($kapasitasTotal > 0 && $jenisUmrahId) {
                    $trx = DB::table('transaksi_m as tm')
                        ->leftJoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                        ->where('tm.is_active', true)
                        ->where('tm.jenis_transaksi_id', $jenisUmrahId)
                        ->where('tm.produk_id', $id)
                        ->whereNotIn('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN'])
                        ->get(['tm.jamaah_data']);
                    foreach ($trx as $t) {
                        $jd = $t->jamaah_data;
                        $arr = is_string($jd) ? json_decode($jd, true) : (is_array($jd) ? $jd : []);
                        if (is_array($arr)) {
                            $terpakai += count($arr);
                        }
                    }
                }
                $sisaPax = max(0, $kapasitasTotal - $terpakai);

                $hargaTermurahAsli = $p->harga_termurah !== null ? (float) $p->harga_termurah : 0;
                $hargaTermahalAsli = $p->harga_termahal !== null ? (float) $p->harga_termahal : null;
                if ($persen > 0) {
                    $hargaTermurah = $this->applyMitraDiscount($hargaTermurahAsli, $persen);
                    $hargaTermahal = $hargaTermahalAsli !== null ? $this->applyMitraDiscount($hargaTermahalAsli, $persen) : null;
                    $hargaAsliTermurah = $hargaTermurahAsli;
                    $hargaAsliTermahal = $hargaTermahalAsli;
                    $persenPotonganMitra = $persen;
                } else {
                    $hargaTermurah = $hargaTermurahAsli;
                    $hargaTermahal = $hargaTermahalAsli;
                    $hargaAsliTermurah = null;
                    $hargaAsliTermahal = null;
                    $persenPotonganMitra = 0;
                }

                $data[] = [
                    'id' => $id,
                    'nama_paket' => $p->nama_paket,
                    'durasi_total' => (int) ($p->durasi_total ?? 0),
                    'nama_musim' => $p->nama_musim ?? '',
                    'bintang' => $bintang ? (string) $bintang : '0',
                    'nama_maskapai' => $maskapaiRow ? ($maskapaiRow->nama_maskapai ?? '') : '',
                    'kelas_penerbangan' => $maskapaiRow ? ($maskapaiRow->kelas_penerbangan ?? '') : '',
                    'url_foto' => $foto ?? '',
                    'destinasi' => $destinasi,
                    'harga_termurah' => $hargaTermurah,
                    'harga_termahal' => $hargaTermahal,
                    'harga_asli_termurah' => $hargaAsliTermurah,
                    'harga_asli_termahal' => $hargaAsliTermahal,
                    'persen_potongan_mitra' => $persenPotonganMitra,
                    'paket_terjual' => $terpakai,
                    'sisa_paket' => $sisaPax,
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'List paket umrah berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil list paket: ' . $th->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Detail paket umrah untuk halaman [slug]. GET /api/paket-umrah/paket?paket_umrah_id={id}
     */
    public function getPaketUmrah(Request $request)
    {
        $id = (int) $request->query('paket_umrah_id');
        $forAdmin = $request->boolean('for_admin');
        if ($id <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter paket_umrah_id wajib dan harus positif',
                'data' => null,
            ], 400);
        }

        try {
            $pumQuery = DB::table('paket_umrah_m as pum')
                ->leftJoin('musim_m as mm', 'mm.id', '=', 'pum.musim_id')
                ->leftJoin('kota_m as kk', 'kk.id', '=', 'pum.lokasi_keberangkatan_id')
                ->leftJoin('kota_m as kt', 'kt.id', '=', 'pum.lokasi_tujuan_id')
                ->where('pum.id', $id);
            if (! $forAdmin) {
                $pumQuery->where('pum.is_active', true);
            }
            $pum = $pumQuery
                ->select(
                    'pum.*',
                    'mm.nama_musim',
                    DB::raw('COALESCE(kk.nama_kota, \'\') as lokasi_keberangkatan_nama'),
                    DB::raw('COALESCE(kt.nama_kota, \'\') as lokasi_tujuan_nama')
                )
                ->first();

            if (! $pum) {
                return response()->json([
                    'status' => false,
                    'message' => 'Paket umrah tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $fotoCols = $forAdmin ? ['id', 'url_foto', 'urutan'] : ['url_foto', 'urutan'];
            $fotoPaket = DB::table('paket_umrah_foto_m')
                ->where('paket_umrah_id', $id)
                ->where('is_active', true)
                ->orderBy('urutan')
                ->get($fotoCols)
                ->map(fn ($r) => array_merge(
                    ['url_foto' => $r->url_foto ?? '', 'urutan' => (int) ($r->urutan ?? 0)],
                    $forAdmin ? ['id' => (int) $r->id] : []
                ))
                ->all();

            $destinasiSelect = $forAdmin
                ? ['k.id as kota_id', 'k.nama_kota', 'pudt.durasi']
                : ['k.nama_kota', 'pudt.durasi'];
            $destinasi = DB::table('paket_umrah_destinasi_t as pudt')
                ->join('kota_m as k', 'k.id', '=', 'pudt.kota_id')
                ->where('pudt.paket_umrah_id', $id)
                ->where('pudt.is_active', true)
                ->orderBy('pudt.id')
                ->select($destinasiSelect)
                ->get()
                ->map(fn ($d) => array_merge(
                    ['nama_kota' => $d->nama_kota ?? '', 'durasi' => (int) ($d->durasi ?? 0)],
                    $forAdmin && isset($d->kota_id) ? ['kota_id' => (int) $d->kota_id] : []
                ))
                ->all();

            $hotelRows = DB::table('paket_umrah_hotel_t as puht')
                ->join('hotel_m as h', 'h.id', '=', 'puht.hotel_id')
                ->leftJoin('kota_m as k', 'k.id', '=', 'h.kota_id')
                ->where('puht.paket_umrah_id', $id)
                ->where('puht.is_active', true)
                ->orderBy('puht.id')
                ->select('h.id as id_hotel', 'h.nama_hotel', 'h.bintang', 'h.jarak_ke_masjid', 'k.nama_kota as kota')
                ->get();

            $hotel = [];
            foreach ($hotelRows as $hr) {
                $fasilitasHotel = DB::table('hotel_fasilitas_t as hft')
                    ->join('fasilitas_hotel_m as fhm', 'fhm.id', '=', 'hft.fasilitas_id')
                    ->where('hft.hotel_id', $hr->id_hotel)
                    ->where('hft.is_active', true)
                    ->select('fhm.id', 'fhm.nama')
                    ->get()
                    ->map(fn ($f) => ['id' => (int) $f->id, 'nama' => $f->nama ?? ''])
                    ->all();
                $hotel[] = [
                    'id_hotel' => (int) $hr->id_hotel,
                    'nama_hotel' => $hr->nama_hotel ?? '',
                    'kota' => $hr->kota ?? '',
                    'bintang' => $hr->bintang !== null ? (string) $hr->bintang : '0',
                    'jarak_ke_masjid' => $hr->jarak_ke_masjid ?? '',
                    'fasilitas_hotel' => $fasilitasHotel,
                ];
            }

            $maskapaiRows = DB::table('paket_umrah_maskapai_t as pumt')
                ->join('maskapai_m as m', 'm.id', '=', 'pumt.maskapai_id')
                ->where('pumt.paket_umrah_id', $id)
                ->where('pumt.is_active', true)
                ->select('m.id as id_maskapai', 'm.nama_maskapai', 'm.kode_iata', 'm.negara_asal', 'm.logo_url')
                ->get();

            $maskapai = $maskapaiRows->map(fn ($m) => [
                'id_maskapai' => (int) $m->id_maskapai,
                'nama_maskapai' => $m->nama_maskapai ?? '',
                'kode_iata' => $m->kode_iata ?? '',
                'negara_asal' => $m->negara_asal ?? '',
                'logo_url' => $m->logo_url ?? '',
            ])->all();

            $fasilitasByJenis = DB::table('paket_umrah_fasilitas_t as puft')
                ->join('fasilitas_m as fm', 'fm.id', '=', 'puft.fasilitas_id')
                ->join('jenis_fasilitas_m as jfm', 'jfm.id', '=', 'fm.jenis_fasilitas_id')
                ->leftJoin('icon_m as im', 'im.id', '=', 'jfm.icon_id')
                ->where('puft.paket_umrah_id', $id)
                ->where('puft.is_active', true)
                ->select('jfm.id as jenis_id', 'jfm.nama_jenis', 'im.id as icon_id', 'im.nama_icon as icon_nama', 'im.url as icon_url', 'fm.id as fasilitas_id', 'fm.nama_fasilitas')
                ->get();

            $fasilitasTambahan = [];
            foreach ($fasilitasByJenis->groupBy('jenis_id') as $jenisId => $items) {
                $first = $items->first();
                $fasilitasTambahan[] = [
                    'jenis_id' => (int) $jenisId,
                    'nama_jenis' => $first->nama_jenis ?? '',
                    'icon' => [
                        'id' => (int) ($first->icon_id ?? 0),
                        'nama' => $first->icon_nama ?? '',
                        'url' => $first->icon_url ?? '',
                    ],
                    'list' => $items->map(fn ($i) => ['id' => (int) $i->fasilitas_id, 'nama' => $i->nama_fasilitas ?? ''])->all(),
                ];
            }

            $perlengkapanByJenis = DB::table('paket_umrah_perlengkapan_t as pupt')
                ->join('perlengkapan_m as pm', 'pm.id', '=', 'pupt.perlengkapan_id')
                ->join('jenis_perlengkapan_m as jpm', 'jpm.id', '=', 'pm.jenis_perlengkapan_id')
                ->leftJoin('icon_m as im', 'im.id', '=', 'jpm.icon_id')
                ->where('pupt.paket_umrah_id', $id)
                ->where('pupt.is_active', true)
                ->select('jpm.id as jenis_id', 'jpm.nama_jenis', 'im.id as icon_id', 'im.nama_icon as icon_nama', 'im.url as icon_url', 'pm.id as perlengkapan_id', 'pm.nama_perlengkapan')
                ->get();

            $perlengkapan = [];
            foreach ($perlengkapanByJenis->groupBy('jenis_id') as $jenisId => $items) {
                $first = $items->first();
                $perlengkapan[] = [
                    'jenis_id' => (int) $jenisId,
                    'nama_jenis' => $first->nama_jenis ?? '',
                    'icon' => [
                        'id' => (int) ($first->icon_id ?? 0),
                        'nama' => $first->icon_nama ?? '',
                        'url' => $first->icon_url ?? '',
                    ],
                    'list' => $items->map(fn ($i) => ['id' => (int) $i->perlengkapan_id, 'nama' => $i->nama_perlengkapan ?? ''])->all(),
                ];
            }

            $keberangkatan = DB::table('paket_umrah_keberangkatan_t')
                ->where('paket_umrah_id', $id)
                ->where('is_active', true)
                ->orderBy('tanggal_berangkat')
                ->get()
                ->map(fn ($k) => [
                    'id' => (int) $k->id,
                    'is_active' => (bool) $k->is_active,
                    'paket_umrah_id' => (int) $k->paket_umrah_id,
                    'tanggal_berangkat' => $k->tanggal_berangkat ?? '',
                    'jam_berangkat' => $k->jam_berangkat ? (string) $k->jam_berangkat : '',
                    'tanggal_pulang' => $k->tanggal_pulang ?? '',
                    'jam_pulang' => $k->jam_pulang ? (string) $k->jam_pulang : '',
                    'created_at' => $k->created_at ?? '',
                    'updated_at' => $k->updated_at ?? '',
                ])
                ->all();

            // Tipe paket / tipe kamar (dipakai di halaman checkout untuk pilih tipe kamar)
            $tipeQuery = DB::table('paket_umrah_tipe_m as put')
                ->join('tipe_kamar_m as tk', 'tk.id', '=', 'put.tipe_kamar_id')
                ->where('put.paket_umrah_id', $id);
            if (! $forAdmin) {
                $tipeQuery->where('put.is_active', true);
            }
            $tipeSelect = ['put.id', 'put.tipe_kamar_id', 'tk.tipe_kamar', 'put.harga_per_pax', 'put.kapasitas_total'];
            if ($forAdmin) {
                $tipeSelect[] = 'put.is_active';
            }
            $tipeRows = $tipeQuery->select($tipeSelect)->orderBy('put.id')->get();

            $tipePaketUmrah = $tipeRows->map(function ($t) use ($forAdmin) {
                $row = [
                    'paket_umrah_tipe_id' => (int) $t->id,
                    'tipe_kamar_id' => (int) $t->tipe_kamar_id,
                    'tipe_kamar' => $t->tipe_kamar ?? '',
                    'harga_per_pax' => $t->harga_per_pax !== null ? (float) $t->harga_per_pax : 0,
                    'kapasitas_total' => $t->kapasitas_total !== null ? (int) $t->kapasitas_total : 0,
                ];
                if ($forAdmin && isset($t->is_active)) {
                    $row['is_active'] = (bool) $t->is_active;
                }
                return $row;
            })->all();

            // Hitung sisa pax: kapasitas_total - jumlah jamaah dari transaksi aktif (tidak dibatalkan/refund)
            $kapasitasTotal = (int) ($pum->jumlah_pax ?? 0);
            $jenisUmrahId = \App\Models\JenisTransaksi::where('kode', 'PAKET_UMRAH')->value('id');
            $terpakai = 0;
            if ($kapasitasTotal > 0 && $jenisUmrahId) {
                $trx = DB::table('transaksi_m as tm')
                    ->leftJoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                    ->where('tm.is_active', true)
                    ->where('tm.jenis_transaksi_id', $jenisUmrahId)
                    ->where('tm.produk_id', $id)
                    // Abaikan transaksi yang dibatalkan atau sedang proses refund
                    ->whereNotIn('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN'])
                    ->get(['tm.jamaah_data']);

                foreach ($trx as $t) {
                    $jd = $t->jamaah_data;
                    if (is_string($jd)) {
                        $arr = json_decode($jd, true);
                    } else {
                        $arr = is_array($jd) ? $jd : [];
                    }
                    if (is_array($arr)) {
                        $terpakai += count($arr);
                    }
                }
            }
            $sisaPax = max(0, $kapasitasTotal - $terpakai);

            $bintangMax = $hotelRows->max('bintang');

            $persen = $this->getMitraDiscountPercent($request);
            $hargaTermurahAsli = $pum->harga_termurah !== null ? (float) $pum->harga_termurah : 0;
            if ($persen > 0) {
                $hargaTermurah = $this->applyMitraDiscount($hargaTermurahAsli, $persen);
                $hargaAsliTermurah = $hargaTermurahAsli;
                $persenPotonganMitra = $persen;
            } else {
                $hargaTermurah = $hargaTermurahAsli;
                $hargaAsliTermurah = null;
                $persenPotonganMitra = 0;
            }

            $data = [
                'id' => (int) $pum->id,
                'nama_paket' => $pum->nama_paket ?? '',
                'foto_paket' => $fotoPaket,
                'destinasi' => $destinasi,
                'deskripsi' => $pum->deskripsi ?? '',
                'musim_id' => (int) ($pum->musim_id ?? 0),
                'musim' => $pum->nama_musim ?? '',
                'nama_musim' => $pum->nama_musim ?? '',
                'bintang' => $bintangMax !== null ? (string) $bintangMax : '0',
                'lokasi_keberangkatan_id' => (int) ($pum->lokasi_keberangkatan_id ?? 0),
                'lokasi_tujuan_id' => (int) ($pum->lokasi_tujuan_id ?? 0),
                'lokasi_keberangkatan' => $pum->lokasi_keberangkatan_nama ?? '',
                'lokasi_tujuan' => $pum->lokasi_tujuan_nama ?? '',
                'durasi_total' => (int) ($pum->durasi_total ?? 0),
                // Untuk admin: jumlah_pax = kapasitas total. Untuk user: sisa pax (kuota tersedia).
                'jumlah_pax' => $forAdmin ? $kapasitasTotal : $sisaPax,
                // Untuk admin: tampilkan terpakai & sisa agar jelas saat mengubah kapasitas.
                ...($forAdmin ? [
                    'jumlah_pax_terpakai' => $terpakai,
                    'jumlah_pax_sisa' => $sisaPax,
                ] : []),
                'harga_termurah' => (string) $hargaTermurah,
                'harga_asli_termurah' => $hargaAsliTermurah,
                'persen_potongan_mitra' => $persenPotonganMitra,
                'hotel' => $hotel,
                'maskapai' => $maskapai,
                'fasilitas_tambahan' => $fasilitasTambahan,
                'perlengkapan' => $perlengkapan,
                'keberangkatan' => $keberangkatan,
                'tipe_paket_umrah' => $tipePaketUmrah,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Detail paket umrah berhasil diambil',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil detail paket: ' . $th->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Daftar tipe paket / tipe kamar untuk satu paket umrah.
     * Route: GET /api/paket-umrah/tipe?paket_umrah_id={id}
     * Dipakai di frontend (useUmrahRoomType) untuk menampilkan pilihan tipe kamar beserta harga per pax.
     */
    public function getTipePaketUmrah(Request $request)
    {
        $paketId = $request->query('paket_umrah_id');

        if (! $paketId) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter paket_umrah_id wajib diisi',
            ], 400);
        }

        try {
            $items = DB::table('paket_umrah_tipe_m as put')
                ->join('tipe_kamar_m as tk', 'put.tipe_kamar_id', '=', 'tk.id')
                ->select(
                    'put.id',
                    'put.harga_per_pax',
                    'put.kapasitas_total',
                    'put.is_active',
                    'tk.id as hotel_kamar_id',
                    'tk.tipe_kamar',
                    'tk.kapasitas'
                )
                ->where('put.paket_umrah_id', $paketId)
                ->where('put.is_active', true)
                ->orderBy('put.created_at', 'asc')
                ->get();

            $persen = $this->getMitraDiscountPercent($request);
            $result = $items->map(function ($row) use ($persen) {
                $harga = (float) $row->harga_per_pax;
                $hargaMitra = $this->applyMitraDiscount($harga, $persen);
                $out = [
                    'id' => $row->id,
                    'harga_per_pax' => $hargaMitra,
                    'kapasitas_total' => $row->kapasitas_total,
                    'is_active' => (bool) $row->is_active,
                    'hotel_kamar' => [
                        'id' => $row->hotel_kamar_id,
                        'tipe_kamar' => $row->tipe_kamar . ' Room',
                        'kapasitas' => $row->kapasitas,
                    ],
                ];
                if ($persen > 0) {
                    $out['harga_asli_per_pax'] = $harga;
                    $out['persen_potongan_mitra'] = $persen;
                }
                return $out;
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Daftar tipe paket umrah berhasil diambil',
                'data' => $result,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar review paket umrah untuk tampilan di halaman detail.
     * Route: GET /api/paket-umrah/review?paket_umrah_id={id}
     */
    public function getReview(Request $request)
    {
        $paketId = $request->query('paket_umrah_id');

        if (! $paketId) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter paket_umrah_id wajib diisi',
            ], 400);
        }

        try {
            $reviews = PaketUmrahReview::query()
                ->where('paket_umrah_id', $paketId)
                ->where('is_active', true)
                ->with('user:id,nama_lengkap')
                ->orderBy('created_at', 'desc')
                ->get();

            $total = $reviews->count();
            $ratingAvg = $total > 0
                ? round($reviews->avg('rating'), 1)
                : 0;

            $list = $reviews->map(function (PaketUmrahReview $r) {
                return [
                    'id' => $r->id,
                    'user_name' => $r->user?->nama_lengkap ?? 'Anonim',
                    'rating' => (int) $r->rating,
                    'komentar' => $r->komentar ?? '',
                    'created_at' => $r->created_at ? $r->created_at->format('c') : '',
                ];
            })->values()->all();

            return response()->json([
                'status' => true,
                'message' => 'Review paket umrah berhasil diambil',
                'data' => [
                    'rating_avg' => $ratingAvg,
                    'total' => $total,
                    'reviews' => $list,
                ],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview tipe paket umrah (keberangkatan, maskapai, hotel, fasilitas, perlengkapan) untuk halaman checkout.
     * Route: GET /api/paket-umrah/preview
     *
     * Query:
     * - paket_umrah_id (required)
     * - paket_umrah_tipe_id (required)
     * - tanggal_keberangkatan_id (required)
     */
    public function previewTipePaketUmrah(Request $request)
    {
        $tanggalKeberangkatanId = $request->query('tanggal_keberangkatan_id');
        $paketId = $request->query('paket_umrah_id');
        $tipeId = $request->query('paket_umrah_tipe_id');

        if (! $paketId || ! $tipeId) {
            return response()->json([
                'status' => false,
                'message' => 'Parameter paket_umrah_id dan paket_umrah_tipe_id wajib diisi',
            ], 400);
        }

        try {
            $keberangkatan = DB::table('paket_umrah_keberangkatan_t')
                ->where('id', $tanggalKeberangkatanId)
                ->where('paket_umrah_id', $paketId)
                ->where('is_active', true)
                ->orderBy('tanggal_berangkat', 'asc')
                ->first();

            $maskapai = DB::table('paket_umrah_maskapai_t as pum')
                ->join('maskapai_m as mm', 'pum.maskapai_id', '=', 'mm.id')
                ->join('kelas_penerbangan_m as kpm', 'pum.kelas_penerbangan_id', '=', 'kpm.id')
                ->select(
                    'mm.id as id',
                    'mm.nama_maskapai',
                    'kpm.kelas_penerbangan',
                    'pum.kelas_penerbangan_id'
                )
                ->where('pum.paket_umrah_id', $paketId)
                ->where('pum.is_active', true)
                ->get();

            $otherHotels = DB::table('paket_umrah_hotel_t as puh')
                ->join('hotel_m as hm', 'puh.hotel_id', '=', 'hm.id')
                ->join('kota_m as km', 'hm.kota_id', '=', 'km.id')
                ->select(
                    'hm.id as hotel_id',
                    'hm.nama_hotel',
                    'hm.bintang',
                    'km.nama_kota',
                    'puh.keterangan'
                )
                ->where('puh.paket_umrah_id', $paketId)
                ->where('puh.is_active', true)
                ->orderBy('hm.bintang', 'desc')
                ->get();

            $fasilitas = DB::table('paket_umrah_fasilitas_t as puf')
                ->join('fasilitas_m as f', 'puf.fasilitas_id', '=', 'f.id')
                ->join('jenis_fasilitas_m as jf', 'f.jenis_fasilitas_id', '=', 'jf.id')
                ->leftJoin('icon_m as i', 'jf.icon_id', '=', 'i.id')
                ->where('puf.paket_umrah_id', $paketId)
                ->select(
                    'jf.id as jenis_id',
                    'jf.nama_jenis',
                    'i.id as icon_id',
                    'i.nama_icon',
                    'i.url as icon_url',
                    'f.id as fasilitas_id',
                    'f.nama_fasilitas'
                )
                ->get()
                ->groupBy('jenis_id')
                ->map(function ($group) {
                    $first = $group->first();

                    return [
                        'jenis_id' => $first->jenis_id,
                        'nama_jenis' => $first->nama_jenis,
                        'icon' => [
                            'id' => $first->icon_id,
                            'nama' => $first->nama_icon,
                            'url' => $first->icon_url ? asset(ltrim($first->icon_url, '/')) : null,
                        ],
                        'list' => $group->map(fn ($f) => [
                            'id' => $f->fasilitas_id,
                            'nama' => $f->nama_fasilitas,
                        ])->values(),
                    ];
                })->values();

            $perlengkapan = DB::table('paket_umrah_perlengkapan_t as pup')
                ->join('perlengkapan_m as p', 'pup.perlengkapan_id', '=', 'p.id')
                ->join('jenis_perlengkapan_m as jp', 'p.jenis_perlengkapan_id', '=', 'jp.id')
                ->leftJoin('icon_m as i', 'jp.icon_id', '=', 'i.id')
                ->where('pup.paket_umrah_id', $paketId)
                ->select(
                    'jp.id as jenis_id',
                    'jp.nama_jenis',
                    'i.id as icon_id',
                    'i.nama_icon',
                    'i.url as icon_url',
                    'p.id as perlengkapan_id',
                    'p.nama_perlengkapan'
                )
                ->get()
                ->groupBy('jenis_id')
                ->map(function ($group) {
                    $first = $group->first();

                    return [
                        'jenis_id' => $first->jenis_id,
                        'nama_jenis' => $first->nama_jenis,
                        'icon' => [
                            'id' => $first->icon_id,
                            'nama' => $first->nama_icon,
                            'url' => $first->icon_url ? asset(ltrim($first->icon_url, '/')) : null,
                        ],
                        'list' => $group->map(fn ($p) => [
                            'id' => $p->perlengkapan_id,
                            'nama' => $p->nama_perlengkapan,
                        ])->values(),
                    ];
                })->values();

            $tipe = DB::table('paket_umrah_tipe_m')
                ->where('id', $tipeId)
                ->where('paket_umrah_id', $paketId)
                ->first();

            if (! $tipe) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tipe paket tidak ditemukan untuk paket ini',
                ], 404);
            }

            $persen = $this->getMitraDiscountPercent($request);
            $hargaPerPax = (float) $tipe->harga_per_pax;
            $hargaPerPaxMitra = $this->applyMitraDiscount($hargaPerPax, $persen);

            $tipeData = [
                'id' => $tipe->id,
                'harga_per_pax' => $hargaPerPaxMitra,
                'kapasitas_total' => $tipe->kapasitas_total,
            ];

            if ($persen > 0) {
                $tipeData['harga_asli_per_pax'] = $hargaPerPax;
                $tipeData['persen_potongan_mitra'] = $persen;
            }

            $data = [
                'keberangkatan' => $keberangkatan,
                'maskapai' => $maskapai,
                'hotels' => $otherHotels,
                'fasilitas' => $fasilitas,
                'perlengkapan' => $perlengkapan,
                'tipe' => $tipeData,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Preview tipe paket umrah',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail paket umrah untuk ADMIN by path param.
     * Route: GET /api/sistem-admin/paket-umrah/get-paket-umrah/{id}
     *
     * Reuse logic getPaketUmrah supaya struktur respons konsisten dengan frontend user.
     */
    public function getPaketUmrahId(Request $request, int $id)
    {
        // Set query parameter agar getPaketUmrah bisa dipakai ulang tanpa duplikasi logika.
        // for_admin: agar response include id foto, kota_id destinasi, kapasitas, is_active tipe, dan bisa edit paket non-aktif.
        $request->merge(['paket_umrah_id' => $id, 'for_admin' => true]);

        return $this->getPaketUmrah($request);
    }

    /**
     * Pesan paket umrah (dengan pembayaran). Membuat transaksi dan mengembalikan data order.
     * Validasi disesuaikan dengan frontend: provinsi/kota/kecamatan/alamat boleh kosong (checkout mitra).
     */
    public function pesanPaketUmrah(Request $request)
    {
        // Normalisasi: 0 atau kosong untuk lokasi jadi null (checkout mitra sering tanpa alamat)
        $pv = (int) $request->input('provinsi_id');
        $kt = (int) $request->input('kota_id');
        $kc = (int) $request->input('kecamatan_id');
        $request->merge([
            'provinsi_id' => $pv ?: null,
            'kota_id' => $kt ?: null,
            'kecamatan_id' => $kc ?: null,
        ]);

        $request->validate([
            'gelar_id' => 'nullable|integer|exists:gelar_m,id',
            // Untuk checkout mitra, nama_lengkap dan no_whatsapp boleh kosong
            // karena diambil dari data akun; gunakan required_without agar tetap wajib
            // jika bukan dibuat_sebagai_mitra.
            'nama_lengkap' => 'required_without:dibuat_sebagai_mitra|string|max:255',
            'no_whatsapp' => 'required_without:dibuat_sebagai_mitra|string|max:20',
            'provinsi_id' => 'nullable|integer|exists:provinsi_m,id',
            'kota_id' => 'nullable|integer|exists:kota_m,id',
            'kecamatan_id' => 'nullable|integer|exists:kecamatan_m,id',
            'alamat_lengkap' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'produk_id' => 'required|integer|exists:paket_umrah_m,id',
            'keberangkatan_id' => 'required|integer|exists:paket_umrah_keberangkatan_t,id',
            'paket_umrah_tipe_id' => 'required|integer|exists:paket_umrah_tipe_m,id',
            'jumlah_bayar' => 'nullable|integer|min:1',
            'dibuat_sebagai_mitra' => 'nullable',
            'jamaah_data' => 'required|array|min:1',
            'jamaah_data.*.id' => 'nullable',
            'jamaah_data.*.nama' => 'required|string|max:255',
            'jamaah_data.*.nik' => 'nullable|string|max:32',
            'jamaah_data.*.no_paspor' => 'nullable|string|max:50',
            'jamaah_data.*.foto_ktp' => 'nullable|file|image|max:2048',
            'jamaah_data.*.foto_paspor' => 'nullable|file|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $userId = auth()->id();
            if (! $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized. Silakan login kembali.',
                ], 401);
            }

            $produkId = (int) $request->produk_id;
            $keberangkatanId = (int) $request->keberangkatan_id;
            $tipeId = (int) $request->paket_umrah_tipe_id;

            $paket = PaketUmrah::where('id', $produkId)->where('is_active', true)->first();
            if (! $paket) {
                return response()->json([
                    'status' => false,
                    'message' => 'Paket umrah tidak ditemukan atau tidak aktif.',
                ], 404);
            }

            $tipeData = DB::table('paket_umrah_tipe_m')
                ->where('id', $tipeId)
                ->where('paket_umrah_id', $produkId)
                ->where('is_active', true)
                ->first();

            if (! $tipeData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tipe kamar tidak ditemukan atau tidak aktif.',
                ], 404);
            }

            $keberangkatan = DB::table('paket_umrah_keberangkatan_t')
                ->where('id', $keberangkatanId)
                ->where('paket_umrah_id', $produkId)
                ->where('is_active', true)
                ->first();

            if (! $keberangkatan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jadwal keberangkatan tidak ditemukan atau tidak aktif.',
                ], 404);
            }

            $jamaahData = [];
            foreach ($request->jamaah_data as $i => $j) {
                $item = [
                    'id' => $j['id'] ?? null,
                    'nama' => $j['nama'] ?? '',
                    'nik' => $j['nik'] ?? '',
                    'no_paspor' => $j['no_paspor'] ?? '',
                ];
                if (isset($j['foto_ktp']) && $j['foto_ktp'] instanceof \Illuminate\Http\UploadedFile) {
                    $item['foto_ktp_path'] = $j['foto_ktp']->store('private/ktp', 'local');
                }
                if (isset($j['foto_paspor']) && $j['foto_paspor'] instanceof \Illuminate\Http\UploadedFile) {
                    $item['foto_paspor_path'] = $j['foto_paspor']->store('private/paspor', 'local');
                }
                $jamaahData[] = $item;
            }

            $jumlahJamaah = count($jamaahData);
            $hargaPerPax = (float) $tipeData->harga_per_pax;
            $totalBiaya = $hargaPerPax * $jumlahJamaah;

            $snapshot = [
                'id' => $paket->id,
                'nama_paket' => $paket->nama_paket,
                'musim_id' => $paket->musim_id,
                'paket_umrah_tipe_id' => $tipeId,
                'harga_per_pax' => $hargaPerPax,
                'durasi_total' => $paket->durasi_total ?? null,
                'keberangkatan' => [
                    'id' => $keberangkatan->id,
                    'tanggal_berangkat' => $keberangkatan->tanggal_berangkat ?? null,
                    'tanggal_pulang' => $keberangkatan->tanggal_pulang ?? null,
                    'jam_berangkat' => $keberangkatan->jam_berangkat ?? null,
                    'jam_pulang' => $keberangkatan->jam_pulang ?? null,
                ],
            ];

            $jenisTransaksi = JenisTransaksi::where('kode', 'PAKET_UMRAH')->firstOrFail();
            $tanggal = date('dmy');
            $lastCount = Transaksi::where('jenis_transaksi_id', $jenisTransaksi->id)
                ->whereDate('created_at', today())
                ->count();
            $autoIncrement = str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);
            $kodeTransaksi = $jenisTransaksi->kode . '-' . $tanggal . '-' . $userId . '-' . $autoIncrement;

            $transaksi = Transaksi::create([
                'is_active' => true,
                'akun_id' => $userId,
                'dibuat_sebagai_mitra' => (bool) $request->input('dibuat_sebagai_mitra', false),
                'gelar_id' => $request->gelar_id ? (int) $request->gelar_id : null,
                'nama_lengkap' => $request->nama_lengkap,
                'no_whatsapp' => $request->no_whatsapp,
                'provinsi_id' => $request->provinsi_id ? (int) $request->provinsi_id : null,
                'kota_id' => $request->kota_id ? (int) $request->kota_id : null,
                'kecamatan_id' => $request->kecamatan_id ? (int) $request->kecamatan_id : null,
                'alamat_lengkap' => $request->alamat_lengkap ?: null,
                'deskripsi' => $request->deskripsi ?: null,
                'jenis_transaksi_id' => $jenisTransaksi->id,
                'produk_id' => $produkId,
                'keberangkatan_id' => $keberangkatanId,
                'snapshot_produk' => $snapshot,
                'jamaah_data' => $jamaahData,
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => true,
                'total_biaya' => $totalBiaya,
                'status_pembayaran_id' => StatusPembayaran::where('kode', 'MENUNGGU_PEMBAYARAN')->value('id'),
                'status_transaksi_id' => StatusTransaksi::where('kode', 'BELUM_DIHUBUNGI')->value('id'),
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pemesanan paket umrah berhasil dibuat.',
                'data' => [
                    'transaksi' => $transaksi,
                    'kode_transaksi' => $kodeTransaksi,
                    'total_biaya' => $totalBiaya,
                    'jumlah_jamaah' => $jumlahJamaah,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat pemesanan: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Membuat master Paket Umrah (dipakai di admin saat tombol "Simpan" pada form paket umrah).
     * Route: POST /api/sistem-admin/paket-umrah/create-paket-umrah
     *
     * Catatan:
     * - Upload / update foto paket ditangani terpisah oleh endpoint upsert-foto.
     * - Di sini kita fokus menyimpan record utama paket + relasi dasar (destinasi, maskapai, fasilitas, perlengkapan, keberangkatan, tipe paket).
     */
    public function createPaketUmrah(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'musim.value' => 'required|integer|exists:musim_m,id',
            'lokasi_keberangkatan.value' => 'required|integer|exists:kota_m,id',
            'lokasi_tujuan.value' => 'required|integer|exists:kota_m,id',
            'durasi_total' => 'required|integer|min:0',
            'jumlah_pax' => 'required|integer|min:0',
            'destinasi_hotel' => 'array',
            'maskapai' => 'array',
            'fasilitas_tambahan' => 'array',
            'perlengkapan' => 'array',
            'keberangkatan' => 'array',
            'keberangkatan.*.tanggal_berangkat' => 'required|date',
            'keberangkatan.*.tanggal_pulang' => 'required|date',
            'keberangkatan.*.jam_berangkat' => 'nullable',
            'keberangkatan.*.jam_pulang' => 'nullable',
            'tipe_paket_umrah' => 'array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $tipeList = $request->input('tipe_paket_umrah', []);
            $hargaList = [];
            foreach ($tipeList as $tipe) {
                if (isset($tipe['harga_per_pax']) && is_numeric($tipe['harga_per_pax'])) {
                    $hargaList[] = (float) $tipe['harga_per_pax'];
                }
            }
            $hargaTermurah = ! empty($hargaList) ? min($hargaList) : 0;
            $hargaTermahal = ! empty($hargaList) ? max($hargaList) : 0;

            $paketId = DB::table('paket_umrah_m')->insertGetId([
                'nama_paket' => $request->nama_paket,
                'deskripsi' => $request->deskripsi,
                'musim_id' => (int) data_get($request->musim, 'value'),
                'lokasi_keberangkatan_id' => (int) data_get($request->lokasi_keberangkatan, 'value'),
                'lokasi_tujuan_id' => (int) data_get($request->lokasi_tujuan, 'value'),
                'durasi_total' => (int) $request->durasi_total,
                'jumlah_pax' => (int) $request->jumlah_pax,
                'harga_termurah' => $hargaTermurah,
                'harga_termahal' => $hargaTermahal,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $destinasiHotel = $request->input('destinasi_hotel', []);
            foreach ($destinasiHotel as $row) {
                $kotaId = (int) data_get($row, 'nama_kota.value');
                if (! $kotaId) {
                    continue;
                }
                DB::table('paket_umrah_destinasi_t')->insert([
                    'paket_umrah_id' => $paketId,
                    'kota_id' => $kotaId,
                    'durasi' => (int) ($row['durasi'] ?? 0),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $hotelId = (int) data_get($row, 'nama_hotel.value');
                if ($hotelId) {
                    $hotelKotaId = DB::table('hotel_m')->where('id', $hotelId)->value('kota_id') ?: $kotaId;
                    DB::table('paket_umrah_hotel_t')->insert([
                        'paket_umrah_id' => $paketId,
                        'hotel_id' => $hotelId,
                        'kota_id' => $hotelKotaId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $maskapaiList = $request->input('maskapai', []);
            foreach ($maskapaiList as $row) {
                $maskapaiId = (int) data_get($row, 'nama_maskapai.value');
                if (! $maskapaiId) {
                    continue;
                }
                $kelasPenerbanganId = $row['kelas_penerbangan_id'] ?? null;
                if (! $kelasPenerbanganId) {
                    $kelasPenerbanganId = DB::table('kelas_penerbangan_m')->where('is_active', true)->value('id');
                }
                if (! $kelasPenerbanganId) {
                    $kelasPenerbanganId = 1;
                }
                DB::table('paket_umrah_maskapai_t')->insert([
                    'paket_umrah_id' => $paketId,
                    'maskapai_id' => $maskapaiId,
                    'kelas_penerbangan_id' => (int) $kelasPenerbanganId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $fasilitas = $request->input('fasilitas_tambahan', []);
            foreach ($fasilitas as $jenis) {
                foreach ($jenis['list'] ?? [] as $item) {
                    $fid = $item['value'] ?? $item['id'] ?? null;
                    if (! $fid) {
                        continue;
                    }
                    DB::table('paket_umrah_fasilitas_t')->insert([
                        'paket_umrah_id' => $paketId,
                        'fasilitas_id' => (int) $fid,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $perlengkapan = $request->input('perlengkapan', []);
            foreach ($perlengkapan as $jenis) {
                foreach ($jenis['list'] ?? [] as $item) {
                    $pid = $item['value'] ?? $item['id'] ?? null;
                    if (! $pid) {
                        continue;
                    }
                    DB::table('paket_umrah_perlengkapan_t')->insert([
                        'paket_umrah_id' => $paketId,
                        'perlengkapan_id' => (int) $pid,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $keberangkatanList = $request->input('keberangkatan', []);
            foreach ($keberangkatanList as $row) {
                DB::table('paket_umrah_keberangkatan_t')->insert([
                    'paket_umrah_id' => $paketId,
                    'tanggal_berangkat' => $row['tanggal_berangkat'] ?? null,
                    'jam_berangkat' => $row['jam_berangkat'] ?? null,
                    'tanggal_pulang' => $row['tanggal_pulang'] ?? null,
                    'jam_pulang' => $row['jam_pulang'] ?? null,
                    'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($tipeList as $row) {
                $tipeId = (int) data_get($row, 'tipe_kamar.value');
                DB::table('paket_umrah_tipe_m')->insert([
                    'paket_umrah_id' => $paketId,
                    'tipe_kamar_id' => $tipeId ?: null,
                    'harga_per_pax' => $row['harga_per_pax'] ?? 0,
                    'kapasitas_total' => $row['kapasitas_total'] ?? 0,
                    'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Paket umrah berhasil dibuat',
                'data' => ['id' => $paketId],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat paket umrah: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update master Paket Umrah (dipakai di admin saat edit lewat /paket/umrah/form/:id).
     * Route: PUT /api/sistem-admin/paket-umrah/update-paket-umrah/{id}
     *
     * Catatan:
     * - Foto tetap dikelola oleh endpoint upsert-foto; di sini hanya update data utama + relasi-relasi.
     * - Strategi sederhana: hapus relasi lama (destinasi, hotel, maskapai, fasilitas, perlengkapan, keberangkatan, tipe)
     *   lalu insert ulang dari payload terbaru.
     */
    public function updatePaketUmrah(Request $request, int $id)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'musim.value' => 'required|integer|exists:musim_m,id',
            'lokasi_keberangkatan.value' => 'required|integer|exists:kota_m,id',
            'lokasi_tujuan.value' => 'required|integer|exists:kota_m,id',
            'durasi_total' => 'required|integer|min:0',
            'jumlah_pax' => 'required|integer|min:0',
            'destinasi_hotel' => 'array',
            'maskapai' => 'array',
            'fasilitas_tambahan' => 'array',
            'perlengkapan' => 'array',
            'keberangkatan' => 'array',
            'keberangkatan.*.tanggal_berangkat' => 'required|date',
            'keberangkatan.*.tanggal_pulang' => 'required|date',
            'keberangkatan.*.jam_berangkat' => 'nullable',
            'keberangkatan.*.jam_pulang' => 'nullable',
            'tipe_paket_umrah' => 'array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $paket = DB::table('paket_umrah_m')->where('id', $id)->first();
            if (! $paket) {
                return response()->json([
                    'status' => false,
                    'message' => 'Paket umrah tidak ditemukan.',
                ], 404);
            }

            // Kapasitas tidak boleh kurang dari jamaah yang sudah terdaftar (terpakai)
            $jenisUmrahId = \App\Models\JenisTransaksi::where('kode', 'PAKET_UMRAH')->value('id');
            $terpakai = 0;
            if ($jenisUmrahId) {
                $trx = DB::table('transaksi_m as tm')
                    ->leftJoin('status_transaksi_m as stm', 'stm.id', '=', 'tm.status_transaksi_id')
                    ->where('tm.is_active', true)
                    ->where('tm.jenis_transaksi_id', $jenisUmrahId)
                    ->where('tm.produk_id', $id)
                    ->whereNotIn('stm.kode', ['DIBATALKAN', 'REFUND_DIAJUKAN'])
                    ->get(['tm.jamaah_data']);
                foreach ($trx as $t) {
                    $jd = $t->jamaah_data;
                    $arr = is_string($jd) ? json_decode($jd, true) : (is_array($jd) ? $jd : []);
                    if (is_array($arr)) {
                        $terpakai += count($arr);
                    }
                }
            }
            $kapasitasBaru = (int) $request->jumlah_pax;
            if ($kapasitasBaru < $terpakai) {
                return response()->json([
                    'status' => false,
                    'message' => "Kapasitas (jumlah pax) tidak boleh kurang dari jamaah yang sudah terdaftar. Terpakai: {$terpakai} jamaah. Isi kapasitas minimal {$terpakai} atau lebih (mis. 50 agar sisa kuota " . (50 - $terpakai) . ').',
                ], 422);
            }

            $tipeList = $request->input('tipe_paket_umrah', []);
            $hargaList = [];
            foreach ($tipeList as $tipe) {
                if (isset($tipe['harga_per_pax']) && is_numeric($tipe['harga_per_pax'])) {
                    $hargaList[] = (float) $tipe['harga_per_pax'];
                }
            }
            $hargaTermurah = ! empty($hargaList) ? min($hargaList) : 0;
            $hargaTermahal = ! empty($hargaList) ? max($hargaList) : 0;

            DB::table('paket_umrah_m')
                ->where('id', $id)
                ->update([
                    'nama_paket' => $request->nama_paket,
                    'deskripsi' => $request->deskripsi,
                    'musim_id' => (int) data_get($request->musim, 'value'),
                    'lokasi_keberangkatan_id' => (int) data_get($request->lokasi_keberangkatan, 'value'),
                    'lokasi_tujuan_id' => (int) data_get($request->lokasi_tujuan, 'value'),
                    'durasi_total' => (int) $request->durasi_total,
                    'jumlah_pax' => (int) $request->jumlah_pax,
                    'harga_termurah' => $hargaTermurah,
                    'harga_termahal' => $hargaTermahal,
                    'updated_at' => now(),
                ]);

            // Hapus relasi lama
            DB::table('paket_umrah_destinasi_t')->where('paket_umrah_id', $id)->delete();
            DB::table('paket_umrah_hotel_t')->where('paket_umrah_id', $id)->delete();
            DB::table('paket_umrah_maskapai_t')->where('paket_umrah_id', $id)->delete();
            DB::table('paket_umrah_fasilitas_t')->where('paket_umrah_id', $id)->delete();
            DB::table('paket_umrah_perlengkapan_t')->where('paket_umrah_id', $id)->delete();
            DB::table('paket_umrah_keberangkatan_t')->where('paket_umrah_id', $id)->delete();
            DB::table('paket_umrah_tipe_m')->where('paket_umrah_id', $id)->delete();

            // Insert relasi baru (logika sama seperti createPaketUmrah)
            $destinasiHotel = $request->input('destinasi_hotel', []);
            foreach ($destinasiHotel as $row) {
                $kotaId = (int) data_get($row, 'nama_kota.value');
                if (! $kotaId) {
                    continue;
                }
                DB::table('paket_umrah_destinasi_t')->insert([
                    'paket_umrah_id' => $id,
                    'kota_id' => $kotaId,
                    'durasi' => (int) ($row['durasi'] ?? 0),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $hotelId = (int) data_get($row, 'nama_hotel.value');
                if ($hotelId) {
                    $hotelKotaId = DB::table('hotel_m')->where('id', $hotelId)->value('kota_id') ?: $kotaId;
                    DB::table('paket_umrah_hotel_t')->insert([
                        'paket_umrah_id' => $id,
                        'hotel_id' => $hotelId,
                        'kota_id' => $hotelKotaId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $maskapaiList = $request->input('maskapai', []);
            foreach ($maskapaiList as $row) {
                $maskapaiId = (int) data_get($row, 'nama_maskapai.value');
                if (! $maskapaiId) {
                    continue;
                }
                $kelasPenerbanganId = $row['kelas_penerbangan_id'] ?? null;
                if (! $kelasPenerbanganId) {
                    $kelasPenerbanganId = DB::table('kelas_penerbangan_m')->where('is_active', true)->value('id');
                }
                if (! $kelasPenerbanganId) {
                    $kelasPenerbanganId = 1;
                }
                DB::table('paket_umrah_maskapai_t')->insert([
                    'paket_umrah_id' => $id,
                    'maskapai_id' => $maskapaiId,
                    'kelas_penerbangan_id' => (int) $kelasPenerbanganId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $fasilitas = $request->input('fasilitas_tambahan', []);
            foreach ($fasilitas as $jenis) {
                foreach ($jenis['list'] ?? [] as $item) {
                    $fid = $item['value'] ?? $item['id'] ?? null;
                    if (! $fid) {
                        continue;
                    }
                    DB::table('paket_umrah_fasilitas_t')->insert([
                        'paket_umrah_id' => $id,
                        'fasilitas_id' => (int) $fid,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $perlengkapan = $request->input('perlengkapan', []);
            foreach ($perlengkapan as $jenis) {
                foreach ($jenis['list'] ?? [] as $item) {
                    $pid = $item['value'] ?? $item['id'] ?? null;
                    if (! $pid) {
                        continue;
                    }
                    DB::table('paket_umrah_perlengkapan_t')->insert([
                        'paket_umrah_id' => $id,
                        'perlengkapan_id' => (int) $pid,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $keberangkatanList = $request->input('keberangkatan', []);
            foreach ($keberangkatanList as $row) {
                DB::table('paket_umrah_keberangkatan_t')->insert([
                    'paket_umrah_id' => $id,
                    'tanggal_berangkat' => $row['tanggal_berangkat'] ?? null,
                    'jam_berangkat' => $row['jam_berangkat'] ?? null,
                    'tanggal_pulang' => $row['tanggal_pulang'] ?? null,
                    'jam_pulang' => $row['jam_pulang'] ?? null,
                    'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($tipeList as $row) {
                $tipeId = (int) data_get($row, 'tipe_kamar.value');
                DB::table('paket_umrah_tipe_m')->insert([
                    'paket_umrah_id' => $id,
                    'tipe_kamar_id' => $tipeId ?: null,
                    'harga_per_pax' => $row['harga_per_pax'] ?? 0,
                    'kapasitas_total' => $row['kapasitas_total'] ?? 0,
                    'is_active' => array_key_exists('is_active', $row) ? (bool) $row['is_active'] : true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Paket umrah berhasil diperbarui',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui paket umrah: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload / update foto paket umrah.
     * Route: POST /api/sistem-admin/paket-umrah/upsert-foto
     *
     * Input:
     * - paket_umrah_id (required, int)
     * - urutan (required, int)
     * - file (required, uploaded file)
     * - foto_id (optional, jika ingin update baris lama)
     */
    public function upsertFotoPaket(Request $request)
    {
        $request->validate([
            'paket_umrah_id' => 'required|integer|exists:paket_umrah_m,id',
            'urutan' => 'required|integer|min:1',
            'file' => 'required|file|image|max:2048',
            'foto_id' => 'nullable|integer|exists:paket_umrah_foto_m,id',
        ]);

        try {
            $paketId = (int) $request->paket_umrah_id;
            $urutan = (int) $request->urutan;
            $file = $request->file('file');

            $path = $file->store('foto_paket', 'public');

            if ($request->filled('foto_id')) {
                DB::table('paket_umrah_foto_m')
                    ->where('id', (int) $request->foto_id)
                    ->update([
                        'paket_umrah_id' => $paketId,
                        'url_foto' => $path,
                        'urutan' => $urutan,
                        'updated_at' => now(),
                    ]);
                $id = (int) $request->foto_id;
            } else {
                $id = DB::table('paket_umrah_foto_m')->insertGetId([
                    'paket_umrah_id' => $paketId,
                    'url_foto' => $path,
                    'urutan' => $urutan,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Foto paket berhasil disimpan',
                'data' => [
                    'id' => $id,
                    'paket_umrah_id' => $paketId,
                    'url_foto' => $path,
                    'urutan' => $urutan,
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan foto paket: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus paket umrah (beserta relasi-relasinya via foreign key cascade).
     * Route: DELETE /api/sistem-admin/paket-umrah/delete-paket-umrah/{id}
     */
    public function deletePaketUmrah(int $id)
    {
        try {
            $deleted = DB::table('paket_umrah_m')->where('id', $id)->delete();

            if (! $deleted) {
                return response()->json([
                    'status' => false,
                    'message' => 'Paket umrah tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Paket umrah berhasil dihapus.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus paket umrah: ' . $th->getMessage(),
            ], 500);
        }
    }
}
