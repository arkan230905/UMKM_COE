<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CEK STRUKTUR TABEL STOCK_LAYERS ===\n\n";

// Cek kolom yang ada
$columns = \Schema::getColumnListing('stock_layers');
echo "Kolom yang ada:\n";
foreach ($columns as $column) {
    echo "- {$column}\n";
}

echo "\n=== CEK DATA STOCK_LAYERS ===\n";
$stockLayers = \DB::table('stock_layers')->limit(5)->get();
foreach ($stockLayers as $layer) {
    echo "ID: {$layer->id}\n";
    echo "Item Type: {$layer->item_type}\n";
    echo "Item ID: {$layer->item_id}\n";
    echo "Remaining Qty: {$layer->remaining_qty}\n";
    echo "Unit Cost: {$layer->unit_cost}\n";
    echo "Batch: {$layer->batch_number}\n";
    echo "Created: {$layer->created_at}\n";
    echo "---\n";
}
