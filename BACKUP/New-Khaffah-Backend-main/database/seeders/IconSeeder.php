<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Icon;

class IconSeeder extends Seeder
{
    public function run()
    {
        $iconPath = storage_path('app/public/icon');

        if (!File::exists($iconPath)) {
            throw new \Exception("Folder '$iconPath' tidak ditemukan!");
        }

        $files = File::files($iconPath);
        $now = now();

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $kode = Str::slug($name, '-');

            Icon::updateOrCreate(
                ['kode' => $kode],
                [
                    'nama_icon' => $name,
                    'url' => "/storage/icon/{$filename}",
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
