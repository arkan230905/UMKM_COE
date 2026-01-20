<?php
/**
 * Script untuk recalculate semua BOM Job Costing HPP
 * Jalankan: php recalculate_bom_hpp.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BomJobCosting;
use App\Models\Produk;

echo "=== RECALCULATE BOM HPP ===\n\n";

$bomJobCostings = BomJobCosting::with(['detailBBB', 'detailBTKL', 'detailBahanPendukung', 'detailBOP', 'produk'])->get();

echo "Total BOM Job Costing: " . $bomJobCostings->count() . "\n\n";

foreach ($bomJobCostings as $bom) {
    echo "----------------------------------------\n";
    echo "Produk: " . ($bom->produk->nama_produk ?? 'N/A') . "\n";
    echo "Jumlah Produk: " . $bom->jumlah_produk . "\n\n";
    
    // Hitung ulang dari detail
    $totalBBB = $bom->detailBBB->sum('subtotal');
    $totalBTKL = $bom->detailBTKL->sum('subtotal');
    $totalBahanPendukung = $bom->detailBahanPendukung->sum('subtotal');
    $totalBOP = $bom->detailBOP->sum('subtotal');
    
    echo "Detail BBB: " . $bom->detailBBB->count() . " item\n";
    echo "Detail BTKL: " . $bom->detailBTKL->count() . " item\n";
    echo "Detail Bahan Pendukung: " . $bom->detailBahanPendukung->count() . " item\n";
    echo "Detail BOP: " . $bom->detailBOP->count() . " item\n\n";
    
    echo "SEBELUM:\n";
    echo "  Total BBB: Rp " . number_format($bom->total_bbb, 0, ',', '.') . "\n";
    echo "  Total BTKL: Rp " . number_format($bom->total_btkl, 0, ',', '.') . "\n";
    echo "  Total Bahan Pendukung: Rp " . number_format($bom->total_bahan_pendukung, 0, ',', '.') . "\n";
    echo "  Total BOP: Rp " . number_format($bom->total_bop, 0, ',', '.') . "\n";
    echo "  Total HPP: Rp " . number_format($bom->total_hpp, 0, ',', '.') . "\n\n";
    
    // Recalculate
    $bom->recalculate();
    $bom->refresh();
    
    $totalHPP = $bom->total_bbb + $bom->total_bahan_pendukung + $bom->total_btkl + $bom->total_bop;
    
    echo "SESUDAH:\n";
    echo "  Total BBB: Rp " . number_format($bom->total_bbb, 0, ',', '.') . "\n";
    echo "  Total BTKL: Rp " . number_format($bom->total_btkl, 0, ',', '.') . "\n";
    echo "  Total Bahan Pendukung: Rp " . number_format($bom->total_bahan_pendukung, 0, ',', '.') . "\n";
    echo "  Total BOP: Rp " . number_format($bom->total_bop, 0, ',', '.') . "\n";
    echo "  Total HPP: Rp " . number_format($bom->total_hpp, 0, ',', '.') . "\n";
    echo "  HPP Per Unit: Rp " . number_format($bom->hpp_per_unit, 0, ',', '.') . "\n\n";
}

echo "=== SELESAI ===\n";
