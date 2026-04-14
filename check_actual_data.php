<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Actual Data - Ayam Potong ===\n";

// 1. Check all stock movements for Ayam Potong
echo "1. All Stock Movements for Ayam Potong:\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

$runningBalance = 0;
foreach ($stockMovements as $movement) {
    $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
    $runningBalance += $change;
    
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | ";
    echo "{$movement->direction} {$movement->qty} kg | Balance: {$runningBalance} kg\n";
}

// 2. Check retur pembelian data
echo "\n2. Retur Pembelian Data:\n";
$returs = \App\Models\PurchaseReturn::all();
foreach ($returs as $retur) {
    echo "Retur #{$retur->id}:\n";
    echo "- Pembelian ID: {$retur->pembelian_id}\n";
    echo "- Jenis: {$retur->jenis_retur}\n";
    echo "- Jumlah: {$retur->jumlah}\n";
    echo "- Satuan: {$retur->satuan}\n";
    echo "- Status: {$retur->status}\n";
    echo "- Tanggal: {$retur->created_at}\n\n";
}

// 3. Check production usage
echo "3. Production Usage:\n";
$productionMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'production')
    ->get();

foreach ($productionMovements as $movement) {
    echo "- Production #{$movement->ref_id}: {$movement->qty} kg ({$movement->direction})\n";
}

// 4. Calculate correct stock
echo "\n4. Manual Calculation:\n";
echo "Starting from purchase data:\n";

// Get purchase data
$pembelianDetail = \DB::table('pembelian_details')
    ->where('pembelian_id', 2)
    ->where('bahan_baku_id', 1)
    ->first();

echo "Purchase: {$pembelianDetail->jumlah} ekor = {$pembelianDetail->jumlah_satuan_utama} kg\n";

// Get initial stock
$initialStock = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial')
    ->sum('qty');
echo "Initial stock: {$initialStock} kg\n";

// Calculate total available
$totalAvailable = $initialStock + $pembelianDetail->jumlah_satuan_utama;
echo "Total available: {$totalAvailable} kg\n";

// Convert to potong for user's reference
$totalAvailablePotong = $totalAvailable * 3;
echo "Total available: {$totalAvailablePotong} potong\n";

echo "\nUser says:\n";
echo "- Production used: 160 potong = " . (160/3) . " kg\n";
echo "- Retur: 10 ekor + 1 ekor = 11 ekor = " . (11 * 0.8) . " kg\n";

$expectedUsage = (160/3) + (11 * 0.8);
$expectedRemaining = $totalAvailable - $expectedUsage;
$expectedRemainingPotong = $expectedRemaining * 3;

echo "\nExpected calculation:\n";
echo "- Total usage: {$expectedUsage} kg\n";
echo "- Remaining stock: {$expectedRemaining} kg = {$expectedRemainingPotong} potong\n";

echo "\nActual system shows: {$runningBalance} kg = " . ($runningBalance * 3) . " potong\n";

if (abs($runningBalance - $expectedRemaining) > 0.1) {
    echo "❌ MISMATCH! System calculation is wrong\n";
    echo "Difference: " . ($runningBalance - $expectedRemaining) . " kg\n";
} else {
    echo "✅ System calculation matches expected\n";
}

// 5. Check retur conversion
echo "\n5. Retur Conversion Check:\n";
foreach ($returs as $retur) {
    if ($retur->satuan == 'ekor' || $retur->satuan == '7') {
        $returKg = $retur->jumlah * 0.8; // Convert ekor to kg
        echo "Retur #{$retur->id}: {$retur->jumlah} ekor = {$returKg} kg\n";
        
        // Check if stock movement matches
        $returMovement = \App\Models\StockMovement::where('ref_type', 'retur')
            ->where('ref_id', $retur->id)
            ->where('item_id', 1)
            ->first();
        
        if ($returMovement) {
            echo "  Stock movement: {$returMovement->qty} kg\n";
            if (abs($returMovement->qty - $returKg) > 0.1) {
                echo "  ❌ Retur conversion wrong in stock movement\n";
            } else {
                echo "  ✅ Retur conversion correct\n";
            }
        } else {
            echo "  ❌ No stock movement found for this retur\n";
        }
    }
}