<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the exact same logic as in ProduksiController
echo "=== TEST PRODUCTION VALIDATION LOGIC ===\n";

$produk = \App\Models\Produk::find(1);
$qtyProd = 1;
$converter = new \App\Support\UnitConverter();
$stock = new \App\Services\StockService();

$bomItems = \App\Models\Bom::with('details.bahanBaku')->where('produk_id', $produk->id)->get();

echo "BOM Items Count: " . $bomItems->count() . "\n";

// Validasi stok cukup untuk setiap bahan baku (cek ke StockLayer via StockService)
$shortages = [];
$shortNames = [];
foreach ($bomItems as $bom) {
    echo "Processing BOM ID: " . $bom->id . "\n";
    foreach ($bom->details as $detail) {
        echo "Processing Detail ID: " . $detail->id . "\n";
        $bahan = $detail->bahanBaku;
        if (!$bahan) { 
            echo "No bahan found, skipping\n";
            continue; 
        }
        
        echo "Bahan: " . $bahan->nama_bahan . "\n";
        echo "Bahan Satuan: " . gettype($bahan->satuan) . " - " . json_encode($bahan->satuan) . "\n";
        
        $qtyPerUnit = (float)$detail->jumlah;
        echo "Qty per Unit: " . $qtyPerUnit . "\n";
        
        $satuanResep = $detail->satuan ?: ($bahan->satuan->nama ?? $bahan->satuan);
        echo "Satuan Resep: " . $satuanResep . "\n";
        
        $qtyResepTotal = $qtyPerUnit * $qtyProd;
        echo "Qty Resep Total: " . $qtyResepTotal . "\n";
        
        $targetSatuan = (string)($bahan->satuan->nama ?? $bahan->satuan);
        echo "Target Satuan: " . $targetSatuan . "\n";
        
        $qtyBase = $converter->convert($qtyResepTotal, (string)$satuanResep, $targetSatuan);
        echo "Qty Base: " . $qtyBase . "\n";
        
        $available = (float) $stock->getAvailableQty('material', (int)$bahan->id);
        echo "Available: " . $available . "\n";
        
        echo "Check: " . ($available + 1e-9) . " < " . $qtyBase . " = " . (($available + 1e-9 < $qtyBase) ? "true" : "false") . "\n";
        
        if ($available + 1e-9 < $qtyBase) {
            $shortages[] = "Stok {$bahan->nama_bahan} tidak cukup. Butuh $qtyBase, tersedia " . (float)($available);
            $shortNames[] = (string)($bahan->nama_bahan ?? $bahan->nama ?? 'Bahan');
            echo ">>> SHORTAGE DETECTED!\n";
        } else {
            echo ">>> Stock OK!\n";
        }
    }
}

if (!empty($shortages)) {
    echo "\n=== SHORTAGES FOUND ===\n";
    foreach ($shortages as $shortage) {
        echo "- " . $shortage . "\n";
    }
    $msg = 'Bahan baku '.implode(', ', $shortNames).' kurang untuk melakukan produksi produk.';
    echo "Message: " . $msg . "\n";
} else {
    echo "\n=== NO SHORTAGES - ALL GOOD! ===\n";
}
