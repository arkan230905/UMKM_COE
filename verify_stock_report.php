<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFYING STOCK REPORT SALDO AWAL ===" . PHP_EOL;

// Test the controller logic for Jagung
$controller = new \App\Http\Controllers\LaporanController();

// Simulate the request for Jagung stock report
$request = new \Illuminate\Http\Request([
    'item_id' => 1,
    'tipe' => 'material'
]);

echo "Testing stock report for Jagung..." . PHP_EOL;

// Check if initial stock movement exists and has correct values
$initialStock = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial_stock')
    ->first();

if ($initialStock) {
    echo "✅ Initial stock movement found:" . PHP_EOL;
    echo "  - Qty: {$initialStock->qty} kg" . PHP_EOL;
    echo "  - Unit Cost: Rp " . number_format($initialStock->unit_cost, 0, ',', '.') . PHP_EOL;
    echo "  - Total Cost: Rp " . number_format($initialStock->total_cost, 0, ',', '.') . PHP_EOL;
    echo "  - Date: {$initialStock->tanggal}" . PHP_EOL;
} else {
    echo "❌ Initial stock movement not found!" . PHP_EOL;
}

echo PHP_EOL . "Expected in stock report:" . PHP_EOL;
echo "- Stok Awal column should show: 12 kg" . PHP_EOL;
echo "- Harga column should show: Rp 50.000" . PHP_EOL;
echo "- Total column should show: Rp 600.000" . PHP_EOL;

echo PHP_EOL . "=== STOCK MOVEMENTS SUMMARY ===" . PHP_EOL;
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

foreach ($movements as $movement) {
    echo "Date: {$movement->tanggal} | {$movement->direction} {$movement->qty} kg | Type: {$movement->ref_type}" . PHP_EOL;
}