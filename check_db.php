<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Load environment variables
$app->loadEnvironmentFrom('.env');

// Bootstrap the application
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::connection()->getPdo();
    echo "Connected successfully to: " . DB::connection()->getDatabaseName() . "\n";
    
    // Check if migrations table exists
    $tables = DB::select('SHOW TABLES');
    echo "Tables in database: " . count($tables) . "\n";
    
    if (count($tables) > 0) {
        echo "First table: " . json_encode($tables[0]) . "\n";
    }
    
} catch (\Exception $e) {
    die("Could not connect to the database. Error: " . $e->getMessage() . "\n");
}
