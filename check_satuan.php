<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Satuan;

echo "=== DATA SATUAN ===\n\n";

$satuans = Satuan::orderBy('kode')->get();

echo "Total: " . $satuans->count() . " satuan\n\n";

echo str_pad("Kode", 10) . str_pad("Nama", 20) . str_pad("Kategori", 15) . "Faktor\n";
echo str_repeat("-", 60) . "\n";

foreach ($satuans as $satuan) {
    echo str_pad($satuan->kode, 10) . 
         str_pad($satuan->nama, 20) . 
         str_pad($satuan->kategori, 15) . 
         $satuan->faktor_ke_dasar . "\n";
}

echo "\n✓ Data Satuan lengkap!\n";
