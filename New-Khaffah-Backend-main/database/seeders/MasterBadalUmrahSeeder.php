<?php

namespace Database\Seeders;

use App\Models\MasterBadalUmrah;
use Illuminate\Database\Seeder;

class MasterBadalUmrahSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama_layanan' => 'Badal Umrah',
                'slug' => 'badal-umrah',
                'deskripsi' => 'Pelaksanaan ibadah Umrah atas nama orang lain (sesuai syariat).',
                'harga' => 350000,
                'urutan' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            MasterBadalUmrah::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}

