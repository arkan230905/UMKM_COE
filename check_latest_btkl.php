<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING LATEST BTKL DATA ===\n\n";

$proses = DB::table('proses_produksis')
    ->orderBy('id', 'desc')
    ->first();

if ($proses) {
    echo "Latest Record:\n";
    echo "ID: {$proses->id}\n";
    echo "Nama: {$proses->nama_proses}\n";
    echo "Tarif BTKL: Rp " . number_format($proses->tarif_btkl, 2) . "\n";
    echo "Kapasitas: {$proses->kapasitas_per_jam} pcs/jam\n";
    echo "Biaya Per Produk (DB): Rp " . number_format($proses->biaya_btkl_per_produk, 2) . "\n";
    
    $expected = $proses->kapasitas_per_jam > 0 ? $proses->tarif_btkl / $proses->kapasitas_per_jam : 0;
    echo "Biaya Per Produk (Expected): Rp " . number_format($expected, 2) . "\n";
    
    if (abs($proses->biaya_btkl_per_produk - $expected) < 0.01) {
        echo "\n✓ CORRECT! Database value matches expected value\n";
    } else {
        echo "\n✗ ERROR! Database value doesn't match\n";
    }
} else {
    echo "No data found\n";
}

echo "\n=== ALL RECORDS ===\n";
$allProses = DB::table('proses_produksis')
    ->where('user_id', 2)
    ->orderBy('id', 'desc')
    ->get();

foreach ($allProses as $p) {
    echo "\nID {$p->id}: {$p->nama_proses}\n";
    echo "  Tarif: Rp " . number_format($p->tarif_btkl, 2) . " | ";
    echo "Kapasitas: {$p->kapasitas_per_jam} | ";
    echo "Biaya/Produk: Rp " . number_format($p->biaya_btkl_per_produk, 2);
    
    if ($p->biaya_btkl_per_produk > 0) {
        echo " ✓\n";
    } else {
        echo " ✗\n";
    }
}
