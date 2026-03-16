<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\LaUmrahHotel;
use App\Models\LaUmrahMaskapai;
use App\Models\Maskapai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pengaturan opsi untuk paket kostumisasi LA Umrah (hotel & penerbangan).
 * Data hotel bersumber dari Master Hotel (hotel_m). Daftar hotel yang dipilih di sini dipakai untuk:
 * - isi paket kostumisasi (mitra pilih hotel Mekkah/Madinah)
 * - Komponen Hotel (mitra pesan hotel tanpa paket; API list: listHotelsPublic, detail: showHotelPublic).
 */
class LaUmrahOptionController extends Controller
{
    /**
     * Admin: daftar hotel yang dipilih untuk LA Umrah (dengan detail hotel).
     */
    public function indexHotels(Request $request)
    {
        $items = LaUmrahHotel::with('hotel.kota')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $items,
        ]);
    }

    /**
     * Admin: daftar maskapai yang dipilih untuk LA Umrah (dengan detail maskapai).
     */
    public function indexMaskapai(Request $request)
    {
        $items = LaUmrahMaskapai::with('maskapai.kelasPenerbangan')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $items,
        ]);
    }

    /**
     * Admin: daftar semua hotel (Master Hotel) untuk dropdown pilih hotel.
     */
    public function listMasterHotels(Request $request)
    {
        $query = Hotel::with('kota')->where('is_active', true)->orderBy('nama_hotel');
        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where('nama_hotel', 'like', $q);
        }
        $items = $query->limit(200)->get(['id', 'nama_hotel', 'kota_id', 'jarak_ke_masjid', 'bintang']);
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $items]);
    }

    /**
     * Admin: daftar semua maskapai (Master Maskapai) untuk dropdown.
     */
    public function listMasterMaskapai(Request $request)
    {
        $query = Maskapai::with('kelasPenerbangan')->where('is_active', true)->orderBy('nama_maskapai');
        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where('nama_maskapai', 'like', $q)->orWhere('kode_iata', 'like', $q);
        }
        $items = $query->limit(200)->get();
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $items]);
    }

    /**
     * Admin: tambah hotel ke LA Umrah.
     */
    public function storeHotel(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotel_m,id',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $exists = LaUmrahHotel::where('hotel_id', $request->hotel_id)->exists();
        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Hotel ini sudah ada di daftar LA Umrah.',
            ], 422);
        }

        $maxUrutan = (int) LaUmrahHotel::max('urutan');
        $item = LaUmrahHotel::create([
            'hotel_id' => $request->hotel_id,
            'urutan' => $request->urutan ?? $maxUrutan + 1,
            'is_active' => true,
        ]);
        $item->load('hotel.kota');

        return response()->json([
            'status' => true,
            'message' => 'Hotel berhasil ditambahkan ke LA Umrah',
            'data' => $item,
        ], 201);
    }

    /**
     * Admin: tambah maskapai ke LA Umrah.
     */
    public function storeMaskapai(Request $request)
    {
        $request->validate([
            'maskapai_id' => 'required|exists:maskapai_m,id',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $exists = LaUmrahMaskapai::where('maskapai_id', $request->maskapai_id)->exists();
        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Maskapai ini sudah ada di daftar LA Umrah.',
            ], 422);
        }

        $maxUrutan = (int) LaUmrahMaskapai::max('urutan');
        $item = LaUmrahMaskapai::create([
            'maskapai_id' => $request->maskapai_id,
            'urutan' => $request->urutan ?? $maxUrutan + 1,
            'is_active' => true,
        ]);
        $item->load('maskapai.kelasPenerbangan');

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil ditambahkan ke LA Umrah',
            'data' => $item,
        ], 201);
    }

    /**
     * Admin: hapus hotel dari LA Umrah.
     */
    public function destroyHotel($id)
    {
        $item = LaUmrahHotel::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        $item->delete();
        return response()->json(['status' => true, 'message' => 'Hotel dihapus dari LA Umrah']);
    }

    /**
     * Admin: hapus maskapai dari LA Umrah.
     */
    public function destroyMaskapai($id)
    {
        $item = LaUmrahMaskapai::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        $item->delete();
        return response()->json(['status' => true, 'message' => 'Maskapai dihapus dari LA Umrah']);
    }

    /**
     * Public: daftar hotel untuk isi paket kostumisasi dan Komponen Hotel. Data dari Master Hotel (yang dipilih di Master LA Umrah).
     */
    public function listHotelsPublic()
    {
        $items = LaUmrahHotel::with(['hotel' => function ($q) {
            $q->with([
                'kota',
                'foto' => function ($f) {
                    $f->orderBy('urutan')->limit(5);
                },
            ]);
        }])
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $baseUrl = rtrim(config('app.url'), '/');
        $list = $items->map(function ($row) use ($baseUrl) {
            $h = $row->hotel;
            if (! $h) {
                return null;
            }
            // Ambil harga termurah dari kamar aktif (dipakai sebagai harga hotel)
            $minHarga = $h->kamar()
                ->where('is_active', true)
                ->min('harga_per_malam');
            // Jika belum ada kamar aktif, fallback ke semua kamar
            if ($minHarga === null) {
                $minHarga = $h->kamar()->min('harga_per_malam');
            }

            $fotos = $h->foto->map(function ($f) use ($baseUrl) {
                $url = $f->url_foto ? trim(str_replace('\\', '/', $f->url_foto)) : null;
                return $url ? $baseUrl . '/' . (str_starts_with($url, 'storage/') ? $url : 'storage/' . $url) : null;
            })->filter()->values();
            return [
                'id' => (string) $h->id,
                'name' => $h->nama_hotel,
                'city' => $h->kota ? $h->kota->nama_kota : '',
                'stars' => (int) round((float) $h->bintang),
                'distance' => $h->jarak_ke_masjid ?? '',
                // harga hotel = harga kamar termurah
                'price' => $minHarga !== null ? (float) $minHarga : null,
                'images' => $fotos->isEmpty() ? [] : $fotos->toArray(),
                'description' => $h->deskripsi ?? '',
            ];
        })->filter()->values();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $list,
        ]);
    }

    /**
     * Public: detail satu hotel (foto + kamar) untuk Komponen Hotel / transaksi hotel. Sumber data: Master Hotel (fallback ke hotel_m jika tidak di LA Umrah).
     */
    public function showHotelPublic($id)
    {
        // Prioritaskan hotel yang ada di daftar LA Umrah
        $row = LaUmrahHotel::with(['hotel' => function ($q) {
            $q->with(['kota', 'foto', 'kamar.tipeKamar']);
        }])
            ->where('hotel_id', $id)
            ->where('is_active', true)
            ->first();

        // Jika tidak ditemukan di LA Umrah, fallback langsung ke master hotel
        if (! $row) {
            $hotel = Hotel::with(['kota', 'foto', 'kamar.tipeKamar'])->find($id);
        } else {
            $hotel = $row->hotel;
        }

        if (! $hotel) {
            return response()->json(['status' => false, 'message' => 'Hotel tidak ditemukan'], 404);
        }

        $baseUrl = rtrim(config('app.url'), '/');

        $fotos = $hotel->foto->map(function ($f) use ($baseUrl) {
            $url = $f->url_foto ? trim(str_replace('\\', '/', $f->url_foto)) : null;
            $displayUrl = $url ? $baseUrl . '/' . (str_starts_with($url, 'storage/') ? $url : 'storage/' . $url) : null;
            return [
                'id' => $f->id,
                'url_foto' => $f->url_foto,
                'url_foto_display' => $displayUrl,
                'urutan' => $f->urutan,
            ];
        })->values();

        $rooms = $hotel->kamar->map(function ($kamar) {
            return [
                'id' => $kamar->id,
                'tipe_kamar' => $kamar->tipeKamar ? $kamar->tipeKamar->tipe_kamar : null,
                'kapasitas' => (int) $kamar->kapasitas,
                'harga_per_malam' => $kamar->harga_per_malam !== null ? (float) $kamar->harga_per_malam : null,
            ];
        })->values();

        // Ambil harga termurah sebagai "harga mulai"
        $minHarga = $rooms->whereNotNull('harga_per_malam')->min('harga_per_malam');

        // Fasilitas umum hotel (dari hotel_fasilitas_t + fasilitas_hotel_m)
        $fasilitasRows = DB::table('hotel_fasilitas_t as hft')
            ->join('fasilitas_hotel_m as fhm', 'fhm.id', '=', 'hft.fasilitas_id')
            ->where('hft.hotel_id', $hotel->id)
            ->where('hft.is_active', true)
            ->where('fhm.is_active', true)
            ->orderBy('fhm.nama')
            ->get(['fhm.id', 'fhm.nama', 'fhm.icon']);
        $facilities = $fasilitasRows->map(fn ($f) => [
            'id' => (int) $f->id,
            'nama' => $f->nama ?? '',
            'icon' => $f->icon ?? null,
        ])->values()->all();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => [
                'id' => (string) $hotel->id,
                'name' => $hotel->nama_hotel,
                'city' => $hotel->kota ? $hotel->kota->nama_kota : '',
                'stars' => (int) round((float) $hotel->bintang),
                'distance' => $hotel->jarak_ke_masjid ?? '',
                'address' => $hotel->alamat ?? '',
                'description' => $hotel->deskripsi ?? '',
                'price' => $minHarga !== null ? (float) $minHarga : null,
                'photos' => $fotos,
                'rooms' => $rooms,
                'facilities' => $facilities,
            ],
        ]);
    }

    /**
     * Public: daftar maskapai untuk paket kostumisasi (frontend isi-paket-kostumisasi).
     */
    public function listMaskapaiPublic()
    {
        $items = LaUmrahMaskapai::with('maskapai.kelasPenerbangan')
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $baseUrl = rtrim(config('app.url'), '/');
        $list = $items->map(function ($row) use ($baseUrl) {
            $m = $row->maskapai;
            if (! $m) {
                return null;
            }
            // Hanya tampilkan maskapai yang punya harga & logo (agar frontend tidak error / kosong)
            if ($m->harga_tiket_per_orang === null) {
                return null;
            }
            $logo = $m->logo_url ? trim(str_replace('\\', '/', $m->logo_url)) : null;
            $logoUrl = $logo ? ($logo && (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) ? $logo : $baseUrl . '/' . (str_starts_with($logo, 'storage/') ? $logo : 'storage/' . $logo)) : '';
            if ($logoUrl === '') {
                return null;
            }
            return [
                'id' => (string) $m->id,
                'name' => $m->nama_maskapai,
                'logo' => $logoUrl,
                'description' => $m->negara_asal ? 'Maskapai dari ' . $m->negara_asal : ($m->kode_iata ? 'Kode ' . $m->kode_iata : ''),
                'harga_per_orang' => $m->harga_tiket_per_orang ? (float) $m->harga_tiket_per_orang : null,
            ];
        })->filter()->values();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $list,
        ]);
    }
}
