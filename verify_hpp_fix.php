<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFY HPP FIX ===\n";

try {
    // Get BomJobCosting data
    $bomJobCostings = DB::table('bom_job_costings')->get();
    
    foreach ($bomJobCostings as $bjc) {
        $produk = DB::table('produks')->where('id', $bjc->produk_id)->first();
        
        $totalBiayaBahan = $bjc->total_bbb + $bjc->total_bahan_pendukung;
        $totalBTKL = $bjc->total_btkl;
        $totalBOP = \App\Models\BomJobBOP::where('bom_job_costing_id', $bjc->id)->sum('subtotal');
        
        // NEW LOGIC: Total HPP = Biaya Bahan + Biaya Bahan + BTKL + BOP
        $totalBiayaHPP = $totalBiayaBahan + $totalBiayaBahan + $totalBTKL + $totalBOP;
        
        echo "Product: {$produk->nama_produk ?? 'Unknown'}\n";
        echo "  - Biaya Bahan: " . number_format($totalBiayaBahan, 2) . "\n";
        echo "  - BTKL: " . number_format($totalBTKL, 2) . "\n";
        echo "  - BOP: " . number_format($totalBOP, 2) . "\n";
        echo "  - Total HPP: " . number_format($totalBiayaHPP, 2) . "\n";
        echo "  - Expected: Rp 6.380\n";
        
        $expected = 6380;
        if ($produk->harga_pokok == $expected) {
            echo "  ✅ CORRECT!\n";
        } else {
            echo "  ❌ MISMATCH! Current: " . number_format($produk->harga_pokok ?? 0, 2) . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
