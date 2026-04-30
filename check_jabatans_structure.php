<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Jabatans table structure...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('jabatans');
echo "Jabatans Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

$hasUserId = \Illuminate\Support\Facades\Schema::hasColumn('jabatans', 'user_id');
echo "\nHas user_id column: " . ($hasUserId ? "YES" : "NO") . "\n";

// Check if this table is used in DashboardController
echo "\nChecking DashboardController usage...\n";
$dashboardControllerPath = base_path('app/Http/Controllers/DashboardController.php');
if (file_exists($dashboardControllerPath)) {
    $content = file_get_contents($dashboardControllerPath);
    if (strpos($content, 'Jabatan::where(\'user_id\'') !== false) {
        echo "DashboardController IS using Jabatan::where('user_id') query\n";
    } else {
        echo "DashboardController usage not found\n";
    }
}

echo "\nJabatans structure check completed!\n";
