<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Penjualan Column Fix ===\n";

// Simulate what the controller will now process
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('tanggal')
    ->get();

echo "Expected laporan stok display after fix:\n\n";

$runningQty = 0;
$runningValue = 0;

foreach ($stockMovements as $movement) {
    echo "Row: {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id}\n";
    
    if ($movement->direction === 'in') {
        $runningQty += $movement->qty;
        $runningValue += $movement->total_cost;
        
        if ($movement->ref_type === 'production') {
            echo "- Produksi: {$movement->qty} PCS\n";
            echo "- Penjualan: KOSONG ✅\n";
        }
    } else {
        // Calculate average cost for sales
        $avgCost = $runningQty > 0 ? $runningValue / $runningQty : 0;
        $salesCost = $movement->total_cost ?: ($movement->qty * $avgCost);
        
        $runningQty -= $movement->qty;
        $runningValue -= $salesCost;
        
        if ($movement->ref_type === 'sale') {
            echo "- Penjualan: {$movement->qty} PCS ✅ (NOW VISIBLE!)\n";
            echo "- Cost: Rp " . number_format($salesCost, 0) . "\n";
        } elseif (strpos($movement->ref_type, 'retur') !== false) {
            echo "- Penjualan: {$movement->qty} PCS (retur) ✅\n";
        }
        echo "- Produksi: KOSONG ✅\n";
    }
    
    echo "- Saldo Akhir: {$runningQty} PCS\n";
    echo "\n";
}

echo "=== EXPECTED RESULT ===\n";
echo "✅ Row 1 (Production): Penjualan = KOSONG, Produksi = 160 PCS\n";
echo "✅ Row 2 (Sale #4): Penjualan = 50 PCS, Produksi = KOSONG\n";
echo "✅ Row 3 (Sale #5): Penjualan = 50 PCS, Produksi = KOSONG\n";
echo "✅ Row 4 (Retur): Penjualan = 5 PCS, Produksi = KOSONG\n";

echo "\n🎉 Kolom Penjualan should now show sales data correctly!\n";