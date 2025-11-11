<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illware\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Check if migrations table exists
    $tables = DB::select('SHOW TABLES LIKE "migrations"');
    
    if (count($tables) > 0) {
        echo "Migrations table exists.\n";
        
        // Check if there are any migrations
        $migrations = DB::table('migrations')->count();
        echo "Number of migrations: $migrations\n";
        
        if ($migrations > 0) {
            $lastBatch = DB::table('migrations')->max('batch');
            echo "Last batch number: $lastBatch\n";
        }
    } else {
        echo "Migrations table does not exist.\n";
        
        // Try to create migrations table
        try {
            DB::statement('CREATE TABLE migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL
            )');
            echo "Migrations table created successfully.\n";
        } catch (Exception $e) {
            echo "Failed to create migrations table: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
