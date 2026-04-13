<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Duplicate Initial Stock ===\n";

// 1. Check all initial stock movements
$initialMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial_stock')
    ->get();

echo "Found {$initialMovements->count()} initial stock movements:\n";
foreach ($initialMovements as $movement) {
    echo "- ID: {$movement->id}, Qty: {$movement->qty} kg, Date: {$movement->tanggal}\n";
}

// 2. Remove duplicates, keep only one
if ($initialMovements->count() > 1) {
    echo "\nRemoving duplicate initial stock movements...\n";
    $keepFirst = $initialMovements->first();
    $duplicates = $initialMovements->skip(1);
    
    foreach ($duplicates as $duplicate) {
        echo "Deleting duplicate ID: {$duplicate->id}\n";
        $duplicate->delete();
    }
    echo "✅ Duplicates removed\n";
}

// 3. Also check if there are duplicate initial movements with different ref_type
$otherInitials = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial')
    ->get();

if ($otherInitials->count() > 0) {
    echo "\nFound other initial movements with ref_type 'initial':\n";
    foreach ($otherInitials as $movement) {
        echo "- ID: {$movement->id}, Qty: {$movement->qty} kg\n";
        echo "Deleting this duplicate...\n";
        $movement->delete();
    }
}

// 4. Final verification
echo "\n=== Final Verification After Cleanup ===\n";
$allMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->get();

$totalIn = 0;
$totalOut = 0;
echo "All movements after cleanup:\n";
foreach ($allMovements as $movement) {
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id}: {$movement->direction} {$movement->qty} kg\n";
    if ($movement->direction === 'in') {
        $totalIn += $movement->qty;
    } else {
        $totalOut += $movement->qty;
    }
}

$finalStock = $totalIn - $totalOut;
$finalStockPotong = $finalStock * 3;

echo "\nFinal calculation:\n";
echo "- Total IN: {$totalIn} kg\n";
echo "- Total OUT: {$totalOut} kg\n";
echo "- Final stock: {$finalStock} kg = {$finalStockPotong} potong\n";

echo "\nUser's expected:\n";
echo "- Initial: 50 kg\n";
echo "- Purchase: 40 kg\n";
echo "- Available: 90 kg\n";
echo "- Production used: 53.33 kg (160 potong)\n";
echo "- Retur: 8.8 kg (11 ekor)\n";
echo "- Should remain: 27.87 kg = 83.6 potong\n";

$expectedStock = 50 + 40 - 53.33 - 8.8;
if (abs($finalStock - $expectedStock) < 0.1) {
    echo "\n🎉 SUCCESS! Stock calculation now matches user's data perfectly!\n";
    echo "✅ Ayam Potong stock is now correct: {$finalStock} kg = {$finalStockPotong} potong\n";
} else {
    echo "\n❌ Still discrepancy: " . ($finalStock - $expectedStock) . " kg\n";
}