<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Dropping temporary tables...\n";

try {
    DB::statement('DROP TABLE IF EXISTS pegawais_new');
    echo "✓ Dropped pegawais_new\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    DB::statement('DROP TABLE IF EXISTS presensis_new');
    echo "✓ Dropped presensis_new\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
