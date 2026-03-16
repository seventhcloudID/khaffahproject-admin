<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Checking User Login ===\n\n";

$email = 'admin@kaffah.com';
$password = 'password123';

$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "✅ User found: {$user->email}\n";
echo "   Name: {$user->nama_lengkap}\n";
echo "   Is Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
echo "   Roles: " . $user->getRoleNames()->implode(', ') . "\n";
echo "   Subroles: " . $user->subroles()->count() . "\n";

echo "\n=== Password Check ===\n";
$passwordMatch = Hash::check($password, $user->password);
echo "Password '{$password}' matches: " . ($passwordMatch ? '✅ YES' : '❌ NO') . "\n";

if (!$passwordMatch) {
    echo "\n⚠️  Password mismatch detected!\n";
    echo "Resetting password...\n";
    $user->password = Hash::make($password);
    $user->save();
    echo "✅ Password reset complete!\n";
}

echo "\n=== User Status ===\n";
if (!$user->is_active) {
    echo "⚠️  User is not active! Activating...\n";
    $user->is_active = true;
    $user->save();
    echo "✅ User activated!\n";
}

echo "\n=== Final Check ===\n";
$finalCheck = Hash::check($password, $user->password);
echo "Can login with '{$password}': " . ($finalCheck ? '✅ YES' : '❌ NO') . "\n";
