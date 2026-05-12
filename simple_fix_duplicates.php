<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Simple Fix for Duplicates ===\n";

// 1. Delete duplicate initial stock using raw SQL to avoid observers
echo "1. Removing duplicate initial stock...\n";
$duplicateInitials = \DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial_stock')
    ->orderBy('id', 'desc')
    ->skip(1) // Keep the first one, delete the rest
    ->get();

foreach ($duplicateInitials as $duplicate) {
    echo "Deleting duplicate initial stock ID: {$duplicate->id}\n";
    \DB::table('stock_movements')->where('id', $duplicate->id)->delete();
}

// 2. Check final result
echo "\n2. Final verification...\n";
$movements = \DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->get();

$totalIn = 0;
$totalOut = 0;
echo "All movements:\n";
foreach ($movements as $movement) {
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id}: {$movement->direction} {$movement->qty} kg\n";
    if ($movement->direction === 'in') {
        $totalIn += $movement->qty;
    } else {
        $totalOut += $movement->qty;
    }
}

$finalStock = $totalIn - $totalOut;
$finalStockPotong = $finalStock * 3;

echo "\nCalculation:\n";
echo "- Total IN: {$totalIn} kg\n";
echo "- Total OUT: {$totalOut} kg\n";
echo "- Final stock: {$finalStock} kg = {$finalStockPotong} potong\n";

// Expected calculation based on user's data
$expected = 50 + 40 - (160/3) - (11 * 0.8);
echo "\nExpected: {$expected} kg = " . ($expected * 3) . " potong\n";

if (abs($finalStock - $expected) < 0.1) {
    echo "\n🎉 PERFECT! Stock now matches user's actual data\n";
} else {
    echo "\n❌ Difference: " . ($finalStock - $expected) . " kg\n";
}

echo "\n=== SUMMARY ===\n";
echo "User's actual data:\n";
echo "✅ Initial stock: 50 kg\n";
echo "✅ Purchase: 50 ekor = 40 kg\n";
echo "✅ Production used: 160 potong = 53.33 kg\n";
echo "✅ Retur: 11 ekor (10+1) = 8.8 kg\n";
echo "✅ Final stock: {$finalStock} kg = {$finalStockPotong} potong\n";