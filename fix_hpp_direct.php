<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIX HPP DIRECT SQL ===\n\n";

// 1. Fix subtotal bahan pendukung
echo "1. FIX SUBTOTAL BAHAN PENDUKUNG:\n";
DB::statement("UPDATE bom_job_bahan_pendukung SET subtotal = jumlah * harga_satuan");
echo "   Done!\n\n";

// 2. Fix subtotal BBB
echo "2. FIX SUBTOTAL BBB:\n";
DB::statement("UPDATE bom_job_bbb SET subtotal = jumlah * harga_satuan");
echo "   Done!\n\n";

// 3. Update total di bom_job_costings
echo "3. UPDATE TOTAL DI BOM_JOB_COSTINGS:\n";

$boms = DB::table('bom_job_costings')->get();
foreach ($boms as $bom) {
    $totalBBB = DB::table('bom_job_bbb')
        ->where('bom_job_costing_id', $bom->id)
        ->sum('subtotal');
    
    $totalBP = DB::table('bom_job_bahan_pendukung')
        ->where('bom_job_costing_id', $bom->id)
        ->sum('subtotal');
    
    $totalBTKL = DB::table('bom_job_btkl')
        ->where('bom_job_costing_id', $bom->id)
        ->sum('subtotal');
    
    $totalBOP = DB::table('bom_job_bop')
        ->where('bom_job_costing_id', $bom->id)
        ->sum('subtotal');
    
    $totalHPP = $totalBBB + $totalBP + $totalBTKL + $totalBOP;
    $hppPerUnit = $bom->jumlah_produk > 0 ? $totalHPP / $bom->jumlah_produk : 0;
    
    $produk = DB::table('produks')->where('id', $bom->produk_id)->first();
    
    echo "   Produk: " . ($produk->nama_produk ?? 'N/A') . "\n";
    echo "   - Total BBB: Rp " . number_format($totalBBB, 0) . "\n";
    echo "   - Total Bahan Pendukung: Rp " . number_format($totalBP, 0) . "\n";
    echo "   - Total BTKL: Rp " . number_format($totalBTKL, 0) . "\n";
    echo "   - Total BOP: Rp " . number_format($totalBOP, 0) . "\n";
    echo "   - TOTAL HPP: Rp " . number_format($totalHPP, 0) . "\n\n";
    
    DB::table('bom_job_costings')
        ->where('id', $bom->id)
        ->update([
            'total_bbb' => $totalBBB,
            'total_bahan_pendukung' => $totalBP,
            'total_btkl' => $totalBTKL,
            'total_bop' => $totalBOP,
            'total_hpp' => $totalHPP,
            'hpp_per_unit' => $hppPerUnit
        ]);
}

echo "=== SELESAI ===\n";
