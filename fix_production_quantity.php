<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing production quantity based on total costs...\n";

try {
    // Update produksi Ayam Pop (ID: 4)
    $produksiAyamPop = \App\Models\Produksi::find(2);
    if ($produksiAyamPop) {
        // Berdasarkan data yang ditampilkan: 100 unit dengan total biaya 1.621.666
        $produksiAyamPop->jumlah = 100;
        $produksiAyamPop->save();
        echo "Updated Ayam Pop production: Qty = 100, Total Cost = {$produksiAyamPop->total_biaya}\n";
        echo "HPP per unit: " . ($produksiAyamPop->total_biaya / 100) . "\n";
    }
    
    // Update produksi Nasi Ayam Crispy (ID: 2)
    $produksiNasiAyam = \App\Models\Produksi::find(3);
    if ($produksiNasiAyam) {
        // Berdasarkan data yang ditampilkan: 100 unit dengan total biaya 2.005.000
        $produksiNasiAyam->jumlah = 100;
        $produksiNasiAyam->save();
        echo "Updated Nasi Ayam Crispy production: Qty = 100, Total Cost = {$produksiNasiAyam->total_biaya}\n";
        echo "HPP per unit: " . ($produksiNasiAyam->total_biaya / 100) . "\n";
    }
    
    // Test HPP setelah fix
    echo "\n=== Testing HPP after fix ===\n";
    
    $products = [2, 4];
    foreach($products as $productId) {
        $produk = \App\Models\Produk::find($productId);
        echo "\nProduct: {$produk->nama_produk}\n";
        
        $penjualanDetails = \App\Models\PenjualanDetail::where('produk_id', $productId)
            ->with(['penjualan'])
            ->get();
            
        foreach($penjualanDetails as $detail) {
            $hpp = $produk->getHPPForSaleDate($detail->penjualan->tanggal);
            $margin = ($detail->harga_satuan - $hpp) * $detail->jumlah;
            
            echo "  Sale: {$detail->penjualan->tanggal} - Qty: {$detail->jumlah} - Price: {$detail->harga_satuan}\n";
            echo "    HPP: {$hpp} - Margin: {$margin}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Done.\n";
