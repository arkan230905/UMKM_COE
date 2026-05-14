<?php

/**
 * Script untuk memperbaiki biaya_btkl_per_produk yang masih 0
 * Jalankan: php fix_biaya_btkl_per_produk.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FIX BIAYA BTKL PER PRODUK ===\n\n";

// Get all proses_produksis with biaya_btkl_per_produk = 0
$prosesList = DB::table('proses_produksis')
    ->where('biaya_btkl_per_produk', 0)
    ->orWhereNull('biaya_btkl_per_produk')
    ->get();

echo "Found " . $prosesList->count() . " records with biaya_btkl_per_produk = 0\n\n";

$updated = 0;
$skipped = 0;

foreach ($prosesList as $proses) {
    if ($proses->kapasitas_per_jam > 0 && $proses->tarif_btkl > 0) {
        $biayaPerProduk = $proses->tarif_btkl / $proses->kapasitas_per_jam;
        
        DB::table('proses_produksis')
            ->where('id', $proses->id)
            ->update(['biaya_btkl_per_produk' => $biayaPerProduk]);
        
        echo "✓ Updated ID {$proses->id} ({$proses->nama_proses}): ";
        echo "Rp " . number_format($proses->tarif_btkl, 2) . " ÷ {$proses->kapasitas_per_jam} = ";
        echo "Rp " . number_format($biayaPerProduk, 2) . "\n";
        
        $updated++;
    } else {
        echo "⊘ Skipped ID {$proses->id} ({$proses->nama_proses}): ";
        echo "kapasitas_per_jam = {$proses->kapasitas_per_jam}, tarif_btkl = {$proses->tarif_btkl}\n";
        $skipped++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Updated: $updated records\n";
echo "Skipped: $skipped records\n";
echo "\n=== DONE ===\n";
