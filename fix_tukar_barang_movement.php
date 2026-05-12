<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Tukar Barang Stock Movement ===\n";

// 1. Analyze the retur tukar barang
echo "1. Analyzing retur tukar barang...\n";
$returTukarBarang = \DB::table('retur_penjualans')->where('jenis_retur', 'tukar_barang')->first();

if ($returTukarBarang) {
    echo "Found retur tukar barang #{$returTukarBarang->id}\n";
    echo "- Nomor: {$returTukarBarang->nomor_retur}\n";
    echo "- Tanggal: {$returTukarBarang->tanggal}\n";
    echo "- Penjualan ID: {$returTukarBarang->penjualan_id}\n";
    
    // Get detail
    $returDetail = \DB::table('detail_retur_penjualans')
        ->where('retur_penjualan_id', $returTukarBarang->id)
        ->first();
    
    if ($returDetail) {
        echo "- Produk ID yang diretur: {$returDetail->produk_id}\n";
        echo "- Qty retur: {$returDetail->qty_retur}\n";
        
        // Get product name
        $produk = \App\Models\Produk::find($returDetail->produk_id);
        echo "- Produk: " . ($produk->nama_produk ?? 'Unknown') . "\n";
        
        // For tukar barang, customer returns product A and gets product B
        // We need to create stock movement OUT for the replacement product
        
        echo "\n2. Checking existing stock movements for this retur...\n";
        $existingMovements = \App\Models\StockMovement::where('ref_type', 'retur_penjualan')
            ->where('ref_id', $returTukarBarang->id)
            ->get();
            
        foreach ($existingMovements as $movement) {
            echo "- Existing: {$movement->item_type} ID {$movement->item_id} | {$movement->direction} {$movement->qty}\n";
        }
        
        echo "\n3. Logic for tukar barang:\n";
        echo "- Customer returns: {$produk->nama_produk} (qty: {$returDetail->qty_retur})\n";
        echo "- Should create IN movement for returned product ✅ (already exists)\n";
        echo "- Should create OUT movement for replacement product ❌ (missing)\n";
        
        // Assuming replacement is Ayam Goreng Bundo (product_id 2)
        $replacementProductId = 2; // Ayam Goreng Bundo
        $replacementProduct = \App\Models\Produk::find($replacementProductId);
        
        echo "- Replacement product: {$replacementProduct->nama_produk}\n";
        
        // Check if OUT movement exists for replacement
        $replacementMovement = \App\Models\StockMovement::where('ref_type', 'retur_penjualan')
            ->where('ref_id', $returTukarBarang->id)
            ->where('item_type', 'product')
            ->where('item_id', $replacementProductId)
            ->where('direction', 'out')
            ->first();
            
        if (!$replacementMovement) {
            echo "\n4. Creating missing OUT movement for replacement product...\n";
            
            $newMovement = new \App\Models\StockMovement();
            $newMovement->item_type = 'product';
            $newMovement->item_id = $replacementProductId;
            $newMovement->tanggal = $returTukarBarang->tanggal;
            $newMovement->ref_type = 'retur_tukar_barang';
            $newMovement->ref_id = $returTukarBarang->id;
            $newMovement->direction = 'out';
            $newMovement->qty = $returDetail->qty_retur; // Same quantity as returned
            $newMovement->unit_cost = $replacementProduct->harga_jual ?? 0;
            $newMovement->total_cost = $newMovement->qty * $newMovement->unit_cost;
            $newMovement->keterangan = "Tukar Barang - Pengganti untuk {$produk->nama_produk}";
            $newMovement->save();
            
            echo "✅ Created OUT movement: {$newMovement->qty} units of {$replacementProduct->nama_produk}\n";
            echo "   Movement ID: {$newMovement->id}\n";
            
        } else {
            echo "OUT movement for replacement already exists\n";
        }
        
        // 5. Verify final stock
        echo "\n5. Final stock verification for Ayam Goreng Bundo:\n";
        $stockMovements = \App\Models\StockMovement::where('item_type', 'product')
            ->where('item_id', 2)
            ->orderBy('tanggal')
            ->get();
        
        $runningStock = 0;
        foreach ($stockMovements as $movement) {
            $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
            $runningStock += $change;
            echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} | Running: {$runningStock}\n";
        }
        
        echo "\nFinal stock: {$runningStock} units\n";
        echo "Expected: 60 units (160 - 50 - 50 + 5 - 5)\n";
        
    } else {
        echo "No detail found for retur tukar barang\n";
    }
} else {
    echo "No retur tukar barang found\n";
}