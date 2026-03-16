<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    /** Prefix URL untuk file di storage (baca dari config agar jalan saat config:cache). */
    private static function storageUrlPrefix(): string
    {
        $prefix = config('filesystems.storage_url_prefix', 'storage');
        return $prefix !== null && $prefix !== '' ? rtrim(str_replace('\\', '/', $prefix), '/') : 'storage';
    }

    /** Daftar lokasi banner (untuk dropdown / penggunaan internal). */
    private static function getLokasiOptionsArray(): array
    {
        return [
            ['value' => 'home', 'label' => 'Home (Utama)'],
            ['value' => 'home_edutrip', 'label' => 'Home - Section Edutrip'],
            ['value' => 'umrah', 'label' => 'Halaman Program Umrah'],
            ['value' => 'haji', 'label' => 'Halaman Program Haji'],
            ['value' => 'edutrip', 'label' => 'Halaman Edutrip'],
            ['value' => 'request_product', 'label' => 'Request Product'],
            ['value' => 'land_arrangement', 'label' => 'Land Arrangement'],
            ['value' => 'join_partner', 'label' => 'Join Partner'],
        ];
    }

    /**
     * List banner (admin): paginated, filter lokasi & is_aktif.
     */
    public function index(Request $request)
    {
        $query = Banner::query()->orderBy('lokasi')->orderBy('urutan')->orderBy('id');

        if ($request->filled('lokasi')) {
            $query->where('lokasi', $request->lokasi);
        }

        if ($request->has('is_aktif') && $request->is_aktif !== '') {
            $query->where('is_aktif', (bool) $request->is_aktif);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->paginate($perPage);

        $baseUrl = rtrim(config('app.url'), '/');
        $prefix = self::storageUrlPrefix();
        $items->getCollection()->transform(function ($row) use ($baseUrl, $prefix) {
            $row->gambar_url = $row->gambar
                ? $baseUrl . '/' . $prefix . '/' . ltrim(str_replace('\\', '/', $row->gambar), '/')
                : null;
            return $row;
        });

        return response()->json([
            'status' => true,
            'message' => 'Daftar banner berhasil diambil',
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
     * Options lokasi untuk dropdown (route handler).
     */
    public function lokasiOptions()
    {
        return response()->json([
            'status' => true,
            'data' => self::getLokasiOptionsArray(),
        ]);
    }

    /**
     * Detail satu banner.
     */
    public function show($id)
    {
        $item = Banner::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Banner tidak ditemukan'], 404);
        }

        $baseUrl = rtrim(config('app.url'), '/');
        $item->gambar_url = $item->gambar
            ? $baseUrl . '/' . self::storageUrlPrefix() . '/' . ltrim(str_replace('\\', '/', $item->gambar), '/')
            : null;

        return response()->json([
            'status' => true,
            'message' => 'Detail banner berhasil diambil',
            'data' => $item,
        ]);
    }

    /**
     * Upload gambar banner (simpan ke storage public).
     */
    public function uploadGambar(Request $request)
    {
        $request->validate([
            'file' => 'required|file|image|max:5120',
        ]);

        Storage::disk('public')->makeDirectory('banner');
        $path = $request->file('file')->store('banner', 'public');

        $baseUrl = rtrim(config('app.url'), '/');
        $url = $baseUrl . '/' . self::storageUrlPrefix() . '/' . ltrim($path, '/');

        return response()->json([
            'status' => true,
            'message' => 'Gambar berhasil diupload',
            'data' => [
                'path' => $path,
                'url' => $url,
            ],
        ], 201);
    }

    /**
     * Simpan banner baru.
     */
    public function store(Request $request)
    {
        $rules = [
            'judul' => 'required|string|max:255',
            'teks' => 'nullable|string|max:500',
            'gambar' => 'nullable|string|max:500',
            'lokasi' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0|max:999',
            'is_aktif' => 'nullable|boolean',
            'link' => 'nullable|string|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = Banner::create([
            'judul' => $request->judul,
            'teks' => $request->teks,
            'gambar' => $request->gambar,
            'lokasi' => $request->lokasi,
            'urutan' => (int) ($request->urutan ?? 1),
            'is_aktif' => $request->boolean('is_aktif', true),
            'link' => $request->link,
        ]);

        $baseUrl = rtrim(config('app.url'), '/');
        $item->gambar_url = $item->gambar
            ? $baseUrl . '/' . self::storageUrlPrefix() . '/' . ltrim(str_replace('\\', '/', $item->gambar), '/')
            : null;

        return response()->json([
            'status' => true,
            'message' => 'Banner berhasil ditambahkan',
            'data' => $item,
        ], 201);
    }

    /**
     * Update banner.
     */
    public function update(Request $request, $id)
    {
        $item = Banner::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Banner tidak ditemukan'], 404);
        }

        $rules = [
            'judul' => 'sometimes|string|max:255',
            'teks' => 'nullable|string|max:500',
            'gambar' => 'nullable|string|max:500',
            'lokasi' => 'sometimes|string|max:100',
            'urutan' => 'nullable|integer|min:0|max:999',
            'is_aktif' => 'nullable|boolean',
            'link' => 'nullable|string|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item->fill($request->only(['judul', 'teks', 'gambar', 'lokasi', 'link']));
        if ($request->has('urutan')) {
            $item->urutan = (int) $request->urutan;
        }
        if ($request->has('is_aktif')) {
            $item->is_aktif = $request->boolean('is_aktif');
        }
        $item->save();

        $baseUrl = rtrim(config('app.url'), '/');
        $item->gambar_url = $item->gambar
            ? $baseUrl . '/' . self::storageUrlPrefix() . '/' . ltrim(str_replace('\\', '/', $item->gambar), '/')
            : null;

        return response()->json([
            'status' => true,
            'message' => 'Banner berhasil diubah',
            'data' => $item,
        ]);
    }

    /**
     * Hapus banner (dan file gambar jika ada).
     */
    public function destroy($id)
    {
        $item = Banner::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Banner tidak ditemukan'], 404);
        }

        if ($item->gambar && Storage::disk('public')->exists($item->gambar)) {
            Storage::disk('public')->delete($item->gambar);
        }

        $item->delete();
        return response()->json([
            'status' => true,
            'message' => 'Banner berhasil dihapus',
        ]);
    }

    /**
     * API publik: list banner aktif (untuk frontend 127.0.0.1:3000).
     * Query: lokasi (optional) — filter by lokasi.
     */
    public function listPublic(Request $request)
    {
        $query = Banner::where('is_aktif', true)->orderBy('urutan')->orderBy('id');

        if ($request->filled('lokasi')) {
            $query->where('lokasi', $request->lokasi);
        }

        $items = $query->get();

        $baseUrl = rtrim(config('app.url'), '/');
        $prefix = self::storageUrlPrefix();
        $items->transform(function ($row) use ($baseUrl, $prefix) {
            $row->gambar_url = $row->gambar
                ? $baseUrl . '/' . $prefix . '/' . ltrim(str_replace('\\', '/', $row->gambar), '/')
                : null;
            return $row;
        });

        return response()->json([
            'status' => true,
            'message' => 'Daftar banner berhasil diambil',
            'data' => $items,
        ]);
    }
}
