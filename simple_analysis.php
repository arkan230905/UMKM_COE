<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Simple Analysis - User's Correct Data ===\n";

echo "USER'S CORRECT DATA:\n";
echo "- Initial stock: 50 kg (from master data)\n";
echo "- Purchase: 50 ekor = 40 kg\n";
echo "- Total available: 90 kg = 270 potong\n";
echo "- Production used: 160 potong = 53.33 kg\n";
echo "- Retur: 10 ekor + 1 ekor = 11 ekor = 8.8 kg\n";
echo "- Expected remaining: 90 - 53.33 - 8.8 = 27.87 kg = 83.6 potong\n";

echo "\nSYSTEM CURRENT DATA:\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->get();

$totalIn = 0;
$totalOut = 0;
foreach ($stockMovements as $movement) {
    echo "- {$movement->ref_type}#{$movement->ref_id}: {$movement->direction} {$movement->qty} kg\n";
    if ($movement->direction === 'in') {
        $totalIn += $movement->qty;
    } else {
        $totalOut += $movement->qty;
    }
}

$currentStock = $totalIn - $totalOut;
echo "System shows: {$currentStock} kg = " . ($currentStock * 3) . " potong\n";

echo "\n=== CORRECTIONS NEEDED ===\n";

// 1. Fix initial stock
$currentInitial = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial')
    ->sum('qty');

if ($currentInitial == 0) {
    echo "1. ❌ Missing initial stock - need to add 50 kg\n";
} else {
    echo "1. Initial stock exists: {$currentInitial} kg\n";
}

// 2. Fix production usage
$currentProduction = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'production')
    ->where('direction', 'out')
    ->sum('qty');

$correctProduction = 160 / 3; // 160 potong = 53.33 kg
echo "2. Production usage: {$currentProduction} kg (should be {$correctProduction} kg)\n";

// 3. Check retur amounts
$returMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'retur')
    ->get();

$totalRetur = 0;
foreach ($returMovements as $retur) {
    echo "3. Retur #{$retur->ref_id}: {$retur->qty} kg\n";
    $totalRetur += $retur->qty;
}

$correctRetur = 11 * 0.8; // 11 ekor = 8.8 kg
echo "   Total retur: {$totalRetur} kg (should be {$correctRetur} kg)\n";

echo "\n=== FIXING PLAN ===\n";
echo "Need to:\n";
if ($currentInitial == 0) {
    echo "1. Add initial stock: 50 kg\n";
}
if (abs($currentProduction - $correctProduction) > 1) {
    echo "2. Fix production usage: {$currentProduction} → {$correctProduction} kg\n";
}
if (abs($totalRetur - $correctRetur) > 1) {
    echo "3. Fix retur amounts: {$totalRetur} → {$correctRetur} kg\n";
}

// Calculate what the final stock should be
$correctTotal = 50 + 40 - $correctProduction - $correctRetur;
echo "\nFinal stock should be: {$correctTotal} kg = " . ($correctTotal * 3) . " potong\n";