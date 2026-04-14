<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BTKL DATA ===\n";

// Check proses_produksis table
$proses = DB::table('proses_produksis')->get();
echo "Total BTKL records: " . $proses->count() . "\n\n";

foreach($proses as $p) {
    echo "ID: {$p->id}\n";
    echo "Kode: " . ($p->kode_proses ?? 'NULL') . "\n";
    echo "Nama: {$p->nama_proses}\n";
    echo "Jabatan ID: " . ($p->jabatan_id ?? 'NULL') . "\n";
    echo "Tarif BTKL: {$p->tarif_btkl}\n";
    
    // Check jabatan data
    if ($p->jabatan_id) {
        $jabatan = DB::table('jabatans')->where('id', $p->jabatan_id)->first();
        if ($jabatan) {
            echo "Jabatan Nama: {$jabatan->nama}\n";
            echo "Jabatan Tarif: {$jabatan->tarif}\n";
        } else {
            echo "Jabatan: NOT FOUND\n";
        }
    } else {
        echo "Jabatan: NULL\n";
    }
    echo "---\n";
}

// Check BOM data for process names
echo "\n=== CHECKING BOM BTKL DATA ===\n";
$bomBtkl = DB::table('bom_job_btkls')->get();
echo "Total BOM BTKL records: " . $bomBtkl->count() . "\n\n";

foreach($bomBtkl as $b) {
    echo "ID: {$b->id}\n";
    echo "BOM Job ID: {$b->bom_job_id}\n";
    echo "Proses Produksi: {$b->proses_produksi}\n";
    echo "Nama Proses: " . ($b->nama_proses ?? 'NULL') . "\n";
    echo "---\n";
}