<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Laporan Stok Issue - Ayam Goreng Bundo ===\n";

// Get all stock movements for Ayam Goreng Bundo
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2) // Ayam Goreng Bundo
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

echo "1. Raw stock movements from database:\n";
$runningStock = 0;
foreach ($stockMovements as $movement) {
    $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
    $runningStock += $change;
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} | Running: {$runningStock} | {$movement->keterangan}\n";
}

echo "\n2. Expected laporan stok rows:\n";
echo "Row 1: Production - 160 PCS (IN) → Saldo: 160 PCS\n";
echo "Row 2: Sale #4 - 50 PCS (OUT) → Saldo: 110 PCS\n";
echo "Row 3: Sale #5 - 50 PCS (OUT) → Saldo: 60 PCS\n";
echo "Row 4: Retur penjualan #2 - 5 PCS (IN) → Saldo: 65 PCS\n";
echo "Row 5: Retur tukar barang #1 - 5 PCS (OUT) → Saldo: 60 PCS\n";

echo "\n3. PROBLEM ANALYSIS:\n";
echo "User sees:\n";
echo "- Row 'Retur penjualan': Shows 65 PCS final stock but no transaction data\n";
echo "- Row 'Retur tukar barang': Shows 5 PCS OUT and 60 PCS final stock\n";

echo "\nThis suggests:\n";
echo "1. Retur penjualan IN (+5) is not being displayed properly\n";
echo "2. The row shows final stock but not the transaction details\n";
echo "3. Controller logic may be grouping or processing movements incorrectly\n";

echo "\n4. CHECKING CONTROLLER LOGIC:\n";

// Simulate controller processing
echo "Simulating how controller processes these movements:\n";

$dailyStock = [];
$runningQty = 0;

foreach ($stockMovements as $m) {
    $dateStr = $m->tanggal;
    
    // Initialize variables
    $saldoAwalQty = 0;
    $pembelianQty = 0;
    $penjualanQty = 0;
    $produksiQty = 0;
    
    // Process movement based on type and direction
    if ($m->direction === 'in') {
        if ($m->ref_type === 'production') {
            $produksiQty = $m->qty;
        } elseif (strpos($m->ref_type, 'retur') !== false) {
            // Retur IN - this might be the issue
            echo "  Retur IN detected: {$m->ref_type} - {$m->qty} PCS\n";
            // This should show in some column but might be getting lost
        }
    } else {
        if ($m->ref_type === 'sale') {
            $penjualanQty = $m->qty;
        } elseif (strpos($m->ref_type, 'retur') !== false) {
            $penjualanQty = $m->qty; // Retur OUT goes to penjualan
        }
    }
    
    // Update running total
    if ($m->direction === 'in') {
        $runningQty += $m->qty;
    } else {
        $runningQty -= $m->qty;
    }
    
    echo "  {$dateStr} | {$m->ref_type} | Produksi: {$produksiQty}, Penjualan: {$penjualanQty} | Running: {$runningQty}\n";
}

echo "\n5. SUSPECTED ISSUE:\n";
echo "The retur_penjualan IN movement might not be categorized properly in the controller.\n";
echo "It should show the +5 PCS transaction, not just the final stock.\n";

echo "\nNeed to check:\n";
echo "1. How retur IN movements are categorized in controller\n";
echo "2. Why transaction details are missing in the display\n";
echo "3. Ensure retur IN shows in appropriate column (not hidden)\n";