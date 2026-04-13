<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Retur Penjualan Detail ===\n";

// 1. Check retur_penjualans table
echo "1. Checking retur_penjualans table...\n";
try {
    $returPenjualans = \DB::table('retur_penjualans')->get();
    echo "Retur penjualan records: " . $returPenjualans->count() . "\n";
    
    foreach ($returPenjualans as $retur) {
        echo "Retur Penjualan #{$retur->id}:\n";
        foreach ((array)$retur as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 2. Check detail_retur_penjualans table
echo "2. Checking detail_retur_penjualans table...\n";
try {
    $returDetails = \DB::table('detail_retur_penjualans')->get();
    echo "Detail retur penjualan records: " . $returDetails->count() . "\n";
    
    foreach ($returDetails as $detail) {
        echo "Detail #{$detail->id}:\n";
        foreach ((array)$detail as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 3. Check sales_returns table
echo "3. Checking sales_returns table...\n";
try {
    $salesReturns = \DB::table('sales_returns')->get();
    echo "Sales returns records: " . $salesReturns->count() . "\n";
    
    foreach ($salesReturns as $retur) {
        echo "Sales Return #{$retur->id}:\n";
        foreach ((array)$retur as $key => $value) {
            echo "  {$key}: {$value}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 4. Check current stock movements for Ayam Goreng Bundo
echo "4. Current stock calculation for Ayam Goreng Bundo:\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2) // Ayam Goreng Bundo
    ->orderBy('tanggal')
    ->get();

$runningStock = 0;
foreach ($stockMovements as $movement) {
    $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
    $runningStock += $change;
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} | Running: {$runningStock}\n";
}

echo "\nCurrent stock: {$runningStock} units\n";

echo "\n=== ANALYSIS ===\n";
echo "Current movements show:\n";
echo "- Production: +160 units\n";
echo "- Sale #4: -50 units\n";
echo "- Sale #5: -50 units\n";
echo "- Retur penjualan #2: +5 units (barang kembali)\n";
echo "- Current stock: {$runningStock} units\n";

echo "\nUser expects:\n";
echo "- Retur tukar barang should create OUT movement of 5 units (barang keluar untuk ganti)\n";
echo "- This would make current stock: " . ($runningStock - 5) . " units\n";

echo "\nMissing movement:\n";
echo "- Need: retur_tukar_barang | out 5.0000 | for replacement goods\n";