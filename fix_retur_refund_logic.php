<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Retur Refund Logic ===\n";

// 1. Check current retur data
echo "1. Checking current retur data...\n";
$returPenjualans = \DB::table('retur_penjualans')->get();

foreach ($returPenjualans as $retur) {
    echo "Retur #{$retur->id}: {$retur->jenis_retur}\n";
    
    // Get detail
    $detail = \DB::table('detail_retur_penjualans')
        ->where('retur_penjualan_id', $retur->id)
        ->first();
    
    if ($detail) {
        echo "- Produk ID: {$detail->produk_id}\n";
        echo "- Qty: {$detail->qty_retur}\n";
        
        // Check stock movement
        $stockMovement = \App\Models\StockMovement::where('ref_type', 'retur_penjualan')
            ->where('ref_id', $retur->id)
            ->first();
            
        if ($stockMovement) {
            echo "- Stock movement: {$stockMovement->direction} {$stockMovement->qty}\n";
            
            // If this is REFUND, the stock movement should NOT exist
            if ($retur->jenis_retur === 'refund') {
                echo "❌ PROBLEM: Refund should NOT create stock movement (barang cacat)\n";
                echo "   Deleting incorrect stock movement...\n";
                
                $stockMovement->delete();
                echo "✅ Deleted stock movement for refund\n";
            }
        }
    }
    echo "\n";
}

echo "2. Correct logic should be:\n";
echo "- Retur REFUND: NO stock movement (barang cacat, tidak masuk stok)\n";
echo "- Retur TUKAR BARANG: Stock movement IN untuk barang yang dikembalikan\n";

echo "\n3. Final verification...\n";
$finalMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('tanggal')
    ->get();

$runningStock = 0;
foreach ($finalMovements as $movement) {
    $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
    $runningStock += $change;
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} | Running: {$runningStock}\n";
}

echo "\nExpected final stock: 60 PCS (160 - 50 - 50 - 5 for tukar barang)\n";
echo "Actual final stock: {$runningStock} PCS\n";

if ($runningStock == 60) {
    echo "✅ Stock calculation is now correct!\n";
} else {
    echo "❌ Stock calculation still wrong\n";
}