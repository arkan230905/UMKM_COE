<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Negative Stock Issue ===" . PHP_EOL;

// Current stock report shows negative values
echo "Current Issue - Stock Shows Negative:" . PHP_EOL;
echo "Kilogram: -110 kg (should be positive)" . PHP_EOL;
echo "Gram: -110.000 gram (should be positive)" . PHP_EOL;
echo "Potong: -330 Potong (should be positive)" . PHP_EOL;
echo "Ons: -1.100 Ons (should be positive)" . PHP_EOL;

// Check actual stock movements
echo PHP_EOL . "=== Stock Movements Analysis ===" . PHP_EOL;
$movements = DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->get();

$runningQty = 0;
echo "Stock Movement Log:" . PHP_EOL;
foreach ($movements as $movement) {
    echo date('Y-m-d', strtotime($movement->tanggal)) . " - " . $movement->ref_type . PHP_EOL;
    echo "  Direction: " . $movement->direction . PHP_EOL;
    echo "  Qty: " . $movement->qty . PHP_EOL;
    
    if ($movement->direction === 'in') {
        $runningQty += $movement->qty;
    } else {
        $runningQty -= $movement->qty;
    }
    
    echo "  Running Total: " . $runningQty . PHP_EOL;
    echo "---" . PHP_EOL;
}

echo PHP_EOL . "Expected Stock Calculation:" . PHP_EOL;
echo "Initial: 50 kg" . PHP_EOL;
echo "Production Usage: -40 kg" . PHP_EOL;
echo "Purchase: +50 kg" . PHP_EOL;
echo "Final: 50 - 40 + 50 = 60 kg" . PHP_EOL;

echo PHP_EOL . "The Issue:" . PHP_EOL;
echo "Stock report is showing: -110 kg" . PHP_EOL;
echo "Should be showing: 60 kg" . PHP_EOL;
echo "Difference: 170 kg (50 initial + 120 wrong calculation)" . PHP_EOL;
