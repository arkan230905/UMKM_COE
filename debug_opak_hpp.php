<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Cari produk opak
$produk = \App\Models\Produk::where('nama_produk', 'like', '%opak%')->first();

if (!$produk) {
    echo "Produk opak tidak ditemukan\n";
    exit;
}

echo "=== DEBUG HPP PRODUK OPAK ===\n";
echo "Produk ID: " . $produk->id . "\n";
echo "Nama Produk: " . $produk->nama_produk . "\n";
echo "HPP (stored): " . ($produk->hpp ?? 'NULL') . "\n";
echo "Harga BOM: " . ($produk->harga_bom ?? 'NULL') . "\n";
echo "getActualHPP(): " . $produk->getActualHPP() . "\n";

// Cek BomJobCosting
$bomJobCosting = $produk->bomJobCosting;
if ($bomJobCosting) {
    echo "\n=== BOM JOB COSTING ===\n";
    echo "Total BBB: " . $bomJobCosting->total_bbb . "\n";
    echo "Total Bahan Pendukung: " . $bomJobCosting->total_bahan_pendukung . "\n";
    echo "Total BOP: " . $bomJobCosting->total_bop . "\n";
    echo "Total BTKL: " . $bomJobCosting->total_btkl . "\n";
    echo "Total Biaya: " . $bomJobCosting->total_biaya . "\n";
    echo "Qty Produksi: " . $bomJobCosting->qty_produksi . "\n";
    echo "HPP per unit: " . ($bomJobCosting->qty_produksi > 0 ? $bomJobCosting->total_biaya / $bomJobCosting->qty_produksi : 0) . "\n";
} else {
    echo "\nBOM Job Costing tidak ditemukan\n";
}

// Cek produksi
$produksi = \App\Models\Produksi::where('produk_id', $produk->id)
    ->where('status', 'completed')
    ->orderBy('id', 'desc')
    ->first();

if ($produksi) {
    echo "\n=== PRODUKSI TERAKHIR ===\n";
    echo "Produksi ID: " . $produksi->id . "\n";
    echo "Total Biaya: " . $produksi->total_biaya . "\n";
    echo "Qty Produksi: " . $produksi->qty_produksi . "\n";
    echo "HPP per unit: " . ($produksi->qty_produksi > 0 ? $produksi->total_biaya / $produksi->qty_produksi : 0) . "\n";
} else {
    echo "\nData produksi completed tidak ditemukan\n";
}
