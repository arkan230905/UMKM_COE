<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Kolom Penjualan Kosong ===\n";

// Check stock movements for Ayam Goreng Bundo
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('tanggal')
    ->get();

echo "1. Stock movements yang seharusnya muncul di kolom Penjualan:\n";

foreach ($stockMovements as $movement) {
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} PCS\n";
    
    // Analyze what should appear in Penjualan column
    if ($movement->direction === 'out') {
        if ($movement->ref_type === 'sale') {
            echo "  → Should show in Penjualan: {$movement->qty} PCS\n";
        } elseif (strpos($movement->ref_type, 'retur') !== false) {
            echo "  → Should show in Penjualan: {$movement->qty} PCS (retur)\n";
        }
    } else {
        echo "  → Should NOT show in Penjualan (IN movement)\n";
    }
    echo "\n";
}

echo "2. Expected Penjualan column data:\n";
echo "Row 1 (Production): Penjualan = KOSONG ✅\n";
echo "Row 2 (Sale #4): Penjualan = 50 PCS ❌ (currently kosong)\n";
echo "Row 3 (Sale #5): Penjualan = 50 PCS ❌ (currently kosong)\n";
echo "Row 4 (Retur tukar barang): Penjualan = 5 PCS ✅ (sudah muncul)\n";

echo "\n3. MASALAH DITEMUKAN:\n";
echo "Sale movements (sale#4 dan sale#5) tidak muncul di kolom Penjualan!\n";
echo "Hanya retur_tukar_barang yang muncul.\n";

echo "\n4. Checking controller logic for sales:\n";
echo "Controller should categorize:\n";
echo "- ref_type = 'sale' + direction = 'out' + tipe = 'product' → Penjualan column\n";
echo "- ref_type contains 'retur' + direction = 'out' + tipe = 'product' → Penjualan column\n";

echo "\n5. Possible issues:\n";
echo "a) Controller logic tidak menangani 'sale' movements dengan benar\n";
echo "b) View tidak menampilkan data penjualan dengan benar\n";
echo "c) Data penjualan tidak memiliki cost information yang lengkap\n";

// Check sales data
echo "\n6. Checking sales data:\n";
$sales = \DB::table('penjualans')->whereIn('id', [4, 5])->get();
foreach ($sales as $sale) {
    echo "Sale #{$sale->id}:\n";
    echo "- Tanggal: {$sale->tanggal}\n";
    echo "- Total: Rp " . number_format($sale->total, 0) . "\n";
    
    // Check sale details
    $details = \DB::table('penjualan_details')->where('penjualan_id', $sale->id)->get();
    foreach ($details as $detail) {
        if ($detail->produk_id == 2) {
            echo "- Ayam Goreng Bundo: {$detail->qty} PCS @ Rp " . number_format($detail->harga_jual, 0) . "\n";
        }
    }
    echo "\n";
}

echo "KESIMPULAN:\n";
echo "❌ Sale movements tidak muncul di kolom Penjualan\n";
echo "✅ Retur movements muncul di kolom Penjualan\n";
echo "🔧 Perlu perbaikan controller logic untuk sales\n";