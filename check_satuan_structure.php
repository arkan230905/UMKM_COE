<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Satuan table structure...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('satuans');
echo "Satuan Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

// Check existing satuan data
$existingSatuan = \Illuminate\Support\Facades\DB::table('satuans')->limit(3)->get();
echo "\nExisting Satuan Data:\n";
foreach ($existingSatuan as $satuan) {
    echo "  " . ($satuan->kode_satuan ?? 'NULL') . " - " . ($satuan->nama_satuan ?? 'NULL') . " - User ID: " . ($satuan->user_id ?? 'NULL') . "\n";
}

echo "\nSatuan structure check completed!\n";
