<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BomJobCosting;
use App\Models\BomJobBBB;
use App\Models\BomJobBahanPendukung;
use App\Models\BomJobBTKL;
use App\Models\BomJobBOP;

echo "=== CEK DETAIL SEMUA BAHAN ===\n\n";

$boms = BomJobCosting::with([
    'produk',
    'detailBBB.bahanBaku', 
    'detailBahanPendukung.bahanPendukung',
    'detailBTKL',
    'detailBOP'
])->get();

foreach ($boms as $bom) {
    echo "========================================\n";
    echo "PRODUK: " . ($bom->produk->nama_produk ?? 'N/A') . "\n";
    echo "========================================\n\n";
    
    echo "1. BAHAN BAKU (BBB):\n";
    $totalBBB = 0;
    foreach ($bom->detailBBB as $d) {
        $subtotal = $d->subtotal ?? ($d->jumlah * $d->harga_satuan);
        $totalBBB += $subtotal;
        echo "   - " . ($d->bahanBaku->nama_bahan ?? 'N/A') . "\n";
        echo "     Qty: " . $d->jumlah . " | Harga: Rp " . number_format($d->harga_satuan, 0) . " | Subtotal: Rp " . number_format($subtotal, 0) . "\n";
    }
    echo "   TOTAL BBB: Rp " . number_format($totalBBB, 0) . "\n\n";
    
    echo "2. BAHAN PENDUKUNG:\n";
    $totalBP = 0;
    foreach ($bom->detailBahanPendukung as $d) {
        $subtotal = $d->subtotal ?? ($d->jumlah * $d->harga_satuan);
        $totalBP += $subtotal;
        echo "   - " . ($d->bahanPendukung->nama ?? 'N/A') . "\n";
        echo "     Qty: " . $d->jumlah . " | Harga: Rp " . number_format($d->harga_satuan, 0) . " | Subtotal: Rp " . number_format($subtotal, 0) . "\n";
    }
    echo "   TOTAL BAHAN PENDUKUNG: Rp " . number_format($totalBP, 0) . "\n\n";
    
    echo "3. BTKL (Biaya Tenaga Kerja Langsung):\n";
    $totalBTKL = 0;
    foreach ($bom->detailBTKL as $d) {
        $subtotal = $d->subtotal ?? 0;
        $totalBTKL += $subtotal;
        echo "   - " . ($d->nama_kegiatan ?? 'N/A') . ": Rp " . number_format($subtotal, 0) . "\n";
    }
    echo "   TOTAL BTKL: Rp " . number_format($totalBTKL, 0) . "\n\n";
    
    echo "4. BOP (Biaya Overhead Pabrik):\n";
    $totalBOP = 0;
    foreach ($bom->detailBOP as $d) {
        $subtotal = $d->subtotal ?? 0;
        $totalBOP += $subtotal;
        echo "   - " . ($d->nama_biaya ?? 'N/A') . ": Rp " . number_format($subtotal, 0) . "\n";
    }
    echo "   TOTAL BOP: Rp " . number_format($totalBOP, 0) . "\n\n";
    
    $grandTotal = $totalBBB + $totalBP + $totalBTKL + $totalBOP;
    echo "==> TOTAL HPP (BBB + BP + BTKL + BOP): Rp " . number_format($grandTotal, 0) . "\n";
    echo "==> Di Database total_hpp: Rp " . number_format($bom->total_hpp, 0) . "\n\n";
}
