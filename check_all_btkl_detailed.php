<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ALL PROSES PRODUKSI RECORDS ===\n\n";

$allProses = DB::table('proses_produksis')
    ->orderBy('id', 'asc')
    ->get();

echo "Total records: " . $allProses->count() . "\n\n";

foreach ($allProses as $p) {
    echo "ID: {$p->id}\n";
    echo "User ID: {$p->user_id}\n";
    echo "Kode: {$p->kode_proses}\n";
    echo "Nama: {$p->nama_proses}\n";
    echo "Tarif BTKL: Rp " . number_format($p->tarif_btkl, 2) . "\n";
    echo "Kapasitas: {$p->kapasitas_per_jam} pcs/jam\n";
    echo "Biaya Per Produk: Rp " . number_format($p->biaya_btkl_per_produk, 2) . "\n";
    echo "Created: {$p->created_at}\n";
    
    $expected = $p->kapasitas_per_jam > 0 ? $p->tarif_btkl / $p->kapasitas_per_jam : 0;
    if (abs($p->biaya_btkl_per_produk - $expected) < 0.01) {
        echo "Status: ✓ CORRECT\n";
    } else {
        echo "Status: ✗ WRONG (Expected: Rp " . number_format($expected, 2) . ")\n";
    }
    echo str_repeat("-", 50) . "\n\n";
}
