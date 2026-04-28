<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get BOP Proses data
$bopProses = DB::table('bop_proses')
    ->select('id', 'nama_bop_proses', 'proses_produksi_id', 'bop_per_unit', 'is_active', 'komponen_bop')
    ->get();

echo "Total BOP Proses: " . $bopProses->count() . "\n\n";

foreach ($bopProses as $bop) {
    echo "ID: {$bop->id}\n";
    echo "Nama BOP Proses: {$bop->nama_bop_proses}\n";
    echo "Proses Produksi ID: " . ($bop->proses_produksi_id ?? 'NULL') . "\n";
    echo "BOP per Unit: {$bop->bop_per_unit}\n";
    echo "Is Active: {$bop->is_active}\n";
    echo "Komponen BOP: " . substr($bop->komponen_bop ?? 'NULL', 0, 100) . "\n";
    echo "---\n\n";
}
