<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING BOM HPP FLOW ===\n\n";

// Get produk yang punya BOM
$produks = \App\Models\Produk::whereHas('boms')->with('boms')->take(3)->get();

foreach ($produks as $produk) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "PRODUK: {$produk->nama_produk}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // 1. Check BOM
    $bom = $produk->boms->first();
    if ($bom) {
        echo "1. BOM (tabel: boms)\n";
        echo "   - ID: {$bom->id}\n";
        echo "   - total_bbb: Rp " . number_format($bom->total_bbb, 0, ',', '.') . "\n";
        echo "   - total_btkl: Rp " . number_format($bom->total_btkl, 0, ',', '.') . "\n";
        echo "   - total_bop: Rp " . number_format($bom->total_bop, 0, ',', '.') . "\n";
        echo "   - total_hpp: Rp " . number_format($bom->total_hpp, 0, ',', '.') . "\n";
        echo "   - total_biaya: Rp " . number_format($bom->total_biaya, 0, ',', '.') . "\n";
    } else {
        echo "1. BOM: TIDAK ADA\n";
    }
    
    echo "\n";
    
    // 2. Check BomJobCosting
    $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $produk->id)->first();
    if ($bomJobCosting) {
        echo "2. BomJobCosting (tabel: bom_job_costings)\n";
        echo "   - ID: {$bomJobCosting->id}\n";
        echo "   - total_bbb: Rp " . number_format($bomJobCosting->total_bbb, 0, ',', '.') . "\n";
        echo "   - total_bahan_pendukung: Rp " . number_format($bomJobCosting->total_bahan_pendukung, 0, ',', '.') . "\n";
        echo "   - total_btkl: Rp " . number_format($bomJobCosting->total_btkl, 0, ',', '.') . "\n";
        echo "   - total_bop: Rp " . number_format($bomJobCosting->total_bop, 0, ',', '.') . "\n";
        echo "   - total_hpp: Rp " . number_format($bomJobCosting->total_hpp, 0, ',', '.') . "\n";
        echo "   - hpp_per_unit: Rp " . number_format($bomJobCosting->hpp_per_unit, 0, ',', '.') . "\n";
    } else {
        echo "2. BomJobCosting: TIDAK ADA\n";
    }
    
    echo "\n";
    
    // 3. Check Produk
    echo "3. PRODUK (tabel: produks)\n";
    echo "   - biaya_bahan: Rp " . number_format($produk->biaya_bahan ?? 0, 0, ',', '.') . "\n";
    echo "   - harga_bom: Rp " . number_format($produk->harga_bom ?? 0, 0, ',', '.') . "\n";
    echo "   - harga_jual: Rp " . number_format($produk->harga_jual ?? 0, 0, ',', '.') . "\n";
    
    echo "\n";
    
    // 4. Validation
    echo "4. VALIDASI ALUR:\n";
    
    $expectedHPP = 0;
    if ($bomJobCosting) {
        $expectedHPP = $bomJobCosting->total_hpp;
        echo "   ✓ Expected harga_bom = BomJobCosting->total_hpp\n";
        echo "     Expected: Rp " . number_format($expectedHPP, 0, ',', '.') . "\n";
        echo "     Actual: Rp " . number_format($produk->harga_bom ?? 0, 0, ',', '.') . "\n";
        
        if (abs(($produk->harga_bom ?? 0) - $expectedHPP) < 1) {
            echo "     ✅ MATCH!\n";
        } else {
            echo "     ❌ MISMATCH!\n";
        }
    } elseif ($bom) {
        $expectedHPP = $bom->total_hpp;
        echo "   ✓ Expected harga_bom = Bom->total_hpp\n";
        echo "     Expected: Rp " . number_format($expectedHPP, 0, ',', '.') . "\n";
        echo "     Actual: Rp " . number_format($produk->harga_bom ?? 0, 0, ',', '.') . "\n";
        
        if (abs(($produk->harga_bom ?? 0) - $expectedHPP) < 1) {
            echo "     ✅ MATCH!\n";
        } else {
            echo "     ❌ MISMATCH!\n";
        }
    }
    
    echo "\n";
    
    // 5. Formula Check
    if ($bomJobCosting) {
        echo "5. FORMULA CHECK:\n";
        $calculatedHPP = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;
        echo "   HPP = BBB + Bahan Pendukung + BTKL + BOP\n";
        echo "   HPP = " . number_format($bomJobCosting->total_bbb, 0, ',', '.') . " + " . number_format($bomJobCosting->total_bahan_pendukung, 0, ',', '.') . " + " . number_format($bomJobCosting->total_btkl, 0, ',', '.') . " + " . number_format($bomJobCosting->total_bop, 0, ',', '.') . "\n";
        echo "   HPP = Rp " . number_format($calculatedHPP, 0, ',', '.') . "\n";
        echo "   Stored HPP = Rp " . number_format($bomJobCosting->total_hpp, 0, ',', '.') . "\n";
        
        if (abs($calculatedHPP - $bomJobCosting->total_hpp) < 1) {
            echo "   ✅ FORMULA CORRECT!\n";
        } else {
            echo "   ❌ FORMULA MISMATCH!\n";
        }
    }
    
    echo "\n\n";
}

echo "=== DONE ===\n";
