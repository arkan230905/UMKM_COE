<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Read SQL file
$sql = file_get_contents('insert_payroll_direct.sql');

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            DB::statement($statement);
            echo "✓ Executed\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nDone!\n";
