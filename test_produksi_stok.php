<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST PRODUKSI KONSUMSI STOK ===\n\n";

// 1. Cek stok sebelum produksi
echo "1. STOK SEBELUM PRODUKSI:\n";
$airBersih = \App\Models\BahanPendukung::where('nama_bahan', 'Air Bersih')->first();
$ayamGeprek = \App\Models\Produk::where('nama_produk', 'Ayam Geprek')->first();

if ($airBersih) {
    echo "- Air Bersih: {$airBersih->stok} {$airBersih->satuan->nama}\n";
}
if ($ayamGeprek) {
    echo "- Ayam Geprek: {$ayamGeprek->stok} unit\n";
}

// 2. Buat produksi baru untuk test
echo "\n2. MEMBUAT PRODUKSI BARU:\n";
$produk = $ayamGeprek;
if (!$produk) {
    echo "Produk Ayam Geprek tidak ditemukan\n";
    exit;
}

$qtyProduksi = 5;
$tanggal = now()->toDateString();

echo "- Produk: {$produk->nama_produk}\n";
echo "- Qty: {$qtyProduksi}\n";
echo "- Tanggal: {$tanggal}\n";

// Manual test - langsung konsumsi stok
echo "\n3. MANUAL TEST KONSUMSI STOK:\n";

// Konsumsi bahan pendukung
$bahanPendukung = \App\Models\BahanPendukung::where('nama_bahan', 'Air Bersih')->first();
if ($bahanPendukung) {
    $qtyKonsumsi = 0.02 * $qtyProduksi; // 0.02 Liter per unit
    echo "- Konsumsi {$bahanPendukung->nama_bahan}: {$qtyKonsumsi} Liter\n";
    
    // Update stok master
    $bahanPendukung->stok = (float)$bahanPendukung->stok - $qtyKonsumsi;
    $bahanPendukung->save();
    
    // Konsumsi dari stock layers
    $stock = app(\App\Services\StockService::class);
    $stock->consume('supporting_material', $bahanPendukung->id, $qtyKonsumsi, 'Liter', 'production', 999, $tanggal);
    
    echo "- Stok setelah: {$bahanPendukung->fresh()->stok} Liter\n";
}

// Tambah stok produk jadi
$produk->stok = (float)$produk->stok + $qtyProduksi;
$produk->save();

echo "- Tambah stok produk: {$produk->nama_produk} +{$qtyProduksi} = {$produk->fresh()->stok} unit\n";

$airBersihAfter = \App\Models\BahanPendukung::where('nama_bahan', 'Air Bersih')->first();
$ayamGeprekAfter = \App\Models\Produk::where('nama_produk', 'Ayam Geprek')->first();

if ($airBersihAfter) {
    echo "- Air Bersih: {$airBersihAfter->stok} {$airBersihAfter->satuan->nama}\n";
    if ($airBersih) {
        $selisih = $airBersihAfter->stok - $airBersih->stok;
        echo "- Selisih: {$selisih} {$airBersihAfter->satuan->nama}\n";
    }
}
if ($ayamGeprekAfter) {
    echo "- Ayam Geprek: {$ayamGeprekAfter->stok} unit\n";
    if ($ayamGeprek) {
        $selisih = $ayamGeprekAfter->stok - $ayamGeprek->stok;
        echo "- Selisih: {$selisih} unit\n";
    }
}

// 4. Cek stock movements
echo "\n4. STOCK MOVEMENTS TERAKHIR:\n";
$movements = \DB::table('stock_movements')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

foreach ($movements as $movement) {
    $itemName = 'Unknown';
    if ($movement->item_type === 'product') {
        $item = \App\Models\Produk::find($movement->item_id);
        $itemName = $item ? $item->nama_produk : 'Unknown Product';
    } elseif ($movement->item_type === 'supporting_material') {
        $item = \App\Models\BahanPendukung::find($movement->item_id);
        $itemName = $item ? $item->nama_bahan : 'Unknown Material';
    }
    
    echo "- {$itemName}: {$movement->qty} ({$movement->direction}) - {$movement->ref_type} #{$movement->ref_id}\n";
}

echo "\n=== SELESAI ===\n";
