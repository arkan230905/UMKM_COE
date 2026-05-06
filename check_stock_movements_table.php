<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== CHECKING STOCK_MOVEMENTS TABLE STRUCTURE ===\n\n";

// Get column listing
$columns = Schema::getColumnListing('stock_movements');
echo "Columns in stock_movements table:\n";
foreach ($columns as $column) {
    echo "- {$column}\n";
}

// Get sample data
echo "\nSample data:\n";
$sample = DB::table('stock_movements')->first();
if ($sample) {
    foreach ((array)$sample as $key => $value) {
        echo "- {$key}: {$value}\n";
    }
} else {
    echo "No data found\n";
}

echo "\n=== CHECK COMPLETED ===\n";