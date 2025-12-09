<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;

echo "Checking asset ID 17:\n";
echo "====================\n";

$aset = Aset::find(17);

if ($aset) {
    echo "Asset: " . $aset->nama_aset . "\n";
    echo "Metode Penyusutan: " . $aset->metode_penyusutan . "\n";
    echo "Tarif Penyusutan: " . $aset->tarif_penyusutan . "%\n";
    echo "Harga Perolehan: Rp " . number_format($aset->harga_perolehan, 2, ',', '.') . "\n";
    
    // Test calculation
    $total = (float)$aset->harga_perolehan + (float)($aset->biaya_perolehan ?? 0);
    $tahunan = $total * ($aset->tarif_penyusutan / 100);
    $bulanan = $tahunan / 12;
    
    echo "\nExpected Display:\n";
    echo "Penyusutan Per Tahun: Rp " . number_format($tahunan, 0, ',', '.') . "\n";
    echo "Penyusutan Per Bulan: Rp " . number_format($bulanan, 0, ',', '.') . "\n";
    
} else {
    echo "Asset not found!\n";
}
