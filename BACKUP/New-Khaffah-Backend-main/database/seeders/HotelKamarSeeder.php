<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HotelKamarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        // Ambil semua hotel yang aktif
        $hotels = DB::table('hotel_m')->where('is_active', true)->get();
        // Ambil tipe kamar yang ada
        $tipeKamar = DB::table('tipe_kamar_m')->get();

        $inserts = [];

        foreach ($hotels as $hotel) {
            foreach ($tipeKamar as $tk) {
                // Jangan buat duplicate karena ada unique constraint
                $exists = DB::table('hotel_kamar_m')
                    ->where('hotel_id', $hotel->id)
                    ->where('tipe_kamar_id', $tk->id)
                    ->exists();

                if ($exists) continue;

                $inserts[] = [
                    'hotel_id' => $hotel->id,
                    'tipe_kamar_id' => $tk->id,
                    'nama_kamar' => $tk->tipe_kamar . ' Room',
                    'kapasitas' => match (strtolower($tk->tipe_kamar)) {
                        'double' => 2,
                        'triple' => 3,
                        'quad' => 4,
                        'quint' => 5,
                        default => 1,
                    },
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($inserts)) {
            DB::table('hotel_kamar_m')->insert($inserts);
        }
    }
}
