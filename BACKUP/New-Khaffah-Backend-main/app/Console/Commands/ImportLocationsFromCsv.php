<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLocationsFromCsv extends Command
{
    protected $signature = 'locations:import-csv 
                            {path? : Path ke file CSV (default: locations.csv di root project)}';

    protected $description = 'Import/update data alamat (provinsi, kota, kecamatan) dari file locations.csv';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! $path) {
            $candidates = [
                base_path('locations.csv'),
                base_path('../../locations.csv'),
                dirname(base_path(), 2) . DIRECTORY_SEPARATOR . 'locations.csv',
            ];
            foreach ($candidates as $c) {
                if (is_file($c)) {
                    $path = $c;
                    break;
                }
            }
            if (! $path || ! is_file($path)) {
                $path = base_path('locations.csv');
            }
        }
        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $this->info("Membaca CSV: {$path}");
        $rows = $this->readCsv($path);
        if (empty($rows)) {
            $this->error('CSV kosong atau format salah.');
            return self::FAILURE;
        }

        $negaraIndonesiaId = DB::table('negara_m')->where('nama_negara', 'Indonesia')->value('id');
        if (! $negaraIndonesiaId) {
            $this->error('Negara "Indonesia" belum ada di database. Jalankan seed NegaraKotaSeeder dulu.');
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $provinsiCache = [];
        $kotaCache = [];
        $imported = ['provinsi' => 0, 'kota' => 0, 'kecamatan' => 0];

        foreach ($rows as $row) {
            $country = trim($row['Country'] ?? '');
            if (strtolower($country) !== 'indonesia') {
                $bar->advance();
                continue;
            }

            $state = trim($row['State'] ?? '');
            $city = trim($row['City'] ?? '');
            $district = trim($row['District'] ?? '');
            $zipCode = trim($row['Zip Code'] ?? '');

            if ($state === '' || $city === '' || $district === '') {
                $bar->advance();
                continue;
            }

            // Provinsi
            if (! isset($provinsiCache[$state])) {
                $prov = DB::table('provinsi_m')->where('nama_provinsi', $state)->first();
                if ($prov) {
                    $provinsiCache[$state] = $prov->id;
                } else {
                    $provinsiId = DB::table('provinsi_m')->insertGetId([
                        'nama_provinsi' => $state,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $provinsiCache[$state] = $provinsiId;
                    $imported['provinsi']++;
                }
            }
            $provinsiId = $provinsiCache[$state];

            // Kota (unik per provinsi)
            $kotaKey = "{$provinsiId}:{$city}";
            if (! isset($kotaCache[$kotaKey])) {
                $k = DB::table('kota_m')->where('provinsi_id', $provinsiId)->where('nama_kota', $city)->first();
                if ($k) {
                    $kotaCache[$kotaKey] = $k->id;
                } else {
                    $kotaId = DB::table('kota_m')->insertGetId([
                        'nama_kota' => $city,
                        'provinsi_id' => $provinsiId,
                        'negara_id' => $negaraIndonesiaId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $kotaCache[$kotaKey] = $kotaId;
                    $imported['kota']++;
                }
            }
            $kotaId = $kotaCache[$kotaKey];

            // Kecamatan (unik per kota)
            $exists = DB::table('kecamatan_m')
                ->where('kota_id', $kotaId)
                ->where('nama_kecamatan', $district)
                ->exists();

            if (! $exists) {
                $payload = [
                    'kota_id' => $kotaId,
                    'nama_kecamatan' => $district,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($zipCode !== '' && $this->hasKodePosColumn()) {
                    $payload['kode_pos'] = $zipCode;
                }
                DB::table('kecamatan_m')->insert($payload);
                $imported['kecamatan']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Import selesai.');
        $this->table(
            ['Data', 'Baru'],
            [
                ['Provinsi', $imported['provinsi']],
                ['Kota', $imported['kota']],
                ['Kecamatan', $imported['kecamatan']],
            ]
        );
        return self::SUCCESS;
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }
        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return [];
        }
        $header = array_map('trim', $header);
        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($header, array_pad(array_map('trim', $line), count($header), ''));
        }
        fclose($handle);
        return $rows;
    }

    private function hasKodePosColumn(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('kecamatan_m', 'kode_pos');
    }
}
