<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Ayam Goreng Bundo Stock Movements ===\n";

// 1. Find Ayam Goreng Bundo product
echo "1. Finding Ayam Goreng Bundo product...\n";
$ayamGorengBundo = \App\Models\Produk::where('nama_produk', 'like', '%Ayam Goreng Bundo%')->first();

if (!$ayamGorengBundo) {
    $ayamGorengBundo = \App\Models\Produk::where('nama_produk', 'like', '%Ayam%')->where('nama_produk', 'like', '%Bundo%')->first();
}

if ($ayamGorengBundo) {
    echo "Found: {$ayamGorengBundo->nama_produk} (ID: {$ayamGorengBundo->id})\n";
} else {
    echo "Product not found, listing all products:\n";
    $products = \App\Models\Produk::all();
    foreach ($products as $product) {
        echo "- ID: {$product->id}, Name: {$product->nama_produk}\n";
    }
    return;
}

// 2. Check stock movements for this product
echo "\n2. Stock movements for Ayam Goreng Bundo:\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', $ayamGorengBundo->id)
    ->orderBy('tanggal')
    ->get();

if ($stockMovements->count() > 0) {
    foreach ($stockMovements as $movement) {
        echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} | {$movement->keterangan}\n";
    }
} else {
    echo "No stock movements found for Ayam Goreng Bundo\n";
}

// 3. Check sales returns (retur penjualan)
echo "\n3. Checking sales returns (retur penjualan)...\n";
try {
    $salesReturns = \DB::table('sales_returns')->get();
    echo "Sales returns found: " . $salesReturns->count() . "\n";
    
    foreach ($salesReturns as $retur) {
        echo "Retur #{$retur->id}:\n";
        foreach ((array)$retur as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Sales returns table not found or error: " . $e->getMessage() . "\n";
}

// 4. Check if there are any retur records that mention tukar barang
echo "\n4. Checking for retur tukar barang records...\n";
try {
    // Check purchase returns first
    $purchaseReturns = \App\Models\PurchaseReturn::where('jenis_retur', 'tukar_barang')->get();
    echo "Purchase returns with tukar_barang: " . $purchaseReturns->count() . "\n";
    
    foreach ($purchaseReturns as $retur) {
        echo "Purchase Retur #{$retur->id}: {$retur->jenis_retur}, Jumlah: {$retur->jumlah}\n";
    }
    
    // Check if there are other retur tables
    $tables = \DB::select('SHOW TABLES');
    echo "\nAvailable tables with 'retur' in name:\n";
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        if (strpos($tableName, 'retur') !== false || strpos($tableName, 'return') !== false) {
            echo "- {$tableName}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error checking retur records: " . $e->getMessage() . "\n";
}

// 5. Check production records to see if Ayam Goreng Bundo was produced
echo "\n5. Checking production records...\n";
try {
    $productions = \App\Models\ProsesProduksi::all();
    echo "Production records found: " . $productions->count() . "\n";
    
    foreach ($productions as $production) {
        echo "Production #{$production->id}: {$production->tanggal}\n";
        
        // Check if this production created Ayam Goreng Bundo
        $productionMovement = \App\Models\StockMovement::where('item_type', 'product')
            ->where('item_id', $ayamGorengBundo->id)
            ->where('ref_type', 'production')
            ->where('ref_id', $production->id)
            ->first();
            
        if ($productionMovement) {
            echo "  - Produced {$productionMovement->qty} units of Ayam Goreng Bundo\n";
        }
    }
} catch (Exception $e) {
    echo "Error checking production: " . $e->getMessage() . "\n";
}

echo "\n=== ANALYSIS ===\n";
echo "User says there should be a retur tukar barang of 5 units Ayam Goreng Bundo.\n";
echo "This should create a stock movement OUT of 5 units for the product.\n";
echo "If this movement is missing, we need to:\n";
echo "1. Find the retur tukar barang record\n";
echo "2. Create the corresponding stock movement\n";
echo "3. Update the laporan stok to show this transaction\n";