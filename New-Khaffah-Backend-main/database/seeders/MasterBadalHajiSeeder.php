<?php

namespace Database\Seeders;

use App\Models\MasterBadalHaji;
use Illuminate\Database\Seeder;

class MasterBadalHajiSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama_layanan' => 'Badal Haji',
                'slug' => 'badal-haji',
                'deskripsi' => 'Pelaksanaan ibadah Haji atas nama orang lain (sesuai syariat).',
                'harga' => 0,
                'urutan' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            MasterBadalHaji::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
