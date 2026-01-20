<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX ALL BOM total_hpp ===\n\n";

// Get all BOMs
$boms = \App\Models\Bom::with(['details.bahanBaku', 'proses'])->get();

echo "Found " . $boms->count() . " BOM records\n\n";

foreach ($boms as $bom) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "BOM ID: {$bom->id} | Produk: " . ($bom->produk->nama_produk ?? 'N/A') . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    echo "BEFORE:\n";
    echo "  - total_bbb: Rp " . number_format($bom->total_bbb, 0, ',', '.') . "\n";
    echo "  - total_btkl: Rp " . number_format($bom->total_btkl, 0, ',', '.') . "\n";
    echo "  - total_bop: Rp " . number_format($bom->total_bop, 0, ',', '.') . "\n";
    echo "  - total_hpp: Rp " . number_format($bom->total_hpp, 0, ',', '.') . "\n";
    echo "  - total_biaya: Rp " . number_format($bom->total_biaya, 0, ',', '.') . "\n";
    
    // Recalculate
    $bom->hitungTotalBiaya();
    $bom->save();
    
    // Refresh
    $bom->refresh();
    
    echo "\nAFTER hitungTotalBiaya():\n";
    echo "  - total_bbb: Rp " . number_format($bom->total_bbb, 0, ',', '.') . "\n";
    echo "  - total_btkl: Rp " . number_format($bom->total_btkl, 0, ',', '.') . "\n";
    echo "  - total_bop: Rp " . number_format($bom->total_bop, 0, ',', '.') . "\n";
    echo "  - total_hpp: Rp " . number_format($bom->total_hpp, 0, ',', '.') . "\n";
    echo "  - total_biaya: Rp " . number_format($bom->total_biaya, 0, ',', '.') . "\n";
    
    // Update product harga_bom
    $bom->updateProductPrice();
    
    echo "\nProduk Updated:\n";
    echo "  - harga_bom: Rp " . number_format($bom->produk->harga_bom, 0, ',', '.') . "\n";
    
    echo "\n✅ DONE\n\n";
}

echo "=== ALL DONE ===\n";
