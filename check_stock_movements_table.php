<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Struktur Tabel Stock Movements ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('stock_movements');
echo "Kolom di tabel stock_movements:\n";
foreach ($columns as $col) {
    echo "- $col\n";
}

// Check existing data types
echo "\n=== Data yang Ada ===\n";
$movements = \App\Models\StockMovement::limit(3)->get();
foreach ($movements as $m) {
    echo "item_type: '{$m->item_type}' (length: " . strlen($m->item_type) . ")\n";
}
