<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Aset;
use App\Models\AssetDepreciation;

echo "Checking latest double declining asset:\n";
echo "=====================================\n";

$assets = Aset::where('metode_penyusutan', 'saldo_menurun')->orderBy('id', 'desc')->first();

if ($assets) {
    echo "Asset: " . $assets->nama_aset . " (ID: " . $assets->id . ")\n";
    echo "Metode: " . $assets->metode_penyusutan . "\n";
    echo "Tarif: " . $assets->tarif_penyusutan . "%\n";
    echo "Bulan Mulai: " . ($assets->bulan_mulai ?? 'NULL') . "\n";
    echo "Harga Perolehan: Rp " . number_format($assets->harga_perolehan, 2, ',', '.') . "\n";
    echo "Nilai Residu: Rp " . number_format($assets->nilai_residu ?? 0, 2, ',', '.') . "\n";
    echo "Umur: " . $assets->umur_manfaat . " tahun\n";
    
    // Check depreciation schedule
    $depreciation = AssetDepreciation::where('asset_id', $assets->id)->orderBy('tahun')->get();
    
    echo "\nDepreciation Schedule:\n";
    foreach ($depreciation as $row) {
        echo "Tahun " . $row->tahun . ": Beban=" . number_format($row->beban_penyusutan, 2) . ", Akumulasi=" . number_format($row->akumulasi_penyusutan, 2) . "\n";
    }
    
} else {
    echo "No double declining asset found!\n";
}
