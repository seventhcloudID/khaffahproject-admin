<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelMakkahSeeder extends Seeder
{
    /**
     * Run the database seeds. Kota = Makkah (nama_kota di kota_m).
     */
    public function run(): void
    {
        $kotaId = DB::table('kota_m')->where('nama_kota', 'Makkah')->value('id');
        if (!$kotaId) {
            return;
        }

        $now = Carbon::now();

        $hotels = [
            [
                'is_active' => true,
                'nama_hotel' => 'Al Ghufran Safwah',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Pulman Zam Zam',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Al Marwa Rayhaan',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Movenpick Makkah',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Al Safwa Tower',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Swissotel Makkah/Swiss Al Maqam',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Makkah Towers',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Le Meridien Towers',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Anjum',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 5.0,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Olayan Ajyad',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 4,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'is_active' => true,
                'nama_hotel' => 'Azka Al Maqam',
                'kota_id' => $kotaId,
                'jarak_ke_masjid' => '',
                'bintang' => 4,
                'alamat' => '',
                'jam_checkin_mulai' => null,
                'jam_checkin_berakhir' => null,
                'jam_checkout_mulai' => null,
                'jam_checkout_berakhir' => null,
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($hotels as $row) {
            $exists = DB::table('hotel_m')
                ->where('nama_hotel', $row['nama_hotel'])
                ->where('kota_id', $row['kota_id'])
                ->exists();
            if (!$exists) {
                DB::table('hotel_m')->insert($row);
            }
        }
    }
}
