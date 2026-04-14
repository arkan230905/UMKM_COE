<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Duplicate Purchases - Ayam Potong ===\n";

// Check actual pembelian records
echo "=== Actual Pembelian Records ===\n";
$pembelians = \App\Models\Pembelian::with(['details' => function($query) {
    $query->where('bahan_baku_id', 1); // Ayam Potong
}])->get();

$totalActualPurchases = 0;
foreach ($pembelians as $pembelian) {
    if ($pembelian->details->count() > 0) {
        foreach ($pembelian->details as $detail) {
            if ($detail->bahan_baku_id == 1) {
                echo "Pembelian #{$pembelian->id} - {$pembelian->nomor_pembelian} ({$pembelian->tanggal}):\n";
                echo "  - Ayam Potong: {$detail->jumlah} Kg\n";
                $totalActualPurchases += $detail->jumlah;
            }
        }
    }
}
echo "Total actual purchases: {$totalActualPurchases} Kg\n\n";

// Check kartu_stok records
echo "=== Kartu Stok Records ===\n";
$kartuStokRecords = \DB::table('kartu_stok')
    ->where('item_type', 'bahan_baku')
    ->where('item_id', 1)
    ->where('ref_type', 'pembelian')
    ->get();

echo "Total kartu_stok purchase records: " . $kartuStokRecords->count() . "\n";
$totalKartuStokPurchases = 0;

foreach ($kartuStokRecords as $record) {
    echo "- ID: {$record->id}, Tanggal: {$record->tanggal}, Qty: {$record->qty_masuk}, Ref ID: {$record->ref_id}, Keterangan: {$record->keterangan}\n";
    $totalKartuStokPurchases += $record->qty_masuk;
}
echo "Total kartu_stok purchases: {$totalKartuStokPurchases} Kg\n\n";

// Check stock_movements records
echo "=== Stock Movements Records ===\n";
$stockMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'purchase')
    ->get();

echo "Total stock_movements purchase records: " . $stockMovements->count() . "\n";
$totalStockMovementPurchases = 0;

foreach ($stockMovements as $movement) {
    echo "- ID: {$movement->id}, Tanggal: {$movement->tanggal}, Qty: {$movement->qty}, Ref ID: {$movement->ref_id}, Direction: {$movement->direction}\n";
    if ($movement->direction === 'in') {
        $totalStockMovementPurchases += $movement->qty;
    }
}
echo "Total stock_movements purchases: {$totalStockMovementPurchases} Kg\n\n";

// Check for duplicates
echo "=== Analysis ===\n";
echo "Actual pembelian transactions: " . $pembelians->filter(function($p) { return $p->details->count() > 0; })->count() . "\n";
echo "Kartu stok entries: " . $kartuStokRecords->count() . "\n";
echo "Stock movements entries: " . $stockMovements->count() . "\n";

if ($kartuStokRecords->count() > 1 || $stockMovements->count() > 1) {
    echo "⚠️  DUPLICATE DETECTED!\n";
    
    // Group by ref_id to see duplicates
    $kartuStokByRef = $kartuStokRecords->groupBy('ref_id');
    $stockMovementsByRef = $stockMovements->groupBy('ref_id');
    
    echo "\nKartu Stok grouped by ref_id:\n";
    foreach ($kartuStokByRef as $refId => $records) {
        echo "- Ref ID {$refId}: " . $records->count() . " records\n";
        if ($records->count() > 1) {
            echo "  ❌ DUPLICATE for ref_id {$refId}\n";
        }
    }
    
    echo "\nStock Movements grouped by ref_id:\n";
    foreach ($stockMovementsByRef as $refId => $records) {
        echo "- Ref ID {$refId}: " . $records->count() . " records\n";
        if ($records->count() > 1) {
            echo "  ❌ DUPLICATE for ref_id {$refId}\n";
        }
    }
}

// Check conversion issue
echo "\n=== Conversion Analysis ===\n";
$ayamPotong = \App\Models\BahanBaku::find(1);
if ($ayamPotong) {
    echo "Ayam Potong conversion data:\n";
    echo "- Sub satuan 2 ID: " . ($ayamPotong->sub_satuan_2_id ?? 'null') . "\n";
    echo "- Sub satuan 2 nilai: " . ($ayamPotong->sub_satuan_2_nilai ?? 'null') . "\n";
    
    if ($ayamPotong->sub_satuan_2_id) {
        $subSatuan = \App\Models\Satuan::find($ayamPotong->sub_satuan_2_id);
        echo "- Sub satuan 2 name: " . ($subSatuan->nama ?? 'Unknown') . "\n";
    }
    
    echo "\nExpected conversion: 1 Kg = 3 Potong\n";
    echo "If purchase is 40 Kg, should be 120 Potong\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Remove duplicate entries from stock_movements\n";
echo "2. Verify kartu_stok has only one entry per purchase\n";
echo "3. Check why PembelianObserver created multiple entries\n";