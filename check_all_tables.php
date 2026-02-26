<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Cek Semua Tabel Transaksi ===\n";

$tables = [
    'pembelians',
    'penggajians', 
    'penjualan_returns',
    'expense_payments',
    'purchase_returns'
];

foreach ($tables as $table) {
    echo "\n=== Tabel: $table ===\n";
    
    if (\Schema::hasTable($table)) {
        $columns = \Schema::getColumnListing($table);
        echo "Kolom: " . implode(', ', $columns) . "\n";
        
        // Cek sample data
        $sample = \DB::table($table)->limit(1)->first();
        if ($sample) {
            echo "Sample data: " . json_encode($sample) . "\n";
        } else {
            echo "Tidak ada data\n";
        }
    } else {
        echo "❌ Tabel tidak ada\n";
    }
}

echo "\n=== Cek Tabel Lain yang Mungkin Berkaitan ===\n";
$otherTables = [
    'pengeluaran',
    'biayas',
    'operational_costs'
];

foreach ($otherTables as $table) {
    echo "\n=== Tabel: $table ===\n";
    
    if (\Schema::hasTable($table)) {
        $columns = \Schema::getColumnListing($table);
        echo "Kolom: " . implode(', ', $columns) . "\n";
    } else {
        echo "❌ Tabel tidak ada\n";
    }
}
