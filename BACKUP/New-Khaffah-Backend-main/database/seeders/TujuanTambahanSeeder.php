<?php

namespace Database\Seeders;

use App\Models\TujuanTambahan;
use Illuminate\Database\Seeder;

class TujuanTambahanSeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Dubai', 'Turki', 'Mesir', 'Yordania'];
        foreach ($items as $i => $nama) {
            TujuanTambahan::create([
                'nama'      => $nama,
                'urutan'    => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
