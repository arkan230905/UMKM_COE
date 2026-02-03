<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK LATEST PRODUCTION ===\n";

$latestProduksi = \App\Models\Produksi::orderBy('id', 'desc')->first();

if ($latestProduksi) {
    echo "Produksi ID: " . $latestProduksi->id . "\n";
    echo "Produk: " . $latestProduksi->produk->nama_produk . "\n";
    echo "Tanggal: " . $latestProduksi->tanggal . "\n";
    echo "Qty Produksi: " . $latestProduksi->qty_produksi . "\n";
    echo "Total Bahan: Rp " . number_format($latestProduksi->total_bahan, 0, ',', '.') . "\n";
    echo "Total BTKL: Rp " . number_format($latestProduksi->total_btkl, 0, ',', '.') . "\n";
    echo "Total BOP: Rp " . number_format($latestProduksi->total_bop, 0, ',', '.') . "\n";
    echo "Total Biaya: Rp " . number_format($latestProduksi->total_biaya, 0, ',', '.') . "\n";
    
    echo "\n--- Production Details ---\n";
    foreach ($latestProduksi->details as $detail) {
        echo "Bahan: " . $detail->bahanBaku->nama_bahan . "\n";
        echo "Qty Resep: " . $detail->qty_resep . " " . $detail->satuan_resep . "\n";
        echo "Qty Konversi: " . $detail->qty_konversi . " (base unit)\n";
        echo "Harga Satuan: Rp " . number_format($detail->harga_satuan, 0, ',', '.') . "\n";
        echo "Subtotal: Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
        echo "---\n";
    }
} else {
    echo "No production records found\n";
}
