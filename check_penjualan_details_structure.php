<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking PenjualanDetails table structure...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('penjualan_details');
echo "PenjualanDetails Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

// Check existing penjualan details
echo "\n=== Existing Penjualan Details ===\n";
$details = \Illuminate\Support\Facades\DB::table('penjualan_details')->get();
echo "Total records: " . $details->count() . "\n";

foreach ($details as $detail) {
    echo "ID: {$detail->id}, Produk ID: {$detail->produk_id}, Harga: " . ($detail->harga_satuan ?? 'NULL') . "\n";
}

echo "\nPenjualanDetails structure check completed!\n";
