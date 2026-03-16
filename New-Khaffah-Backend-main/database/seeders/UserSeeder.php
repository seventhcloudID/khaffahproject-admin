<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin utama dengan subrole superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@kaffah.com'],
            [
                'nama_lengkap'  => 'Super Admin',
                'jenis_kelamin' => 'laki-laki',
                'tgl_lahir'     => '1985-01-01',
                'no_handphone'  => '083456789012',
                'password'      => Hash::make('password123'),
            ]
        );
        $superadmin->assignRole('superadmin');
        
        // Assign subrole superadmin
        $superadminSubrole = DB::table('subrole_m')->where('nama_role', 'superadmin')->first();
        if ($superadminSubrole) {
            $superadmin->subroles()->sync([$superadminSubrole->id]);
        }

        $superadmin = User::firstOrCreate(
            ['email' => 'admin2@kaffah.com'],
            [
                'nama_lengkap'  => 'Super Admin 2',
                'jenis_kelamin' => 'laki-laki',
                'tgl_lahir'     => '1986-01-01',
                'no_handphone'  => '0834567890122',
                'password'      => Hash::make('password123'),
            ]
        );
        $superadmin->assignRole('superadmin');
        
        // Assign subrole superadmin
        $superadminSubrole = DB::table('subrole_m')->where('nama_role', 'superadmin')->first();
        if ($superadminSubrole) {
            $superadmin->subroles()->sync([$superadminSubrole->id]);
        }

        // Mitra
        $mitra = User::firstOrCreate(
            ['email' => 'siti@kaffah.com'],
            [
                'nama_lengkap'  => 'Siti Aminah',
                'jenis_kelamin' => 'perempuan',
                'tgl_lahir'     => '1990-03-15',
                'no_handphone'  => '082345678901',
                'password'      => Hash::make('password123'),
            ]
        );
        $mitra->assignRole('mitra');

        // User
        $user = User::firstOrCreate(
            ['email' => 'budi@kaffah.com'],
            [
                'nama_lengkap'  => 'Budi Santoso',
                'jenis_kelamin' => 'laki-laki',
                'tgl_lahir'     => '1995-05-20',
                'no_handphone'  => '081234567890',
                'password'      => Hash::make('password123'),
            ]
        );
        $user->assignRole('user');

        // ====================================================================================
        //  Generate Superadmin untuk setiap Subrole lainnya (kecuali superadmin)
        // ====================================================================================

        // Exclude 'superadmin' subrole karena sudah di-handle di atas
        $subroles = DB::table('subrole_m')
            ->where('nama_role', '!=', 'superadmin')
            ->get();

        foreach ($subroles as $subrole) {

            $email = 'admin.' . $subrole->nama_role . '@kaffah.com';

            $newSuperadmin = User::firstOrCreate(
                ['email' => $email],
                [
                    'nama_lengkap'  => ucfirst($subrole->nama_role) . ' Admin',
                    'jenis_kelamin' => 'laki-laki',
                    'tgl_lahir'     => '1990-01-01',
                    'no_handphone'  => '0812000000' . str_pad($subrole->id, 2, '0', STR_PAD_LEFT),
                    'password'      => Hash::make('password123'),
                ]
            );

            $newSuperadmin->assignRole('superadmin');
            $newSuperadmin->subroles()->sync([$subrole->id]);
        }
    }
}