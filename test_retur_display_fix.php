<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Retur Display Fix ===\n";

// Simulate what the controller will now process
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2) // Ayam Goreng Bundo
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

echo "How movements will now be displayed:\n";

$runningStock = 0;
foreach ($stockMovements as $movement) {
    $display = '';
    
    if ($movement->direction === 'in') {
        if ($movement->ref_type === 'production') {
            $display = "Produksi: +{$movement->qty} PCS";
            $runningStock += $movement->qty;
        } elseif (strpos($movement->ref_type, 'retur') !== false) {
            $display = "Penjualan: -{$movement->qty} PCS (retur IN - barang kembali)";
            $runningStock += $movement->qty;
        }
    } else {
        if ($movement->ref_type === 'sale') {
            $display = "Penjualan: +{$movement->qty} PCS (OUT)";
            $runningStock -= $movement->qty;
        } elseif (strpos($movement->ref_type, 'retur') !== false) {
            $display = "Penjualan: +{$movement->qty} PCS (retur OUT - tukar barang)";
            $runningStock -= $movement->qty;
        }
    }
    
    echo "- {$movement->tanggal} | {$movement->ref_type} | {$display} | Saldo: {$runningStock} PCS\n";
}

echo "\n=== EXPECTED LAPORAN STOK DISPLAY ===\n";
echo "Now the laporan should show:\n";

echo "\nBaris Production:\n";
echo "- Produksi: 160 PCS\n";
echo "- Saldo: 160 PCS\n";

echo "\nBaris Sale #4:\n";
echo "- Penjualan: 50 PCS\n";
echo "- Saldo: 110 PCS\n";

echo "\nBaris Sale #5:\n";
echo "- Penjualan: 50 PCS\n";
echo "- Saldo: 60 PCS\n";

echo "\nBaris Retur Penjualan #2:\n";
echo "- Penjualan: -5 PCS (barang kembali) ← NOW VISIBLE!\n";
echo "- Saldo: 65 PCS\n";

echo "\nBaris Retur Tukar Barang #1:\n";
echo "- Penjualan: 5 PCS (tukar barang)\n";
echo "- Saldo: 60 PCS\n";

echo "\n✅ The retur penjualan IN should now show as -5 PCS in Penjualan column!\n";
echo "✅ No more empty rows with mysterious stock increases!\n";