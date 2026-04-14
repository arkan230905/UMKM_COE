<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FIXING CONVERSION ISSUE - REMOVING FAKE DATA ===\n\n";

// 1. HAPUS ADJUSTMENT PALSU YANG SAYA BUAT
echo "1. Removing fake adjustment data...\n";
$fakeAdjustment = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'adjustment')
    ->where('qty', 171.1333)
    ->first();

if ($fakeAdjustment) {
    echo "   Deleting StockMovement ID: {$fakeAdjustment->id}\n";
    $fakeAdjustment->delete();
}

$fakeStockLayer = \App\Models\StockLayer::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'adjustment')
    ->where('qty', 171.1333)
    ->first();

if ($fakeStockLayer) {
    echo "   Deleting StockLayer ID: {$fakeStockLayer->id}\n";
    $fakeStockLayer->delete();
}

echo "✅ Fake data removed\n\n";

// 2. PERIKSA KONVERSI SATUAN AYAM POTONG
echo "2. Checking Ayam Potong conversion factors...\n";
$ayamPotong = \App\Models\BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->find(1);

echo "Main unit: " . ($ayamPotong->satuan->nama ?? 'N/A') . "\n";

if ($ayamPotong->sub_satuan_1_id) {
    echo "Sub satuan 1: " . ($ayamPotong->subSatuan1->nama ?? 'N/A') . " (nilai: {$ayamPotong->sub_satuan_1_nilai})\n";
}
if ($ayamPotong->sub_satuan_2_id) {
    echo "Sub satuan 2: " . ($ayamPotong->subSatuan2->nama ?? 'N/A') . " (nilai: {$ayamPotong->sub_satuan_2_nilai})\n";
}
if ($ayamPotong->sub_satuan_3_id) {
    echo "Sub satuan 3: " . ($ayamPotong->subSatuan3->nama ?? 'N/A') . " (nilai: {$ayamPotong->sub_satuan_3_nilai})\n";
}

// 3. HITUNG STOK SEBENARNYA DARI MOVEMENTS
echo "\n3. Calculating actual stock from movements...\n";
$totalIn = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('direction', 'in')
    ->sum('qty');

$totalOut = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('direction', 'out')
    ->sum('qty');

$actualStock = $totalIn - $totalOut;

echo "Total IN: {$totalIn} KG\n";
echo "Total OUT: {$totalOut} KG\n";
echo "Actual stock: {$actualStock} KG\n";

// 4. KONVERSI KE SEMUA SATUAN
echo "\n4. Converting to all units...\n";

// KG (main unit)
echo "Stock in KG: {$actualStock} KG\n";

// Gram (sub satuan 1)
if ($ayamPotong->sub_satuan_1_nilai) {
    $stockInGram = $actualStock * $ayamPotong->sub_satuan_1_nilai;
    echo "Stock in Gram: {$stockInGram} Gram\n";
}

// Potong (sub satuan 2) 
if ($ayamPotong->sub_satuan_2_nilai) {
    $stockInPotong = $actualStock * $ayamPotong->sub_satuan_2_nilai;
    echo "Stock in Potong: {$stockInPotong} Potong\n";
    
    // JIKA POTONG POSITIF TAPI KG NEGATIF, ADA MASALAH KONVERSI
    if ($stockInPotong > 0 && $actualStock < 0) {
        echo "❌ CONVERSION PROBLEM DETECTED!\n";
        echo "   Potong shows positive but KG shows negative\n";
        echo "   This means conversion factor is wrong\n";
        
        // Hitung konversi yang benar berdasarkan user info (40 potong sisa)
        if ($stockInPotong != 40) {
            $correctConversionFactor = 40 / $actualStock; // This should give us the right factor
            echo "   Current conversion: 1 KG = {$ayamPotong->sub_satuan_2_nilai} Potong\n";
            echo "   Suggested conversion: 1 KG = " . abs($correctConversionFactor) . " Potong\n";
        }
    }
}

// Ons (sub satuan 3)
if ($ayamPotong->sub_satuan_3_nilai) {
    $stockInOns = $actualStock * $ayamPotong->sub_satuan_3_nilai;
    echo "Stock in Ons: {$stockInOns} Ons\n";
}

// 5. ANALISIS MASALAH
echo "\n5. Problem analysis...\n";
if ($actualStock < 0) {
    echo "❌ Main stock (KG) is negative: {$actualStock} KG\n";
    echo "This means production consumed more than available\n";
    echo "But if Potong shows positive (40 potong), then:\n";
    echo "- Either the conversion factor is wrong\n";
    echo "- Or there's missing stock data\n";
    echo "- Or production calculation is wrong\n";
} else {
    echo "✅ Main stock (KG) is positive: {$actualStock} KG\n";
}

echo "\n=== RECOMMENDATION ===\n";
echo "The issue is likely in the conversion factors or production calculation.\n";
echo "Please check:\n";
echo "1. Is the conversion 1 KG = X Potong correct?\n";
echo "2. Did production really consume 53.33 KG?\n";
echo "3. Should production use less material?\n";

echo "\nAnalysis completed - no fake data added!\n";