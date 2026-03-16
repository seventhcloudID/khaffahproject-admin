<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelFoto;
use App\Models\HotelKamar;
use App\Models\TipeKamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Master Hotel: satu sumber data hotel (hotel_m) untuk paket dan Komponen Hotel.
 * Data di sini dipakai di: paket LA Umrah, isi paket kostumisasi, dan Komponen Hotel (mitra pesan hotel tanpa paket).
 * Daftar hotel yang tampil di Komponen Hotel diatur di Master LA Umrah (tab Hotel).
 */
class MasterHotelController extends Controller
{
    /**
     * Daftar kota untuk dropdown hotel: hanya Mekkah dan Madinah.
     */
    public function kotaOptions()
    {
        $kota = DB::table('kota_m')
            ->whereIn('nama_kota', ['Makkah', 'Madinah'])
            ->orderByRaw("CASE WHEN nama_kota = 'Makkah' THEN 1 WHEN nama_kota = 'Madinah' THEN 2 ELSE 3 END")
            ->get(['id', 'nama_kota']);

        return response()->json([
            'status' => true,
            'message' => 'Daftar kota (Mekkah & Madinah) berhasil diambil',
            'data' => $kota->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kota]),
        ]);
    }

    /**
     * List hotel (paginated, filter by is_active & search).
     */
    public function index(Request $request)
    {
        $query = Hotel::with('kota');

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($qry) use ($q) {
                $qry->where('nama_hotel', 'like', $q)
                    ->orWhere('alamat', 'like', $q);
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->orderBy('nama_hotel')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Daftar hotel berhasil diambil',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * List sederhana untuk dropdown (tanpa pagination).
     */
    public function list(Request $request)
    {
        $query = Hotel::with('kota')->where('is_active', true)->orderBy('nama_hotel');

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where('nama_hotel', 'like', $q);
        }

        $limit = min((int) $request->get('limit', 50), 200);
        $items = $query->limit($limit)->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar hotel berhasil diambil',
            'data' => $items,
        ]);
    }

    /**
     * Daftar tipe kamar untuk dropdown (Double, Triple, Quad, dll).
     */
    public function tipeKamarOptions()
    {
        $items = TipeKamar::where('is_active', true)->orderBy('kapasitas')->get(['id', 'tipe_kamar', 'kapasitas']);

        return response()->json([
            'status' => true,
            'message' => 'Daftar tipe kamar berhasil diambil',
            'data' => $items->map(fn ($k) => [
                'value' => $k->id,
                'label' => $k->tipe_kamar,
                'kapasitas' => (int) $k->kapasitas,
            ]),
        ]);
    }

    /**
     * Detail satu hotel (dengan foto & kamar).
     */
    public function show($id)
    {
        $item = Hotel::with(['kota', 'foto', 'kamar.tipeKamar'])->find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Hotel tidak ditemukan'], 404);
        }

        $baseUrl = rtrim(config('app.url'), '/');
        $item->foto = $item->foto->map(function ($f) use ($baseUrl) {
            $url = $f->url_foto ? trim(str_replace('\\', '/', $f->url_foto)) : null;
            $displayUrl = $url ? $baseUrl . '/' . (str_starts_with($url, 'storage/') ? $url : 'storage/' . $url) : null;
            return [
                'id' => $f->id,
                'url_foto' => $f->url_foto,
                'url_foto_display' => $displayUrl,
                'urutan' => $f->urutan,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'Detail hotel berhasil diambil',
            'data' => $item,
        ]);
    }

    /**
     * Upload foto hotel (multipart: file, hotel_id, urutan).
     */
    public function uploadFoto(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotel_m,id',
            'urutan' => 'required|integer|min:1|max:20',
            'file' => 'required|file|image|max:5120',
        ]);

        Storage::disk('public')->makeDirectory('foto_hotel');

        $path = $request->file('file')->store('foto_hotel', 'public');

        $foto = HotelFoto::create([
            'hotel_id' => $request->hotel_id,
            'url_foto' => $path,
            'urutan' => (int) $request->urutan,
            'is_active' => true,
        ]);

        $baseUrl = rtrim(config('app.url'), '/');
        $displayUrl = $baseUrl . '/storage/' . ltrim($path, '/');

        return response()->json([
            'status' => true,
            'message' => 'Foto berhasil diupload',
            'data' => [
                'id' => $foto->id,
                'url_foto' => $path,
                'url_foto_display' => $displayUrl,
                'urutan' => $foto->urutan,
            ],
        ], 201);
    }

    /**
     * Hapus satu foto hotel.
     */
    public function deleteFoto($id)
    {
        $foto = HotelFoto::find($id);
        if (! $foto) {
            return response()->json(['status' => false, 'message' => 'Foto tidak ditemukan'], 404);
        }
        if (Storage::disk('public')->exists($foto->url_foto)) {
            Storage::disk('public')->delete($foto->url_foto);
        }
        $foto->delete();
        return response()->json(['status' => true, 'message' => 'Foto berhasil dihapus']);
    }

    /**
     * Simpan hotel baru (termasuk foto & kamar).
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_hotel' => 'required|string|max:255',
            'kota_id' => 'required|exists:kota_m,id',
            'jarak_ke_masjid' => 'nullable|string|max:100',
            'bintang' => 'nullable|numeric|min:0|max:5',
            'alamat' => 'nullable|string',
            'jam_checkin_mulai' => 'nullable|date_format:H:i',
            'jam_checkin_berakhir' => 'nullable|date_format:H:i',
            'jam_checkout_mulai' => 'nullable|date_format:H:i',
            'jam_checkout_berakhir' => 'nullable|date_format:H:i',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'fotos' => 'nullable|array',
            'fotos.*.url_foto' => 'required|string',
            'fotos.*.urutan' => 'required|integer|min:1',
            'kamar' => 'nullable|array',
            'kamar.*.tipe_kamar_id' => 'required|exists:tipe_kamar_m,id',
            'kamar.*.nama_kamar' => 'nullable|string|max:255',
            'kamar.*.kapasitas' => 'required|integer|min:1|max:10',
            'kamar.*.harga_per_malam' => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $fotos = $data['fotos'] ?? [];
        $kamar = $data['kamar'] ?? [];
        unset($data['fotos'], $data['kamar']);

        $item = Hotel::create($data);

        foreach ($fotos as $f) {
            HotelFoto::create([
                'hotel_id' => $item->id,
                'url_foto' => $f['url_foto'],
                'urutan' => $f['urutan'],
                'is_active' => true,
            ]);
        }

        foreach ($kamar as $k) {
            $namaKamar = $k['nama_kamar'] ?? (TipeKamar::find($k['tipe_kamar_id'])->tipe_kamar ?? '') . ' Room';
            HotelKamar::create([
                'hotel_id' => $item->id,
                'tipe_kamar_id' => $k['tipe_kamar_id'],
                'nama_kamar' => $namaKamar,
                'kapasitas' => (int) $k['kapasitas'],
                'harga_per_malam' => isset($k['harga_per_malam']) && $k['harga_per_malam'] !== '' ? $k['harga_per_malam'] : null,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Hotel berhasil ditambahkan',
            'data' => $item->load(['kota', 'foto', 'kamar.tipeKamar']),
        ], 201);
    }

    /**
     * Update hotel (termasuk foto & kamar; fotos/kamar dikirim penuh, yang tidak ada di list akan dihapus).
     */
    public function update(Request $request, $id)
    {
        $item = Hotel::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Hotel tidak ditemukan'], 404);
        }

        $rules = [
            'nama_hotel' => 'sometimes|string|max:255',
            'kota_id' => 'sometimes|exists:kota_m,id',
            'jarak_ke_masjid' => 'nullable|string|max:100',
            'bintang' => 'nullable|numeric|min:0|max:5',
            'alamat' => 'nullable|string',
            'jam_checkin_mulai' => 'nullable|date_format:H:i',
            'jam_checkin_berakhir' => 'nullable|date_format:H:i',
            'jam_checkout_mulai' => 'nullable|date_format:H:i',
            'jam_checkout_berakhir' => 'nullable|date_format:H:i',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'fotos' => 'nullable|array',
            'fotos.*.id' => 'nullable|exists:hotel_foto_m,id',
            'fotos.*.url_foto' => 'required|string',
            'fotos.*.urutan' => 'required|integer|min:1',
            'kamar' => 'nullable|array',
            'kamar.*.id' => 'nullable|exists:hotel_kamar_m,id',
            'kamar.*.tipe_kamar_id' => 'required|exists:tipe_kamar_m,id',
            'kamar.*.nama_kamar' => 'nullable|string|max:255',
            'kamar.*.kapasitas' => 'required|integer|min:1|max:10',
            'kamar.*.harga_per_malam' => 'nullable|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $fotos = $data['fotos'] ?? [];
        $kamar = $data['kamar'] ?? [];
        unset($data['fotos'], $data['kamar']);

        $item->update($data);

        $fotoIds = [];
        foreach ($fotos as $f) {
            if (! empty($f['id'])) {
                HotelFoto::where('id', $f['id'])->where('hotel_id', $item->id)->update([
                    'url_foto' => $f['url_foto'],
                    'urutan' => $f['urutan'],
                ]);
                $fotoIds[] = $f['id'];
            } else {
                $newFoto = HotelFoto::create([
                    'hotel_id' => $item->id,
                    'url_foto' => $f['url_foto'],
                    'urutan' => $f['urutan'],
                    'is_active' => true,
                ]);
                $fotoIds[] = $newFoto->id;
            }
        }
        HotelFoto::where('hotel_id', $item->id)->whereNotIn('id', $fotoIds)->each(function ($f) {
            if (Storage::disk('public')->exists($f->url_foto)) {
                Storage::disk('public')->delete($f->url_foto);
            }
            $f->delete();
        });

        $kamarIds = [];
        foreach ($kamar as $k) {
            $namaKamar = $k['nama_kamar'] ?? (TipeKamar::find($k['tipe_kamar_id'])->tipe_kamar ?? '') . ' Room';
            $payload = [
                'hotel_id' => $item->id,
                'tipe_kamar_id' => $k['tipe_kamar_id'],
                'nama_kamar' => $namaKamar,
                'kapasitas' => (int) $k['kapasitas'],
                'harga_per_malam' => isset($k['harga_per_malam']) && $k['harga_per_malam'] !== '' ? $k['harga_per_malam'] : null,
                'is_active' => true,
            ];
            if (! empty($k['id'])) {
                HotelKamar::where('id', $k['id'])->where('hotel_id', $item->id)->update($payload);
                $kamarIds[] = $k['id'];
            } else {
                $newKamar = HotelKamar::create($payload);
                $kamarIds[] = $newKamar->id;
            }
        }
        HotelKamar::where('hotel_id', $item->id)->whereNotIn('id', $kamarIds)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Hotel berhasil diubah',
            'data' => $item->fresh(['kota', 'foto', 'kamar.tipeKamar']),
        ]);
    }

    /**
     * Hapus hotel (soft: set is_active = false, atau hard delete).
     */
    public function destroy($id)
    {
        $item = Hotel::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Hotel tidak ditemukan'], 404);
        }

        $item->update(['is_active' => false]);

        return response()->json([
            'status' => true,
            'message' => 'Hotel berhasil dinonaktifkan',
        ]);
    }
}
