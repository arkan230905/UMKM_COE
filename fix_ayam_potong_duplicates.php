<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Ayam Potong Duplicates and Conversion ===\n";

// 1. Fix conversion ratio from 4 to 3
echo "1. Fixing conversion ratio...\n";
$ayamPotong = \App\Models\BahanBaku::find(1);
if ($ayamPotong) {
    echo "Current conversion: {$ayamPotong->sub_satuan_2_nilai} potong per kg\n";
    $ayamPotong->sub_satuan_2_nilai = 3.0000;
    $ayamPotong->save();
    echo "✅ Updated conversion to: 3 potong per kg\n";
}

// 2. Remove duplicate kartu_stok entries (keep only the first one)
echo "\n2. Removing duplicate kartu_stok entries...\n";
$kartuStokDuplicates = \DB::table('kartu_stok')
    ->where('item_type', 'bahan_baku')
    ->where('item_id', 1)
    ->where('ref_type', 'pembelian')
    ->where('ref_id', 2)
    ->orderBy('id')
    ->get();

echo "Found {$kartuStokDuplicates->count()} kartu_stok entries for ref_id 2\n";

if ($kartuStokDuplicates->count() > 1) {
    // Keep the first one, delete the rest
    $keepId = $kartuStokDuplicates->first()->id;
    $deleteIds = $kartuStokDuplicates->skip(1)->pluck('id')->toArray();
    
    echo "Keeping kartu_stok ID: {$keepId}\n";
    echo "Deleting kartu_stok IDs: " . implode(', ', $deleteIds) . "\n";
    
    $deleted = \DB::table('kartu_stok')->whereIn('id', $deleteIds)->delete();
    echo "✅ Deleted {$deleted} duplicate kartu_stok entries\n";
}

// 3. Remove duplicate stock_movements entries (keep only the first one)
echo "\n3. Removing duplicate stock_movements entries...\n";
$stockMovementDuplicates = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'purchase')
    ->where('ref_id', 2)
    ->orderBy('id')
    ->get();

echo "Found {$stockMovementDuplicates->count()} stock_movements entries for ref_id 2\n";

if ($stockMovementDuplicates->count() > 1) {
    // Keep the first one, delete the rest
    $keepId = $stockMovementDuplicates->first()->id;
    $deleteIds = $stockMovementDuplicates->skip(1)->pluck('id')->toArray();
    
    echo "Keeping stock_movements ID: {$keepId}\n";
    echo "Deleting stock_movements IDs: " . implode(', ', $deleteIds) . "\n";
    
    $deleted = \App\Models\StockMovement::whereIn('id', $deleteIds)->delete();
    echo "✅ Deleted {$deleted} duplicate stock_movements entries\n";
}

// 4. Update stock layers to reflect correct conversion
echo "\n4. Updating stock layers with correct conversion...\n";
$stockLayers = \App\Models\StockLayer::where('item_type', 'material')
    ->where('item_id', 1)
    ->get();

foreach ($stockLayers as $layer) {
    if ($layer->ref_type === 'purchase' && $layer->ref_id == 2) {
        // This should be 50 Kg = 150 Potong (50 * 3)
        $correctSubQty = $layer->qty * 3;
        echo "Updating stock layer ID {$layer->id}: {$layer->qty} Kg = {$correctSubQty} Potong\n";
        $layer->sub_qty = $correctSubQty;
        $layer->save();
    }
}

// 5. Verify final state
echo "\n=== Final Verification ===\n";

// Check kartu_stok
$finalKartuStok = \DB::table('kartu_stok')
    ->where('item_type', 'bahan_baku')
    ->where('item_id', 1)
    ->where('ref_type', 'pembelian')
    ->get();
echo "Final kartu_stok purchase entries: {$finalKartuStok->count()}\n";

// Check stock_movements
$finalStockMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'purchase')
    ->get();
echo "Final stock_movements purchase entries: {$finalStockMovements->count()}\n";

// Check current stock calculation
$currentStock = \App\Services\StockService::getCurrentStock('material', 1);
echo "Current stock: {$currentStock['qty']} Kg = {$currentStock['sub_qty']} Potong\n";

echo "\n✅ All fixes completed!\n";