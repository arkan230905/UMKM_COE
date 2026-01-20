<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RECALCULATE ALL BOM HPP ===\n\n";

// Get all BomJobCosting
$bomJobCostings = \App\Models\BomJobCosting::with('produk')->get();

echo "Found " . $bomJobCostings->count() . " BomJobCosting records\n\n";

foreach ($bomJobCostings as $bjc) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Produk: " . ($bjc->produk->nama_produk ?? 'N/A') . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    echo "BEFORE Recalculate:\n";
    echo "  - total_bbb: Rp " . number_format($bjc->total_bbb, 0, ',', '.') . "\n";
    echo "  - total_bahan_pendukung: Rp " . number_format($bjc->total_bahan_pendukung, 0, ',', '.') . "\n";
    echo "  - total_btkl: Rp " . number_format($bjc->total_btkl, 0, ',', '.') . "\n";
    echo "  - total_bop: Rp " . number_format($bjc->total_bop, 0, ',', '.') . "\n";
    echo "  - total_hpp: Rp " . number_format($bjc->total_hpp, 0, ',', '.') . "\n";
    
    // Recalculate
    $bjc->recalculate();
    
    // Refresh
    $bjc->refresh();
    
    echo "\nAFTER Recalculate:\n";
    echo "  - total_bbb: Rp " . number_format($bjc->total_bbb, 0, ',', '.') . "\n";
    echo "  - total_bahan_pendukung: Rp " . number_format($bjc->total_bahan_pendukung, 0, ',', '.') . "\n";
    echo "  - total_btkl: Rp " . number_format($bjc->total_btkl, 0, ',', '.') . "\n";
    echo "  - total_bop: Rp " . number_format($bjc->total_bop, 0, ',', '.') . "\n";
    echo "  - total_hpp: Rp " . number_format($bjc->total_hpp, 0, ',', '.') . "\n";
    
    // Update produk harga_bom
    if ($bjc->produk) {
        $bjc->produk->update([
            'harga_bom' => $bjc->total_hpp
        ]);
        
        echo "\nProduk Updated:\n";
        echo "  - harga_bom: Rp " . number_format($bjc->produk->harga_bom, 0, ',', '.') . "\n";
    }
    
    echo "\n✅ DONE\n\n";
}

echo "=== ALL DONE ===\n";
