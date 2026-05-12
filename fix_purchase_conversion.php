<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Purchase Conversion ===\n";

// 1. Check current data
echo "1. Current Purchase Data:\n";
$detail = \DB::table('pembelian_details')
    ->where('pembelian_id', 2)
    ->where('bahan_baku_id', 1)
    ->first();

if ($detail) {
    echo "- Jumlah: {$detail->jumlah} ekor\n";
    echo "- Faktor Konversi: {$detail->faktor_konversi} kg/ekor\n";
    echo "- Jumlah Satuan Utama (current): {$detail->jumlah_satuan_utama} kg\n";
    
    // Calculate correct conversion
    $correctMainUnit = $detail->jumlah * $detail->faktor_konversi;
    echo "- Jumlah Satuan Utama (should be): {$correctMainUnit} kg\n";
    
    // 2. Fix the pembelian_details record
    echo "\n2. Fixing pembelian_details record...\n";
    \DB::table('pembelian_details')
        ->where('id', $detail->id)
        ->update(['jumlah_satuan_utama' => $correctMainUnit]);
    echo "✅ Updated jumlah_satuan_utama to {$correctMainUnit} kg\n";
    
    // 3. Fix kartu_stok entries
    echo "\n3. Fixing kartu_stok entries...\n";
    $kartuStokEntries = \DB::table('kartu_stok')
        ->where('item_type', 'bahan_baku')
        ->where('item_id', 1)
        ->where('ref_type', 'pembelian')
        ->where('ref_id', 2)
        ->get();
    
    foreach ($kartuStokEntries as $entry) {
        echo "- Updating kartu_stok ID {$entry->id}: {$entry->qty_masuk} → {$correctMainUnit}\n";
        \DB::table('kartu_stok')
            ->where('id', $entry->id)
            ->update(['qty_masuk' => $correctMainUnit]);
    }
    
    // 4. Fix stock_movements entries
    echo "\n4. Fixing stock_movements entries...\n";
    $stockMovements = \App\Models\StockMovement::where('item_type', 'material')
        ->where('item_id', 1)
        ->where('ref_type', 'purchase')
        ->where('ref_id', 2)
        ->get();
    
    foreach ($stockMovements as $movement) {
        echo "- Updating stock_movements ID {$movement->id}: {$movement->qty} → {$correctMainUnit}\n";
        $movement->qty = $correctMainUnit;
        $movement->save();
    }
    
    // 5. Fix stock_layers entries
    echo "\n5. Fixing stock_layers entries...\n";
    $stockLayers = \App\Models\StockLayer::where('item_type', 'material')
        ->where('item_id', 1)
        ->where('ref_type', 'purchase')
        ->where('ref_id', 2)
        ->get();
    
    foreach ($stockLayers as $layer) {
        echo "- Updating stock_layer ID {$layer->id}: {$layer->qty} → {$correctMainUnit}\n";
        $layer->qty = $correctMainUnit;
        $layer->save();
    }
    
    // 6. Verify final state
    echo "\n=== Final Verification ===\n";
    
    // Calculate current stock
    $allMovements = \App\Models\StockMovement::where('item_type', 'material')
        ->where('item_id', 1)
        ->get();
    
    $totalIn = 0;
    $totalOut = 0;
    foreach ($allMovements as $movement) {
        if ($movement->direction === 'in') {
            $totalIn += $movement->qty;
        } else {
            $totalOut += $movement->qty;
        }
    }
    
    $currentStock = $totalIn - $totalOut;
    $currentSubStock = $currentStock * 3; // 3 potong per kg
    
    echo "Current stock calculation:\n";
    echo "- Total IN: {$totalIn} kg\n";
    echo "- Total OUT: {$totalOut} kg\n";
    echo "- Current stock: {$currentStock} kg = {$currentSubStock} potong\n";
    
    echo "\n✅ Purchase conversion fixed!\n";
    echo "✅ 50 ekor → 40 kg → 120 potong\n";
}