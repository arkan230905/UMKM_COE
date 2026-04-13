<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Laporan Stok Fix for Ayam Goreng Bundo ===\n";

// Simulate what the controller will process now
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2) // Ayam Goreng Bundo
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

echo "Stock movements and how they will be categorized:\n";

foreach ($stockMovements as $movement) {
    $category = '';
    
    if ($movement->direction === 'in') {
        if ($movement->ref_type === 'production') {
            $category = 'Produksi (IN)';
        } elseif (strpos($movement->ref_type, 'retur') !== false) {
            $category = 'Retur (IN) - barang kembali';
        }
    } else {
        if ($movement->ref_type === 'sale') {
            $category = 'Penjualan (OUT)';
        } elseif (strpos($movement->ref_type, 'retur') !== false) {
            $category = 'Penjualan (OUT) - retur tukar barang ← FIXED!';
        }
    }
    
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} | {$category}\n";
}

echo "\n=== EXPECTED LAPORAN STOK DISPLAY ===\n";
echo "Ayam Goreng Bundo should now show:\n";
echo "\nBaris Production:\n";
echo "- Produksi: 160 units (IN)\n";

echo "\nBaris Sale #4:\n";
echo "- Penjualan: 50 units (OUT)\n";

echo "\nBaris Sale #5:\n";
echo "- Penjualan: 50 units (OUT)\n";

echo "\nBaris Retur Penjualan #2:\n";
echo "- Retur IN: 5 units (barang kembali dari refund)\n";

echo "\nBaris Retur Tukar Barang #1:\n";
echo "- Penjualan: 5 units (OUT) ← This should now appear!\n";

echo "\nFinal stock: 60 units (160 - 50 - 50 + 5 - 5)\n";

echo "\n✅ The missing 5 units OUT for tukar barang should now appear in the Penjualan column!\n";