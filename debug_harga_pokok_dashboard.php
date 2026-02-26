<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG HARGA POKOK DASHBOARD VS PRODUK ===\n";

try {
    // Get data from BomJobCosting (dashboard source)
    $bomJobCostings = DB::table('bom_job_costings')->get();
    
    echo "Data di BomJobCosting (Dashboard):\n";
    foreach($bomJobCostings as $bjc) {
        $produk = DB::table('produks')->where('id', $bjc->produk_id)->first();
        $totalHPP = $bjc->total_bbb + $bjc->total_bahan_pendukung + $bjc->total_btkl + $bjc->total_bop;
        
        echo "Product {$bjc->produk_id} ({$produk->nama_produk ?? 'Unknown'}):\n";
        echo "  - BomJobCosting: Rp " . number_format($totalHPP, 2) . "\n";
        echo "  - Produk Table: Rp " . number_format($produk->harga_pokok ?? 0, 2) . "\n";
        
        if ($produk->harga_pokok == $totalHPP) {
            echo "  ✅ SYNC\n";
        } else {
            $diff = $totalHPP - ($produk->harga_pokok ?? 0);
            echo "  ❌ NOT SYNC! Difference: Rp " . number_format($diff, 2) . "\n";
        }
        echo "\n";
    }
    
    echo "=== ANALYSIS ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
