<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BomJobBahanPendukung;
use App\Models\BomJobBBB;
use App\Models\BomJobCosting;

echo "=== FIX SUBTOTAL BAHAN ===\n\n";

// Fix Bahan Pendukung
echo "1. FIX BAHAN PENDUKUNG:\n";
$bahanPendukung = BomJobBahanPendukung::all();
foreach ($bahanPendukung as $bp) {
    $subtotalLama = $bp->subtotal;
    $subtotalBaru = $bp->jumlah * $bp->harga_satuan;
    
    echo "   ID: {$bp->id} | Qty: {$bp->jumlah} x Harga: {$bp->harga_satuan}\n";
    echo "   Subtotal Lama: Rp " . number_format($subtotalLama, 0) . "\n";
    echo "   Subtotal Baru: Rp " . number_format($subtotalBaru, 0) . "\n";
    
    $bp->subtotal = $subtotalBaru;
    $bp->save();
    echo "   --> FIXED!\n\n";
}

// Fix BBB
echo "2. FIX BAHAN BAKU (BBB):\n";
$bahanBaku = BomJobBBB::all();
foreach ($bahanBaku as $bb) {
    $subtotalLama = $bb->subtotal;
    $subtotalBaru = $bb->jumlah * $bb->harga_satuan;
    
    echo "   ID: {$bb->id} | Qty: {$bb->jumlah} x Harga: {$bb->harga_satuan}\n";
    echo "   Subtotal Lama: Rp " . number_format($subtotalLama, 0) . "\n";
    echo "   Subtotal Baru: Rp " . number_format($subtotalBaru, 0) . "\n";
    
    $bb->subtotal = $subtotalBaru;
    $bb->save();
    echo "   --> FIXED!\n\n";
}

// Recalculate semua BOM
echo "3. RECALCULATE SEMUA BOM:\n";
$boms = BomJobCosting::with('produk')->get();
foreach ($boms as $bom) {
    echo "   Produk: " . ($bom->produk->nama_produk ?? 'N/A') . "\n";
    echo "   HPP Lama: Rp " . number_format($bom->total_hpp, 0) . "\n";
    
    $bom->recalculate();
    $bom->refresh();
    
    echo "   HPP Baru: Rp " . number_format($bom->total_hpp, 0) . "\n\n";
}

echo "=== SELESAI ===\n";
