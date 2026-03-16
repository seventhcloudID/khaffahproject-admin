<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KelasPenerbangan;
use App\Models\Maskapai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterMaskapaiController extends Controller
{
    /**
     * List maskapai (paginated, filter by is_active & search).
     */
    public function index(Request $request)
    {
        $query = Maskapai::query()->with('kelasPenerbangan');

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($qry) use ($q) {
                $qry->where('nama_maskapai', 'like', $q)
                    ->orWhere('kode_iata', 'like', $q)
                    ->orWhere('negara_asal', 'like', $q);
            });
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $items = $query->orderBy('nama_maskapai')->paginate($perPage);

        $data = collect($items->items())->map(function ($m) {
            $jamBerangkat = $m->jam_keberangkatan;
            $jamSampai = $m->jam_sampai;
            if ($jamBerangkat instanceof \Carbon\Carbon || $jamBerangkat instanceof \DateTimeInterface) {
                $jamBerangkat = $jamBerangkat->format('H:i');
            } elseif (is_string($jamBerangkat) && preg_match('/^\d{2}:\d{2}/', $jamBerangkat)) {
                $jamBerangkat = substr($jamBerangkat, 0, 5);
            }
            if ($jamSampai instanceof \Carbon\Carbon || $jamSampai instanceof \DateTimeInterface) {
                $jamSampai = $jamSampai->format('H:i');
            } elseif (is_string($jamSampai) && preg_match('/^\d{2}:\d{2}/', $jamSampai)) {
                $jamSampai = substr($jamSampai, 0, 5);
            }
            return [
                'id' => $m->id,
                'nama_maskapai' => $m->nama_maskapai,
                'kode_iata' => $m->kode_iata,
                'negara_asal' => $m->negara_asal,
                'logo_url' => $m->logo_url,
                'jam_keberangkatan' => $jamBerangkat ? (string) $jamBerangkat : null,
                'jam_sampai' => $jamSampai ? (string) $jamSampai : null,
                'kelas_penerbangan_id' => $m->kelas_penerbangan_id,
                'kelas_penerbangan' => $m->kelasPenerbangan?->kelas_penerbangan,
                'harga_tiket_per_orang' => $m->harga_tiket_per_orang,
                'is_active' => $m->is_active,
            ];
        })->all();

        return response()->json([
            'status' => true,
            'message' => 'Daftar maskapai berhasil diambil',
            'data' => $data,
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
        $query = Maskapai::where('is_active', true)->orderBy('nama_maskapai');

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where('nama_maskapai', 'like', $q)->orWhere('kode_iata', 'like', $q);
        }

        $limit = min((int) $request->get('limit', 50), 200);
        $items = $query->limit($limit)->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar maskapai berhasil diambil',
            'data' => $items,
        ]);
    }

    /**
     * Public API: list maskapai aktif dari Master Maskapai (sama dengan data di /Paket/Master-Maskapai).
     * Untuk halaman mitra /mitra/komponen/tiket. Data lengkap untuk tampilan penuh.
     */
    public function listPublic()
    {
        $items = Maskapai::with('kelasPenerbangan')
            ->where('is_active', true)
            ->orderBy('nama_maskapai')
            ->get()
            ->unique('id')
            ->values();

        $baseUrl = rtrim(config('app.url'), '/');
        $list = $items->map(function ($m) use ($baseUrl) {
            $logo = $m->logo_url ? trim(str_replace('\\', '/', $m->logo_url)) : null;
            $logoUrl = $logo && (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://'))
                ? $logo
                : ($logo ? $baseUrl . '/' . (str_starts_with($logo, 'storage/') ? $logo : 'storage/' . $logo) : '');
            $description = $m->negara_asal
                ? 'Maskapai dari ' . $m->negara_asal
                : ($m->kode_iata ? 'Kode ' . $m->kode_iata : '');

            $jamBerangkat = $m->jam_keberangkatan;
            $jamSampai = $m->jam_sampai;
            if ($jamBerangkat instanceof \Carbon\Carbon || $jamBerangkat instanceof \DateTimeInterface) {
                $jamBerangkat = $jamBerangkat->format('H:i');
            } elseif (is_string($jamBerangkat) && preg_match('/^\d{2}:\d{2}/', $jamBerangkat)) {
                $jamBerangkat = substr($jamBerangkat, 0, 5);
            } else {
                $jamBerangkat = $jamBerangkat ? (string) $jamBerangkat : null;
            }
            if ($jamSampai instanceof \Carbon\Carbon || $jamSampai instanceof \DateTimeInterface) {
                $jamSampai = $jamSampai->format('H:i');
            } elseif (is_string($jamSampai) && preg_match('/^\d{2}:\d{2}/', $jamSampai)) {
                $jamSampai = substr($jamSampai, 0, 5);
            } else {
                $jamSampai = $jamSampai ? (string) $jamSampai : null;
            }

            return [
                'id' => (string) $m->id,
                'name' => $m->nama_maskapai,
                'logo' => $logoUrl ?: null,
                'description' => $description ?: null,
                'kode_iata' => $m->kode_iata ? (string) $m->kode_iata : null,
                'negara_asal' => $m->negara_asal ? (string) $m->negara_asal : null,
                'jam_keberangkatan' => $jamBerangkat,
                'jam_sampai' => $jamSampai,
                'kelas_penerbangan' => $m->kelasPenerbangan?->kelas_penerbangan ?? null,
                'harga_per_orang' => $m->harga_tiket_per_orang !== null ? (float) $m->harga_tiket_per_orang : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $list,
        ], 200);
    }

    /**
     * Daftar kelas penerbangan untuk dropdown.
     */
    public function kelasPenerbanganOptions()
    {
        $items = KelasPenerbangan::where('is_active', true)->orderBy('kelas_penerbangan')->get(['id', 'kelas_penerbangan']);

        return response()->json([
            'status' => true,
            'message' => 'Daftar kelas penerbangan berhasil diambil',
            'data' => $items->map(fn ($k) => ['value' => $k->id, 'label' => $k->kelas_penerbangan]),
        ]);
    }

    /**
     * Detail satu maskapai (dengan kelas penerbangan).
     */
    public function show($id)
    {
        $item = Maskapai::with('kelasPenerbangan')->find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Maskapai tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail maskapai berhasil diambil',
            'data' => $item,
        ]);
    }

    /**
     * Simpan maskapai baru.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_maskapai' => 'required|string|max:255',
            'kode_iata' => 'nullable|string|max:10',
            'negara_asal' => 'nullable|string|max:100',
            'logo_url' => 'nullable|string',
            'jam_keberangkatan' => 'nullable|date_format:H:i',
            'jam_sampai' => 'nullable|date_format:H:i',
            'kelas_penerbangan_id' => 'nullable|exists:kelas_penerbangan_m,id',
            'harga_tiket_per_orang' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = Maskapai::create($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil ditambahkan',
            'data' => $item,
        ], 201);
    }

    /**
     * Update maskapai.
     */
    public function update(Request $request, $id)
    {
        $item = Maskapai::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Maskapai tidak ditemukan'], 404);
        }

        $rules = [
            'nama_maskapai' => 'sometimes|string|max:255',
            'kode_iata' => 'nullable|string|max:10',
            'negara_asal' => 'nullable|string|max:100',
            'logo_url' => 'nullable|string',
            'jam_keberangkatan' => 'nullable|date_format:H:i',
            'jam_sampai' => 'nullable|date_format:H:i',
            'kelas_penerbangan_id' => 'nullable|exists:kelas_penerbangan_m,id',
            'harga_tiket_per_orang' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item->update($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil diubah',
            'data' => $item->fresh(),
        ]);
    }

    /**
     * Nonaktifkan maskapai (soft delete).
     */
    public function destroy($id)
    {
        $item = Maskapai::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Maskapai tidak ditemukan'], 404);
        }

        $item->update(['is_active' => false]);

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil dinonaktifkan',
        ]);
    }

    /**
     * Hapus maskapai permanen dari database.
     * Relasi (paket_umrah_maskapai_t, la_umrah_maskapai_t) ikut terhapus karena cascade.
     */
    public function destroyPermanent($id)
    {
        $item = Maskapai::find($id);
        if (! $item) {
            return response()->json(['status' => false, 'message' => 'Maskapai tidak ditemukan'], 404);
        }

        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Maskapai berhasil dihapus permanen',
        ]);
    }
}
