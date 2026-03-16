<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Icon;
use Carbon\Carbon;

class FasilitasSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // bersihkan data lama (optional)
        Schema::disableForeignKeyConstraints();
        DB::table('fasilitas_m')->truncate();
        DB::table('jenis_fasilitas_m')->truncate();
        Schema::enableForeignKeyConstraints();

        // ambil icon sesuai kode dari tabel icon_m
        $icons = Icon::pluck('id', 'kode')->toArray();

        // daftar jenis fasilitas dan fasilitas turunannya
        $jenisFasilitas = [
            [
                'nama_jenis' => 'Akomodasi',
                'icon' => 'hotel',
                'fasilitas' => [
                    'Hotel Bintang 5',
                    'Dekat Masjidil Haram',
                    'Dekat Masjid Nabawi'
                ],
            ],
            [
                'nama_jenis' => 'Transportasi',
                'icon' => 'flight',
                'fasilitas' => [
                    'Penerbangan Langsung',
                    'Bus AC ke Mekkah & Madinah'
                ],
            ],
            [
                'nama_jenis' => 'Konsumsi',
                'icon' => 'food',
                'fasilitas' => [
                    '3x Makan per Hari',
                    'Air Zamzam 5 Liter'
                ],
            ],
            [
                'nama_jenis' => 'Perlengkapan Jamaah',
                'icon' => 'bag',
                'fasilitas' => [
                    'Koper Eksklusif',
                    'Tas Selempang',
                    'Seragam Jamaah'
                ],
            ],
            [
                'nama_jenis' => 'Layanan & Bimbingan',
                'icon' => 'customer-service',
                'fasilitas' => [
                    'Bimbingan Manasik',
                    'Tour Leader Berpengalaman',
                    'Muthawif Profesional'
                ],
            ],
        ];

        foreach ($jenisFasilitas as $jenis) {
            // ambil ID icon-nya
            $iconId = $icons[$jenis['icon']] ?? null;

            // insert ke jenis_fasilitas_m
            $jenisId = DB::table('jenis_fasilitas_m')->insertGetId([
                'is_active' => true,
                'nama_jenis' => $jenis['nama_jenis'],
                'icon_id' => $iconId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // insert fasilitas-fasilitasnya
            foreach ($jenis['fasilitas'] as $fasilitas) {
                DB::table('fasilitas_m')->insert([
                    'is_active' => true,
                    'jenis_fasilitas_id' => $jenisId,
                    'nama_fasilitas' => $fasilitas,
                    'deskripsi' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

    }
}
