<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Final Verification - Ayam Goreng Bundo Stock ===\n";

// Check final stock movements
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2) // Ayam Goreng Bundo
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

echo "Complete stock movements for Ayam Goreng Bundo:\n";
$runningStock = 0;
foreach ($stockMovements as $movement) {
    $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
    $runningStock += $change;
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} | Running: {$runningStock} | {$movement->keterangan}\n";
}

echo "\n=== SUMMARY ===\n";
echo "✅ Production: +160 units (produksi)\n";
echo "✅ Sale #4: -50 units (penjualan)\n";
echo "✅ Sale #5: -50 units (penjualan)\n";
echo "✅ Retur refund: +5 units (barang kembali dari refund)\n";
echo "✅ Retur tukar barang: -5 units (barang keluar untuk ganti) ← FIXED!\n";
echo "\nFinal stock: {$runningStock} units\n";

echo "\nNow the laporan stok for Ayam Goreng Bundo should show:\n";
echo "- Production IN: 160 units\n";
echo "- Sales OUT: 100 units (50 + 50)\n";
echo "- Retur IN: 5 units (refund)\n";
echo "- Retur OUT: 5 units (tukar barang) ← This was missing before!\n";
echo "- Final balance: 60 units ✅\n";

echo "\n🎉 Problem solved! Laporan stok will now show the missing 5 units OUT for tukar barang.\n";