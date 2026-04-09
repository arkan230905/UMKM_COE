<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE STOCK vs BOM PRICE CHECK ===\n\n";

// Function to get stock report final price
function getStockReportPrice($itemType, $itemId) {
    try {
        $request = new \Illuminate\Http\Request([
            'tipe' => $itemType === 'support' ? 'bahan_pendukung' : 'material',
            'item_id' => $itemId
        ]);

        $controller = new \App\Http\Controllers\LaporanController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('stok');
        $method->setAccessible(true);
        
        $response = $method->invoke($controller, $request);
        $viewData = $response->getData();
        
        if (!isset($viewData['dailyStock']) || empty($viewData['dailyStock'])) {
            return null;
        }

        $dailyStock = $viewData['dailyStock'];
        $lastTransaction = end($dailyStock);
        
        if ($lastTransaction && $lastTransaction['saldo_akhir_qty'] > 0) {
            return $lastTransaction['saldo_akhir_nilai'] / $lastTransaction['saldo_akhir_qty'];
        }

        return null;
    } catch (\Exception $e) {
        echo "Error getting stock price for item $itemId: " . $e->getMessage() . "\n";
        return null;
    }
}

// Function to calculate recipe unit price
function calculateRecipePrice($basePrice, $item, $recipeSatuan) {
    $satuanBase = $item->satuan_nama ?? 'unit';
    $satuanResep = $recipeSatuan ?: $satuanBase;

    if (strtolower($satuanBase) === 'liter' && strtolower($satuanResep) === 'mililiter') {
        return $basePrice / 1000;
    } elseif (strtolower($satuanBase) === 'kilogram' && strtolower($satuanResep) === 'gram') {
        return $basePrice / 1000;
    } elseif (strtolower($satuanBase) === 'ekor' && strtolower($satuanResep) === 'potong') {
        $conversionFactor = $item->sub_satuan_1_nilai ?? 6;
        return $basePrice / $conversionFactor;
    } elseif (strtolower($satuanBase) === 'bungkus' && strtolower($satuanResep) === 'sendok teh') {
        $conversionValue = $item->sub_satuan_2_nilai ?? 0.01;
        $conversionFactor = 1 / $conversionValue;
        return $basePrice / $conversionFactor;
    } elseif (strtolower($satuanBase) === 'kilogram' && strtolower($satuanResep) === 'sendok teh') {
        $conversionFactor = $item->sub_satuan_2_nilai ?? 200;
        return $basePrice / $conversionFactor;
    } elseif (strtolower($satuanBase) === 'bungkus' && strtolower($satuanResep) === 'gram') {
        $conversionFactor = $item->sub_satuan_1_nilai ?? 500;
        return $basePrice / $conversionFactor;
    }

    return $basePrice;
}

// Check all BOM entries
echo "1. CHECKING BAHAN PENDUKUNG\n";
echo "=" . str_repeat("=", 80) . "\n";

$bomBahanPendukung = DB::table('bom_job_bahan_pendukung')->get();

foreach($bomBahanPendukung as $bom) {
    $bahanPendukung = DB::table('bahan_pendukungs')
        ->leftJoin('satuans', 'bahan_pendukungs.satuan_id', '=', 'satuans.id')
        ->where('bahan_pendukungs.id', $bom->bahan_pendukung_id)
        ->select('bahan_pendukungs.*', 'satuans.nama as satuan_nama')
        ->first();
    
    if ($bahanPendukung) {
        echo "\n📦 {$bahanPendukung->nama_bahan} (ID: {$bahanPendukung->id})\n";
        echo "   Satuan Base: {$bahanPendukung->satuan_nama}\n";
        echo "   Satuan Resep: {$bom->satuan}\n";
        echo "   Jumlah Resep: {$bom->jumlah}\n";
        
        // Get stock report price
        $stockPrice = getStockReportPrice('support', $bahanPendukung->id);
        echo "   💰 Harga Laporan Stok: Rp " . number_format($stockPrice, 2) . " per {$bahanPendukung->satuan_nama}\n";
        
        // Calculate expected recipe price
        $expectedRecipePrice = calculateRecipePrice($stockPrice, $bahanPendukung, $bom->satuan);
        echo "   🧮 Harga Resep (Expected): Rp " . number_format($expectedRecipePrice, 2) . " per {$bom->satuan}\n";
        
        // Current BOM price
        echo "   📋 Harga BOM (Current): Rp " . number_format($bom->harga_satuan, 2) . " per {$bom->satuan}\n";
        
        // Check if match
        $difference = abs($expectedRecipePrice - $bom->harga_satuan);
        if ($difference > 0.01) {
            echo "   ❌ TIDAK SESUAI! Selisih: Rp " . number_format($difference, 2) . "\n";
        } else {
            echo "   ✅ SESUAI\n";
        }
        
        // Calculate subtotal
        $expectedSubtotal = $bom->jumlah * $expectedRecipePrice;
        echo "   💵 Subtotal Expected: Rp " . number_format($expectedSubtotal, 2) . "\n";
        echo "   💵 Subtotal Current: Rp " . number_format($bom->subtotal, 2) . "\n";
        
        echo "   " . str_repeat("-", 60) . "\n";
    }
}

echo "\n\n2. CHECKING BAHAN BAKU\n";
echo "=" . str_repeat("=", 80) . "\n";

$bomBahanBaku = DB::table('bom_job_bbb')->get();

foreach($bomBahanBaku as $bom) {
    $bahanBaku = DB::table('bahan_bakus')
        ->leftJoin('satuans', 'bahan_bakus.satuan_id', '=', 'satuans.id')
        ->where('bahan_bakus.id', $bom->bahan_baku_id)
        ->select('bahan_bakus.*', 'satuans.nama as satuan_nama')
        ->first();
    
    if ($bahanBaku) {
        echo "\n📦 {$bahanBaku->nama_bahan} (ID: {$bahanBaku->id})\n";
        echo "   Satuan Base: {$bahanBaku->satuan_nama}\n";
        echo "   Satuan Resep: {$bom->satuan}\n";
        echo "   Jumlah Resep: {$bom->jumlah}\n";
        
        // Get stock report price
        $stockPrice = getStockReportPrice('material', $bahanBaku->id);
        echo "   💰 Harga Laporan Stok: Rp " . number_format($stockPrice, 2) . " per {$bahanBaku->satuan_nama}\n";
        
        // Calculate expected recipe price
        $expectedRecipePrice = calculateRecipePrice($stockPrice, $bahanBaku, $bom->satuan);
        echo "   🧮 Harga Resep (Expected): Rp " . number_format($expectedRecipePrice, 2) . " per {$bom->satuan}\n";
        
        // Current BOM price
        echo "   📋 Harga BOM (Current): Rp " . number_format($bom->harga_satuan, 2) . " per {$bom->satuan}\n";
        
        // Check if match
        $difference = abs($expectedRecipePrice - $bom->harga_satuan);
        if ($difference > 0.01) {
            echo "   ❌ TIDAK SESUAI! Selisih: Rp " . number_format($difference, 2) . "\n";
        } else {
            echo "   ✅ SESUAI\n";
        }
        
        // Calculate subtotal
        $expectedSubtotal = $bom->jumlah * $expectedRecipePrice;
        echo "   💵 Subtotal Expected: Rp " . number_format($expectedSubtotal, 2) . "\n";
        echo "   💵 Subtotal Current: Rp " . number_format($bom->subtotal, 2) . "\n";
        
        echo "   " . str_repeat("-", 60) . "\n";
    }
}

echo "\n\n=== SUMMARY ===\n";
echo "Jika ada yang TIDAK SESUAI, jalankan:\n";
echo "php artisan bom:update-from-stock-report\n";