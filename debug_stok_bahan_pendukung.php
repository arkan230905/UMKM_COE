<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG STOK BAHAN PENDUKUNG ===\n\n";

// 1. Cek stok bahan pendukung saat ini
echo "1. STOK BAHAN PENDUKUNG SAAT INI:\n";
$bahanPendukungs = \App\Models\BahanPendukung::all();
foreach ($bahanPendukungs as $bp) {
    echo "- {$bp->nama_bahan}: {$bp->stok} {$bp->satuan->nama}\n";
}

echo "\n2. CEK PRODUKSI TERAKHIR (Konsumsi Bahan Pendukung):\n";
$produksi = \App\Models\Produksi::with(['details.bahanPendukung'])->orderBy('id', 'desc')->first();
if ($produksi) {
    echo "- Produksi ID: {$produksi->id}\n";
    echo "- Tanggal: {$produksi->tanggal}\n";
    echo "- Produk: {$produksi->produk->nama_produk}\n";
    
    echo "\nDetail Konsumsi Bahan Pendukung:\n";
    foreach ($produksi->details as $detail) {
        if ($detail->bahan_pendukung_id) {
            echo "- {$detail->bahanPendukung->nama_bahan}\n";
            echo "  Qty: {$detail->qty_resep} {$detail->satuan_resep}\n";
            echo "  Konversi: {$detail->qty_konversi}\n";
            echo "  Subtotal: Rp " . number_format($detail->subtotal) . "\n\n";
        }
    }
} else {
    echo "Tidak ada data produksi\n";
}

// 3. Cek stock movements untuk bahan pendukung
echo "3. STOCK MOVEMENTS BAHAN PENDUKUNG:\n";
$stockMovements = \DB::table('stock_movements')
    ->where('item_type', 'supporting_material')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

foreach ($stockMovements as $movement) {
    $item = \App\Models\BahanPendukung::find($movement->item_id);
    $itemName = $item ? $item->nama_bahan : 'Unknown';
    echo "- {$itemName}: {$movement->qty} ({$movement->direction}) - {$movement->ref_type} #{$movement->ref_id}\n";
}

// 4. Cek apakah ada konsumsi stok di produksi
echo "\n4. ANALISIS KONSUMSI STOK:\n";
if ($produksi) {
    foreach ($produksi->details as $detail) {
        if ($detail->bahan_pendukung_id) {
            $bp = \App\Models\BahanPendukung::find($detail->bahan_pendukung_id);
            if ($bp) {
                echo "- {$bp->nama_bahan}\n";
                echo "  Stok sebelum: ?\n";
                echo "  Dikonsumsi: {$detail->qty_konversi}\n";
                echo "  Stok sesudah: {$bp->stok}\n";
                echo "  Harusnya berkurang: {$detail->qty_konversi}\n\n";
            }
        }
    }
}

// 5. Cek penjualan terakhir
echo "5. PENJUALAN TERAKHIR (Konsumsi Produk):\n";
$penjualan = \App\Models\Penjualan::with(['details.produk'])->orderBy('id', 'desc')->first();
if ($penjualan) {
    echo "- Penjualan ID: {$penjualan->id}\n";
    echo "- Tanggal: {$penjualan->tanggal}\n";
    
    echo "\nDetail Produk Terjual:\n";
    foreach ($penjualan->details as $detail) {
        $produk = $detail->produk;
        echo "- {$produk->nama_produk}\n";
        echo "  Qty: {$detail->jumlah}\n";
        echo "  Stok saat ini: {$produk->stok}\n\n";
    }
} else {
    echo "Tidak ada data penjualan\n";
}

echo "\n=== SELESAI ===\n";
