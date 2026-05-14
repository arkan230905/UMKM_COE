<?php

/**
 * Debug script untuk mengecek perhitungan BTKL
 * Jalankan: php debug_btkl_calculation.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUG BTKL CALCULATION ===\n\n";

// Test calculation
$tarifBtkl = 20000;
$kapasitas = 120;
$biayaPerProduk = $tarifBtkl / $kapasitas;

echo "Test Calculation:\n";
echo "Tarif BTKL: Rp " . number_format($tarifBtkl, 2) . "\n";
echo "Kapasitas: $kapasitas pcs/jam\n";
echo "Biaya Per Produk: Rp " . number_format($biayaPerProduk, 2) . "\n";
echo "Expected: Rp 166.67\n\n";

// Check database column type
echo "=== DATABASE COLUMN INFO ===\n";
$columns = DB::select("SHOW COLUMNS FROM proses_produksis WHERE Field = 'biaya_btkl_per_produk'");
if (!empty($columns)) {
    $column = $columns[0];
    echo "Column: {$column->Field}\n";
    echo "Type: {$column->Type}\n";
    echo "Null: {$column->Null}\n";
    echo "Default: {$column->Default}\n\n";
}

// Check existing data
echo "=== EXISTING DATA ===\n";
$proses = DB::table('proses_produksis')
    ->where('user_id', 2)
    ->orderBy('id', 'desc')
    ->first();

if ($proses) {
    echo "ID: {$proses->id}\n";
    echo "Kode Proses: {$proses->kode_proses}\n";
    echo "Tarif BTKL: Rp " . number_format($proses->tarif_btkl, 2) . "\n";
    echo "Kapasitas: {$proses->kapasitas_per_jam} pcs/jam\n";
    echo "Biaya BTKL Per Produk (DB): Rp " . number_format($proses->biaya_btkl_per_produk, 2) . "\n";
    
    $calculated = $proses->kapasitas_per_jam > 0 ? $proses->tarif_btkl / $proses->kapasitas_per_jam : 0;
    echo "Biaya BTKL Per Produk (Calculated): Rp " . number_format($calculated, 2) . "\n";
    
    if (abs($proses->biaya_btkl_per_produk - $calculated) > 0.01) {
        echo "\n⚠️  WARNING: Database value doesn't match calculated value!\n";
        echo "Difference: Rp " . number_format(abs($proses->biaya_btkl_per_produk - $calculated), 2) . "\n";
    } else {
        echo "\n✓ Database value matches calculated value\n";
    }
} else {
    echo "No data found for user_id = 2\n";
}

echo "\n=== DONE ===\n";
