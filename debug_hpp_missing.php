<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG HPP MISSING ===" . PHP_EOL;

// Load penjualan test
$penjualan = \App\Models\Penjualan::with('details.produk')->find(2);
if (!$penjualan) {
    echo "Penjualan test tidak ditemukan" . PHP_EOL;
    exit;
}

echo "Penjualan: " . $penjualan->nomor_penjualan . PHP_EOL;
echo "Details count: " . $penjualan->details->count() . PHP_EOL;

foreach ($penjualan->details as $detail) {
    echo "- Produk: " . $detail->produk->nama_produk . PHP_EOL;
    echo "- Qty: " . $detail->jumlah . PHP_EOL;
    echo "- HPP Produk: " . $detail->produk->hpp . PHP_EOL;
    echo "- Harga Pokok: " . $detail->produk->harga_pokok . PHP_EOL;
    echo "- Total HPP: " . ($detail->produk->hpp * $detail->jumlah) . PHP_EOL;
    echo "- coa_persediaan_id: " . ($detail->produk->coa_persediaan_id ?? 'NULL') . PHP_EOL;
}

// Test manual HPP calculation
echo PHP_EOL . "=== MANUAL HPP CALCULATION ===" . PHP_EOL;

$items = [];
if ($penjualan->details && $penjualan->details->count() > 0) {
    foreach ($penjualan->details as $detail) {
        $produk = $detail->produk;
        if (!$produk) continue;
        $items[] = ['produk' => $produk, 'qty' => (float)($detail->jumlah ?? 0)];
    }
}

echo "Items untuk HPP: " . count($items) . PHP_EOL;

foreach ($items as $index => $item) {
    $produk = $item['produk'];
    $qty    = $item['qty'];
    
    echo PHP_EOL . "Item " . ($index + 1) . ":" . PHP_EOL;
    echo "- Produk: " . $produk->nama_produk . PHP_EOL;
    echo "- Qty: " . $qty . PHP_EOL;
    
    if ($qty <= 0) {
        echo "- SKIP: Qty <= 0" . PHP_EOL;
        continue;
    }

    // Nilai HPP per unit
    $hppPerUnit = (float)($produk->hpp ?? $produk->harga_pokok ?? $produk->harga_bom ?? 0);
    $totalHPP   = round($hppPerUnit * $qty);
    
    echo "- HPP per unit: " . $hppPerUnit . PHP_EOL;
    echo "- Total HPP: " . $totalHPP . PHP_EOL;
    
    if ($totalHPP <= 0) {
        echo "- SKIP: Total HPP <= 0" . PHP_EOL;
        continue;
    }
    
    echo "- WILL CREATE HPP JOURNAL" . PHP_EOL;
}
