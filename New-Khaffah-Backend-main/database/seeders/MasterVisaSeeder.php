<?php

namespace Database\Seeders;

use App\Models\MasterVisa;
use Illuminate\Database\Seeder;

class MasterVisaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'nama_layanan' => 'Visa',
                'slug' => 'visa',
                'deskripsi' => 'Pengurusan visa umrah resmi untuk masuk Arab Saudi.',
                'harga' => 2000000,
                'urutan' => 1,
                'is_active' => true,
            ],
            [
                'nama_layanan' => 'Visa & Transportasi',
                'slug' => 'visa-transportasi',
                'deskripsi' => 'Pengurusan visa plus transportasi ke bandara',
                'harga' => 2500000,
                'urutan' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            MasterVisa::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
