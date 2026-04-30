<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking COA table constraints...\n";

// Get table constraints
$constraints = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM coas WHERE Non_unique = 0");

echo "Current unique constraints:\n";
foreach ($constraints as $constraint) {
    echo "  - {$constraint->Key_name}: " . $constraint->Column_name . "\n";
}

echo "\nCOA constraints check completed!\n";
