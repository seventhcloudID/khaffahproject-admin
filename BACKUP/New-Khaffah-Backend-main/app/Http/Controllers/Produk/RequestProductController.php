<?php

namespace App\Http\Controllers\Produk;

use App\Http\Controllers\Controller;
use App\Models\JenisTransaksi;
use App\Models\LayananPaketRequest;
use App\Models\StatusPembayaran;
use App\Models\StatusTransaksi;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Form Requirements: Umrah Custom & LA Custom.
 * POST /api/request-products menerima payload dari form product-request (data diri, hotel, keberangkatan, jamaah, komponen LA).
 */
class RequestProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'client' => 'required|array',
            'client.fullName' => 'required|string|max:255',
            'client.phoneNumber' => 'required|string|max:50',
            'client.state' => 'nullable|string',
            'client.city' => 'nullable|string',
            'client.suburb' => 'nullable|string',
            'client.address' => 'nullable|string',
            'client.email' => 'nullable|string',
            'client.nik' => 'nullable|string|max:20',
            'clients' => 'nullable|array',
            'departureDate' => 'nullable|string|date',
            'returnDate' => 'nullable|string|date',
            'hotelMekkah' => 'nullable|string',
            'hotelMadinah' => 'nullable|string',
            'hotelMekkahHarga' => 'nullable|numeric',
            'hotelMadinahHarga' => 'nullable|numeric',
            'fasilitasHotelMekkah' => 'nullable|string',
            'fasilitasHotelMadinah' => 'nullable|string',
            'lokasiHotelMekkah' => 'nullable|string',
            'lokasiHotelMadinah' => 'nullable|string',
            'kuotaKamar' => 'nullable|integer|min:1',
            'departureAirport' => 'nullable|string',
            'arrivalAirport' => 'nullable|string',
            'namaMaskapai' => 'nullable|string',
            'additionalDestination' => 'nullable|string',
            'tipePaket' => 'nullable|string|in:umrah_custom,la_custom',
            'paketTemplateId' => 'nullable|integer',
            'negaraLiburan' => 'nullable|array',
            'negaraLiburan.*' => 'nullable|string|max:100',
            'layananTambahanIds' => 'nullable|array',
            'layananTambahanIds.*' => 'nullable|integer',
            'komponen' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $userId = auth()->id();
            $client = $request->input('client', []);
            // Gunakan nama wilayah (stateName, cityName, suburbName) agar alamat bukan angka ID
            $alamatLengkap = trim(implode(', ', array_filter([
                $client['address'] ?? '',
                $client['suburbName'] ?? $client['suburb'] ?? '',
                $client['cityName'] ?? $client['city'] ?? '',
                $client['stateName'] ?? $client['state'] ?? '',
            ])));

            $jenisTransaksi = JenisTransaksi::where('kode', 'REQUEST')->firstOrFail();
            $tanggal = date('dmy');
            $lastTransaksi = Transaksi::where('jenis_transaksi_id', $jenisTransaksi->id)
                ->whereDate('created_at', today())
                ->count();
            $autoIncrement = str_pad($lastTransaksi + 1, 4, '0', STR_PAD_LEFT);
            $tipe = $request->input('tipePaket', 'umrah_custom');
            $prefix = $tipe === 'la_custom' ? 'LA-CUSTOM' : 'UMRAH-CUSTOM';
            $kodeTransaksi = $prefix . '-' . $tanggal . '-' . $userId . '-' . $autoIncrement;

            $kategoriPaket = $request->input('kategoriPaket');
            $snapshot = [
                'tipe' => $tipe,
                'kategori_paket' => $kategoriPaket,
                'nama_paket' => $kategoriPaket ?: ($tipe === 'la_custom' ? 'Land Arrangement Custom' : 'Request Umrah Custom'),
                'paket_template_id' => $request->input('paketTemplateId'),
                'tanggal_program_umrah' => [
                    'departureDate' => $request->input('departureDate'),
                    'returnDate' => $request->input('returnDate'),
                ],
                'data_hotel' => [
                    'hotelMekkah' => $request->input('hotelMekkah'),
                    'hotelMadinah' => $request->input('hotelMadinah'),
                    'hotelMekkahHarga' => $request->input('hotelMekkahHarga'),
                    'hotelMadinahHarga' => $request->input('hotelMadinahHarga'),
                    'fasilitasHotelMekkah' => $request->input('fasilitasHotelMekkah'),
                    'fasilitasHotelMadinah' => $request->input('fasilitasHotelMadinah'),
                    'lokasiHotelMekkah' => $request->input('lokasiHotelMekkah'),
                    'lokasiHotelMadinah' => $request->input('lokasiHotelMadinah'),
                    'kuotaKamar' => $request->input('kuotaKamar'),
                ],
                'data_keberangkatan' => [
                    'bandaraKeberangkatan' => $request->input('departureAirport'),
                    'tanggalKeberangkatan' => $request->input('departureDate'),
                    'namaMaskapai' => $request->input('namaMaskapai'),
                    'tanggalKembali' => $request->input('returnDate'),
                    'bandaraKepulangan' => $request->input('arrivalAirport'),
                ],
                'additionalDestination' => $request->input('additionalDestination'),
                'negara_liburan' => $request->input('negaraLiburan'),
                'layanan_tambahan_ids' => $request->input('layananTambahanIds'), // ID layanan tambahan yang user centang
                'komponen' => $request->input('komponen'),
            ];

            // Snapshot layanan (nama + harga) agar order detail bisa tampil tanpa fetch master
            $layananWajib = LayananPaketRequest::where('is_active', true)
                ->where('jenis', LayananPaketRequest::JENIS_WAJIB)
                ->orderBy('urutan')
                ->get(['id', 'nama', 'harga', 'satuan'])
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'nama' => $row->nama,
                    'harga' => (float) $row->harga,
                    'satuan' => $row->satuan ?? '',
                ])
                ->values()
                ->toArray();
            $tambahanIds = $request->input('layananTambahanIds', []);
            $layananTambahan = [];
            if (! empty($tambahanIds)) {
                $layananTambahan = LayananPaketRequest::where('is_active', true)
                    ->where('jenis', LayananPaketRequest::JENIS_TAMBAHAN)
                    ->whereIn('id', $tambahanIds)
                    ->orderBy('urutan')
                    ->get(['id', 'nama', 'harga', 'satuan'])
                    ->map(fn ($row) => [
                        'id' => $row->id,
                        'nama' => $row->nama,
                        'harga' => (float) $row->harga,
                        'satuan' => $row->satuan ?? '',
                    ])
                    ->values()
                    ->toArray();
            }
            $snapshot['layanan_wajib'] = $layananWajib;
            $snapshot['layanan_tambahan'] = $layananTambahan;

            $jamaahData = [];
            foreach ($request->input('clients', []) as $j) {
                $jamaahData[] = [
                    'nama' => $j['fullName'] ?? $j['nama'] ?? null,
                    'nik' => $j['nik'] ?? null,
                    'no_paspor' => $j['no_paspor'] ?? null,
                    'tanggal_lahir' => $j['tanggal_lahir'] ?? null,
                    'id' => isset($j['id']) ? (is_numeric($j['id']) ? (int) $j['id'] : $j['id']) : null,
                    'dokumen_ktp_id' => isset($j['dokumen_ktp_id']) ? (int) $j['dokumen_ktp_id'] : null,
                    'dokumen_paspor_id' => isset($j['dokumen_paspor_id']) ? (int) $j['dokumen_paspor_id'] : null,
                ];
            }

            // ================= HITUNG TOTAL BIAYA (UNTUK JURNAL & LAPORAN) =================
            $jamaahCount = max(count($jamaahData) ?: 1, 1);
            $dep = $request->input('departureDate');
            $ret = $request->input('returnDate');
            $depDate = $dep ? new \DateTime($dep) : new \DateTime();
            $retDate = $ret ? new \DateTime($ret) : (clone $depDate)->modify('+9 days');
            $duration = max((int) $depDate->diff($retDate)->format('%a') ?: 9, 1);

            $dataHotel = $snapshot['data_hotel'] ?? [];
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

            $toLayananValue = function (array $item) use ($jamaahCount, $duration) {
                $base = isset($item['harga']) ? (float) $item['harga'] : 0;
                if ($base <= 0) {
                    return 0;
                }
                $satuan = strtolower((string) ($item['satuan'] ?? ''));
                $perPax = preg_match('/pax|orang/', $satuan);
                $perHari = preg_match('/hari/', $satuan);
                $mult = $perPax ? $jamaahCount : ($perHari ? $duration : 1);
                return $base * $mult;
            };

            $totalLayanan = 0;
            foreach ($layananWajib as $lw) {
                $totalLayanan += $toLayananValue($lw);
            }
            foreach ($layananTambahan as $lt) {
                $totalLayanan += $toLayananValue($lt);
            }

            $totalBiayaOrder = $totalHotel + $totalLayanan;
            if ($totalBiayaOrder > 0) {
                $snapshot['totalBiaya'] = $totalBiayaOrder;
                $snapshot['total_biaya_order'] = $totalBiayaOrder;
            }

            $transaksi = Transaksi::create([
                'is_active' => true,
                'akun_id' => $userId,
                'dibuat_sebagai_mitra' => (bool) $request->input('dibuat_sebagai_mitra', false),
                'gelar_id' => null,
                'nama_lengkap' => $client['fullName'] ?? 'Pemesan',
                'no_whatsapp' => $client['phoneNumber'] ?? '',
                'provinsi_id' => null,
                'kota_id' => null,
                'kecamatan_id' => null,
                'alamat_lengkap' => $alamatLengkap ?: null,
                'deskripsi' => null,
                'jenis_transaksi_id' => $jenisTransaksi->id,
                'produk_id' => null,
                'snapshot_produk' => $snapshot,
                'jamaah_data' => !empty($jamaahData) ? $jamaahData : null,
                'kode_transaksi' => $kodeTransaksi,
                'is_with_payment' => false,
                'total_biaya' => $totalBiayaOrder > 0 ? $totalBiayaOrder : null,
                'status_pembayaran_id' => StatusPembayaran::where('kode', 'NON_PAYMENT')->value('id'),
                'status_transaksi_id' => StatusTransaksi::where('kode', 'BELUM_DIHUBUNGI')->value('id'),
                'nomor_pembayaran' => null,
                'tanggal_transaksi' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Permintaan paket custom berhasil dikirim.',
                'data' => [
                    'transaksi' => $transaksi,
                    'kode_transaksi' => $kodeTransaksi,
                ],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim permintaan: ' . $th->getMessage(),
            ], 500);
        }
    }
}
