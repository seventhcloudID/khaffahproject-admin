<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Testing Database Connection ===\n\n";

try {
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connection successful!\n";
    echo "   Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "   Server: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    // Test query
    $result = DB::select('SELECT version()');
    echo "   PostgreSQL Version: " . $result[0]->version . "\n";
    
    // Test users table
    $userCount = DB::table('users')->count();
    echo "   Users in database: $userCount\n";
    
} catch (\Exception $e) {
    echo "❌ Database connection failed!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Possible solutions:\n";
    echo "1. Check if PostgreSQL service is running\n";
    echo "2. Check if extension pdo_pgsql is enabled in php.ini\n";
    echo "3. Verify database credentials in .env file\n";
    exit(1);
}
