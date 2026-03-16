<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password {email=admin@kaffah.com} {password=password123}';

    protected $description = 'Reset password akun admin untuk login. Contoh: php artisan admin:reset-password';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email [{$email}] tidak ditemukan.");
            $this->info('Buat dulu dengan: php artisan db:seed --class=UserSeeder');
            return self::FAILURE;
        }

        // Set password mentah; model User punya cast 'hashed' jadi akan di-hash sekali saja
        $user->password = $password;
        $user->save();

        $this->info("Password untuk [{$email}] berhasil direset.");
        $this->line('');
        $this->line("  Email    : {$email}");
        $this->line("  Password : {$password}");
        $this->line('');
        $this->info('Silakan login di admin dengan kredensial di atas.');

        return self::SUCCESS;
    }
}
