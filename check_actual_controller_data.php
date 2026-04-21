<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Actual Controller Data ===" . PHP_EOL;

// Simulate what the controller actually sends to view
echo "Simulating LaporanController::stok method for Ayam Potong..." . PHP_EOL;

// Get the actual stock movements
$movements = DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->get();

echo "Stock Movements Found:" . PHP_EOL;
foreach ($movements as $m) {
    echo date('Y-m-d', strtotime($m->tanggal)) . " - " . $m->ref_type . " - " . $m->direction . PHP_EOL;
    echo "  qty: " . $m->qty . PHP_EOL;
    echo "  qty_as_input: " . ($m->qty_as_input ?? 'NULL') . PHP_EOL;
    echo "  satuan_as_input: " . ($m->satuan_as_input ?? 'NULL') . PHP_EOL;
    
    // Simulate controller logic for this movement
    if ($m->direction === 'out') {
        if ($m->ref_type === 'production' && $m->qty_as_input && $m->satuan_as_input) {
            // NEW LOGIC - use converted qty
            $dailyOutQty = (float)$m->qty; // Should be 40
            echo "  Controller sends produksi_qty: " . $dailyOutQty . PHP_EOL;
        } else {
            $dailyOutQty = (float)$m->qty;
            echo "  Controller sends produksi_qty (fallback): " . $dailyOutQty . PHP_EOL;
        }
    }
    echo "---" . PHP_EOL;
}

// Check what the view actually receives
echo PHP_EOL . "=== What View Receives ===" . PHP_EOL;
echo "For production movement:" . PHP_EOL;
echo "produksi_qty: " . $movements[0]->qty . " (should be 40)" . PHP_EOL;
echo "qty_as_input: " . $movements[0]->qty_as_input . " (should be 160)" . PHP_EOL;
echo "satuan_as_input: " . $movements[0]->satuan_as_input . PHP_EOL;

// Check if there might be a condition that sets qty to 0
echo PHP_EOL . "=== Possible Zero Conditions ===" . PHP_EOL;
echo "1. Is produksi_qty actually 0? " . ($movements[0]->qty == 0 ? "YES" : "NO") . PHP_EOL;
echo "2. Is qty_as_input null? " . (is_null($movements[0]->qty_as_input) ? "YES" : "NO") . PHP_EOL;
echo "3. Is satuan_as_input null? " . (is_null($movements[0]->satuan_as_input) ? "YES" : "NO") . PHP_EOL;

// Test the actual condition
$testCondition = ($movements[0]->ref_type === 'production' && $movements[0]->qty_as_input && $movements[0]->satuan_as_input);
echo "4. Condition passes? " . ($testCondition ? "YES" : "NO") . PHP_EOL;
