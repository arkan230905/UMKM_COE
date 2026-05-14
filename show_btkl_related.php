<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DATA TERKAIT BTKL ===\n\n";

// Check btkls table
$btkls = \Illuminate\Support\Facades\DB::table('btkls')->get();
echo "1. Tabel BTKLS: " . count($btkls) . " records\n";
if (count($btkls) > 0) {
    foreach ($btkls as $b) {
        echo "   - " . json_encode($b) . "\n";
    }
}

// Check harga_pokok_produksi_btkl
$hpp_btkl = \Illuminate\Support\Facades\DB::table('harga_pokok_produksi_btkl')->get();
echo "\n2. Tabel HARGA_POKOK_PRODUKSI_BTKL: " . count($hpp_btkl) . " records\n";
if (count($hpp_btkl) > 0) {
    foreach ($hpp_btkl as $h) {
        echo "   - ID: " . $h->id . ", Produksi ID: " . $h->produksi_id . ", Biaya: " . $h->biaya_btkl . "\n";
    }
}

// Check penggajians
$penggajians = \Illuminate\Support\Facades\DB::table('penggajians')->get();
echo "\n3. Tabel PENGGAJIANS: " . count($penggajians) . " records\n";
if (count($penggajians) > 0) {
    foreach ($penggajians as $p) {
        echo "   - ID: " . $p->id . ", Pegawai ID: " . $p->pegawai_id . ", Total Gaji: " . $p->total_gaji . "\n";
    }
}

// Check produksi_btkl_details
$prod_btkl = \Illuminate\Support\Facades\DB::table('produksi_btkl_details')->get();
echo "\n4. Tabel PRODUKSI_BTKL_DETAILS: " . count($prod_btkl) . " records\n";
if (count($prod_btkl) > 0) {
    foreach ($prod_btkl as $p) {
        echo "   - " . json_encode($p) . "\n";
    }
}
