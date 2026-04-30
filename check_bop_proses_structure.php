<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking BOP Proses table structure...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('bop_proses');
echo "BOP Proses Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

$hasKeterangan = \Illuminate\Support\Facades\Schema::hasColumn('bop_proses', 'keterangan');
echo "\nHas keterangan column: " . ($hasKeterangan ? "YES" : "NO") . "\n";

// Check if this table is used in BOP Controller
echo "\nChecking BOP usage...\n";
$bopControllerPath = base_path('app/Http/Controllers/BopController.php');
if (file_exists($bopControllerPath)) {
    $content = file_get_contents($bopControllerPath);
    if (strpos($content, 'keterangan') !== false) {
        echo "BOPController IS using keterangan field\n";
    } else {
        echo "Checking BOPController usage...\n";
    }
}

echo "\nBOP Proses structure check completed!\n";
