<?php

namespace Database\Seeders;

use App\Models\PaketEdutrip;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SubroleSeeder::class,
            UserSeeder::class,
            ModulAplikasiSeeder::class,
            SubroleModulAplikasiSeeder::class,
            MaskapaiSeeder::class,
            BandaraKeberangkatanSeeder::class,
            BandaraKepulanganSeeder::class,
            NegaraKotaSeeder::class,
            HotelMakkahSeeder::class,
            HotelMadinahSeeder::class,
            MusimSeeder::class,
            KelasPenerbanganSeeder::class,
            TipeKamarSeeder::class,
            HotelKamarSeeder::class,
            PaketHajiSeeder::class,
            JenisTransaksiSeeder::class,
            StatusPembayaranSeeder::class,
            StatusTransaksiSeeder::class,
            StatusSeeder::class,
            MitraLevelSeeder::class,
            MasterVisaSeeder::class,
            MasterBadalUmrahSeeder::class,
            MasterBadalHajiSeeder::class,
            GelarSeeder::class,
            PaketEdutripSeeder::class,
            IconSeeder::class,
            FasilitasSeeder::class,
            PerlengkapanSeeder::class,
            PaketUmrahSeeder::class,
            JamaahSeeder::class,
            TransaksiUmrahSeeder::class,
            TipeOwnerSeeder::class,
            TipeDokumenSeeder::class,
            LayananPaketRequestSeeder::class,
            TujuanTambahanSeeder::class,
        ]);
    }
}
