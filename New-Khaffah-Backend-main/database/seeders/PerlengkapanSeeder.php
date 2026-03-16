<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Icon;
use Carbon\Carbon;

class PerlengkapanSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Bersihkan data lama (optional, tapi aman buat dev)
        Schema::disableForeignKeyConstraints();
        DB::table('perlengkapan_m')->truncate();
        DB::table('jenis_perlengkapan_m')->truncate();
        Schema::enableForeignKeyConstraints();

        // Ambil semua icon berdasarkan kode
        $icons = Icon::pluck('id', 'kode')->toArray();

        // --- Daftar jenis perlengkapan dan isinya ---
        $jenisPerlengkapan = [
            [
                'nama_jenis' => 'Pakaian & Aksesori',
                'icon' => 'shirt',
                'perlengkapan' => [
                    'Seragam Jamaah',
                    'Baju Ihram (Laki-laki)',
                    'Mukena (Perempuan)',
                    'Scarf / Syal',
                ],
            ],
            [
                'nama_jenis' => 'Tas & Penyimpanan',
                'icon' => 'bag',
                'perlengkapan' => [
                    'Koper Besar',
                    'Tas Kabin',
                    'Tas Selempang',
                ],
            ],
            [
                'nama_jenis' => 'Perlengkapan Ibadah',
                'icon' => 'mosque',
                'perlengkapan' => [
                    'Buku Panduan Manasik',
                    'Doa Harian',
                    'Sajadah Travel',
                    'Botol Air Zamzam',
                ],
            ],
            [
                'nama_jenis' => 'Perlengkapan Pribadi',
                'icon' => 'bottle',
                'perlengkapan' => [
                    'Tempat Minum',
                    'Sabun & Sampo Travel',
                    'Masker & Tisu Basah',
                ],
            ],
            [
                'nama_jenis' => 'Pelengkap Perjalanan',
                'icon' => 'card',
                'perlengkapan' => [
                    'ID Card Jamaah',
                    'Name Tag',
                    'Gantungan Koper',
                ],
            ],
        ];

        // --- Insert ke database ---
        foreach ($jenisPerlengkapan as $jenis) {
            $iconId = $icons[$jenis['icon']] ?? null;

            // Simpan jenis perlengkapan
            $jenisId = DB::table('jenis_perlengkapan_m')->insertGetId([
                'is_active' => true,
                'nama_jenis' => $jenis['nama_jenis'],
                'icon_id' => $iconId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Simpan setiap perlengkapan
            foreach ($jenis['perlengkapan'] as $item) {
                DB::table('perlengkapan_m')->insert([
                    'is_active' => true,
                    'jenis_perlengkapan_id' => $jenisId,
                    'nama_perlengkapan' => $item,
                    'deskripsi' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

    }
}
