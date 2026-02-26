<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Struktur Tabel coa_period_balances ===\n";

if (\Schema::hasTable('coa_period_balances')) {
    $columns = \Schema::getColumnListing('coa_period_balances');
    echo "Kolom di tabel coa_period_balances:\n";
    foreach ($columns as $col) {
        echo "- $col\n";
    }
    
    // Cek sample data
    echo "\n=== Sample Data ===\n";
    $sample = \DB::table('coa_period_balances')->limit(3)->get();
    foreach ($sample as $row) {
        echo "ID: " . ($row->id ?? 'N/A') . "\n";
        echo "COA ID: " . ($row->coa_id ?? 'N/A') . "\n";
        echo "COA Period ID: " . ($row->coa_period_id ?? 'N/A') . "\n";
        echo "Saldo Akhir: " . ($row->saldo_akhir ?? 'N/A') . "\n";
        echo "---\n";
    }
} else {
    echo "Tabel coa_period_balances tidak ada\n";
}

echo "\n=== Struktur Tabel coa_periods ===\n";
if (\Schema::hasTable('coa_periods')) {
    $columns = \Schema::getColumnListing('coa_periods');
    echo "Kolom di tabel coa_periods:\n";
    foreach ($columns as $col) {
        echo "- $col\n";
    }
    
    // Cek sample data
    echo "\n=== Sample Data coa_periods ===\n";
    $sample = \DB::table('coa_periods')->limit(3)->get();
    foreach ($sample as $row) {
        echo "ID: " . ($row->id ?? 'N/A') . "\n";
        echo "Periode: " . ($row->periode ?? 'N/A') . "\n";
        echo "---\n";
    }
} else {
    echo "Tabel coa_periods tidak ada\n";
}
