<?php

// Simple script to check current database values
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;

echo "Checking current database values for asset ID 17:\n";
echo "================================================\n";

$aset = Aset::find(17);

if ($aset) {
    echo "Asset: " . $aset->nama_aset . " (ID: " . $aset->id . ")\n";
    echo "Harga Perolehan: Rp " . number_format($aset->harga_perolehan, 2) . "\n";
    echo "Biaya Perolehan: Rp " . number_format($aset->biaya_perolehan ?? 0, 2) . "\n";
    echo "Tarif Penyusutan: " . $aset->tarif_penyusutan . "%\n";
    echo "\n";
    echo "Database Values:\n";
    echo "- penyusutan_per_tahun: Rp " . number_format($aset->penyusutan_per_tahun, 2) . "\n";
    echo "- penyusutan_per_bulan: Rp " . number_format($aset->penyusutan_per_bulan, 2) . "\n";
    echo "\n";
    echo "Accessor Values:\n";
    echo "- depreciation_per_year: Rp " . number_format($aset->depreciation_per_year, 2) . "\n";
    echo "\n";
    echo "Method Values:\n";
    echo "- hitungBebanPenyusutanTahunan(): Rp " . number_format($aset->hitungBebanPenyusutanTahunan(), 2) . "\n";
    echo "- hitungBebanPenyusutanBulanan(): Rp " . number_format($aset->hitungBebanPenyusutanBulanan(), 2) . "\n";
} else {
    echo "Asset not found!\n";
}
