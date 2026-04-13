<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing All Data Based on User's Correct Information ===\n";

// 1. Fix initial stock - add proper initial stock
echo "1. Adding proper initial stock...\n";
$existingInitial = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial')
    ->first();

if (!$existingInitial) {
    // Create initial stock movement
    $initialMovement = new \App\Models\StockMovement();
    $initialMovement->item_type = 'material';
    $initialMovement->item_id = 1;
    $initialMovement->tanggal = '2026-04-01';
    $initialMovement->qty = 50.0000;
    $initialMovement->direction = 'in';
    $initialMovement->ref_type = 'initial_stock';
    $initialMovement->ref_id = null;
    $initialMovement->save();
    echo "✅ Added initial stock: 50 kg\n";
} else {
    echo "Initial stock already exists\n";
}

// 2. Fix production usage from 40 kg to 53.33 kg (160 potong)
echo "\n2. Fixing production usage...\n";
$productionMovement = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'production')
    ->where('ref_id', 2)
    ->first();

if ($productionMovement) {
    $correctProductionQty = 160 / 3; // 160 potong = 53.33 kg
    echo "Updating production usage: {$productionMovement->qty} → {$correctProductionQty} kg\n";
    $productionMovement->qty = $correctProductionQty;
    $productionMovement->save();
    echo "✅ Production usage updated\n";
}

// 3. Fix retur amounts - should be 10 ekor = 8 kg and 1 ekor = 0.8 kg
echo "\n3. Fixing retur amounts...\n";

// Retur #1: 10 ekor = 8 kg
$retur1Movement = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'retur')
    ->where('ref_id', 1)
    ->first();

if ($retur1Movement) {
    $correctRetur1 = 10 * 0.8; // 10 ekor = 8 kg
    echo "Updating retur #1: {$retur1Movement->qty} → {$correctRetur1} kg\n";
    $retur1Movement->qty = $correctRetur1;
    $retur1Movement->save();
    echo "✅ Retur #1 updated\n";
}

// Retur #3: 1 ekor = 0.8 kg
$retur3Movement = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'retur')
    ->where('ref_id', 3)
    ->first();

if ($retur3Movement) {
    $correctRetur3 = 1 * 0.8; // 1 ekor = 0.8 kg
    echo "Updating retur #3: {$retur3Movement->qty} → {$correctRetur3} kg\n";
    $retur3Movement->qty = $correctRetur3;
    $retur3Movement->save();
    echo "✅ Retur #3 updated\n";
}

// 4. Update corresponding kartu_stok entries
echo "\n4. Updating kartu_stok entries...\n";

// Update production in kartu_stok
$productionKartu = \DB::table('kartu_stok')
    ->where('item_type', 'bahan_baku')
    ->where('item_id', 1)
    ->where('ref_type', 'produksi')
    ->where('ref_id', 2)
    ->first();

if ($productionKartu) {
    \DB::table('kartu_stok')
        ->where('id', $productionKartu->id)
        ->update(['qty_keluar' => 160/3]);
    echo "✅ Kartu stok production updated\n";
}

// Update retur in kartu_stok
$returKartus = \DB::table('kartu_stok')
    ->where('item_type', 'bahan_baku')
    ->where('item_id', 1)
    ->where('ref_type', 'retur')
    ->get();

foreach ($returKartus as $kartu) {
    if ($kartu->ref_id == 1) {
        \DB::table('kartu_stok')
            ->where('id', $kartu->id)
            ->update(['qty_keluar' => 8.0]);
        echo "✅ Kartu stok retur #1 updated\n";
    } elseif ($kartu->ref_id == 3) {
        \DB::table('kartu_stok')
            ->where('id', $kartu->id)
            ->update(['qty_keluar' => 0.8]);
        echo "✅ Kartu stok retur #3 updated\n";
    }
}

// 5. Final verification
echo "\n=== Final Verification ===\n";
$allMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->get();

$totalIn = 0;
$totalOut = 0;
echo "All movements:\n";
foreach ($allMovements as $movement) {
    echo "- {$movement->ref_type}#{$movement->ref_id}: {$movement->direction} {$movement->qty} kg\n";
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

echo "\nExpected based on user data:\n";
echo "- Initial: 50 kg\n";
echo "- Purchase: 40 kg\n";
echo "- Production: -53.33 kg (160 potong)\n";
echo "- Retur: -8.8 kg (11 ekor)\n";
echo "- Expected: 27.87 kg = 83.6 potong\n";

if (abs($finalStock - 27.87) < 0.1) {
    echo "\n🎉 SUCCESS! Stock calculation now matches user's data\n";
} else {
    echo "\n❌ Still some discrepancy: " . ($finalStock - 27.87) . " kg\n";
}