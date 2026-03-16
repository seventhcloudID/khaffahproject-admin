<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Maskapai;
use App\Models\PaketUmrah;

class PaketUmrahSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil hotel Mekkah dan Madinah Random
        $mekkahHotel = Hotel::where('is_active', true)
            ->whereHas('kota', fn($q) => $q->where('nama_kota', 'Makkah'))
            ->inRandomOrder()
            ->first();

        $madinahHotel = Hotel::where('is_active', true)
            ->whereHas('kota', fn($q) => $q->where('nama_kota', 'Madinah'))
            ->inRandomOrder()
            ->first();

        // Ambil 2 maskapai aktif
        $maskapai = Maskapai::where('is_active', true)->limit(2)->get();

        // Ambil musim pertama
        $musimId = DB::table('musim_m')->where('is_active', true)->value('id');
        // Ambil kota keberangkatan dan tujuan
        $keberangkatanId = DB::table('kota_m')->where('nama_kota', 'Jakarta')->value('id');
        $tujuanId = DB::table('kota_m')->where('nama_kota', 'Makkah')->value('id');

        // Insert Paket Umrah
        $paketUmrah = PaketUmrah::create([
            'is_active' => true,
            'nama_paket' => 'Umrah Reguler November',
            'deskripsi' => 'Paket Umrah Reguler dengan hotel bintang 5 di Mekkah dan Madinah, maskapai terbaik, fasilitas lengkap.',
            'musim_id' => $musimId,
            'lokasi_keberangkatan_id' => $keberangkatanId,
            'lokasi_tujuan_id' => $tujuanId,
            'durasi_total' => 8,
            'jumlah_pax' => 32,
            'harga_termurah' => 32000000,
            'harga_termahal' => 33050000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        //seed foto paket umrah
        $fotos = ['clock-tower.jpg', 'hotel.jpg', 'kabbah.jpg', 'kereta-cepat.jpg', 'madinah.jpg'];
        $fotoBasePath = 'foto_paket/';
        foreach ($fotos as $index => $namaFoto) {
            DB::table('paket_umrah_foto_m')->insert([
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'url_foto' => $fotoBasePath . $namaFoto,
                'urutan' => $index + 1,
            ]);
        }

        // Paket-Hotel
        if ($mekkahHotel) {
            DB::table('paket_umrah_hotel_t')->insert([
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'hotel_id' => $mekkahHotel->id,
                'kota_id' => 1,
                'keterangan' => 'Hotel di Mekkah',
            ]);
        }
        if ($madinahHotel) {
            DB::table('paket_umrah_hotel_t')->insert([
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'hotel_id' => $madinahHotel->id,
                'kota_id' => 2,
                'keterangan' => 'Hotel di Madinah',
            ]);
        }

        // Paket-Maskapai
        foreach ($maskapai as $m) {
            DB::table('paket_umrah_maskapai_t')->insert([
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'maskapai_id' => $m->id,
                'kelas_penerbangan_id' => rand(1, 4),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Fasilitas (random ambil 5–8 item)
        $fasilitas = DB::table('fasilitas_m')
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(rand(5, 8))
            ->get();

        foreach ($fasilitas as $f) {
            DB::table('paket_umrah_fasilitas_t')->insert([
                'is_active' => true,
                'is_head' => false,
                'paket_umrah_id' => $paketUmrah->id,
                'fasilitas_id'   => $f->id,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // Perlengkapan (random ambil 5–8 item)
        $perlengkapan = DB::table('perlengkapan_m')
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(rand(5, 8))
            ->get();

        foreach ($perlengkapan as $p) {
            DB::table('paket_umrah_perlengkapan_t')->insert([
                'is_active' => true,
                'is_head' => false,
                'paket_umrah_id'   => $paketUmrah->id,
                'perlengkapan_id'  => $p->id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
        // Keberangkatan
        DB::table('paket_umrah_keberangkatan_t')->insert([
            [
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'tanggal_berangkat' => Carbon::now()->addDays(14)->toDateString(),
                'jam_berangkat' => '09:00:00',
                'tanggal_pulang' => Carbon::now()->addDays(24)->toDateString(),
                'jam_pulang' => '21:00:00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'tanggal_berangkat' => Carbon::now()->addDays(30)->toDateString(),
                'jam_berangkat' => '10:00:00',
                'tanggal_pulang' => Carbon::now()->addDays(40)->toDateString(),
                'jam_pulang' => '22:00:00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('paket_umrah_destinasi_t')->insert([
            [
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'kota_id' => 1,
                'durasi' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'paket_umrah_id' => $paketUmrah->id,
                'kota_id' => 2,
                'durasi' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // --- Buat mapping tipe paket (paket_umrah_tipe_m) berdasarkan tipe_kamar ---
        // Ambil tipe_kamar dari hotel_kamar yang ter-link ke paket ini
        $usedTipeKamarIds = DB::table('paket_umrah_tipe_m')->pluck('tipe_kamar_id')->filter()->all();

        $hotelKamarQuery = DB::table('hotel_kamar_m')
            ->whereIn('hotel_id', function ($q) use ($paketUmrah) {
                $q->select('hotel_id')
                    ->from('paket_umrah_hotel_t')
                    ->where('paket_umrah_id', $paketUmrah->id);
            });

        if (!empty($usedTipeKamarIds)) {
            $hotelKamarQuery->whereNotIn('tipe_kamar_id', $usedTipeKamarIds);
        }

        $hotelKamarRows = $hotelKamarQuery->get();

        if ($hotelKamarRows->isNotEmpty()) {
            // group by tipe_kamar_id to create one paket tipe per tipe kamar
            $grouped = $hotelKamarRows->groupBy('tipe_kamar_id');
            foreach ($grouped as $tipeKamarId => $rows) {
                // Build snapshots for this tipe_kamar by including ALL mapped hotels (hotel_kamar rows)
                $hotelSnapshot = [];
                $subtotalPerPaxTotal = 0;

                foreach ($rows as $hk) {
                    // Tentukan faktor harga berdasarkan kapasitas kamar per hotel_kamar
                    $kapasitas = max(1, $hk->kapasitas); // fallback minimal 1
                    $faktorHarga = match (true) {
                        $kapasitas <= 1 => 1.6,
                        $kapasitas == 2 => 1.3,
                        $kapasitas == 3 => 1.1,
                        $kapasitas == 4 => 1.0,
                        $kapasitas >= 5 => 0.9,
                    };

                    // Komponen biaya tetap (sama untuk semua hotel pada tipe ini)
                    $basePrice = 28000000;
                    $pajak = 500000;
                    $asuransi = 150000;
                    $biayaMaskapai = 2000000;

                    // Biaya hotel per hotel_kamar
                    $biayaHotel = 1500000 * $faktorHarga;

                    // Hitung subtotal per pax untuk hotel ini
                    $subtotalPerPax = $basePrice + $pajak + $asuransi + $biayaMaskapai + $biayaHotel;
                    $subtotalPerPaxTotal += $subtotalPerPax;

                    $hotelSnapshot[] = [
                        'kota_id' => DB::table('hotel_m')->where('id', $hk->hotel_id)->value('kota_id'),
                        'hotel_id' => $hk->hotel_id,
                        'hotel_nama' => DB::table('hotel_m')->where('id', $hk->hotel_id)->value('nama_hotel'),
                        'tipe_kamar_id' => $tipeKamarId,
                        'nama_tipe_kamar' => DB::table('tipe_kamar_m')->where('id', $tipeKamarId)->value('tipe_kamar'),
                        'checkin' => Carbon::now()->addDays(14)->toDateString(),
                        'checkout' => Carbon::now()->addDays(19)->toDateString(),
                        'nights' => 5,
                        'kapasitas_kamar' => $hk->kapasitas,
                    ];
                }

                // Average harga_per_pax across all hotels for this tipe
                $avgHargaPerPax = $subtotalPerPaxTotal > 0 ? intval(round($subtotalPerPaxTotal / count($hotelSnapshot))) : 0;

                DB::table('paket_umrah_tipe_m')->insert([
                    'paket_umrah_id' => $paketUmrah->id,
                    'tipe_kamar_id' => $tipeKamarId,
                    'is_active' => true,
                    'harga_per_pax' => $avgHargaPerPax,
                    'kapasitas_total' => 32,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
