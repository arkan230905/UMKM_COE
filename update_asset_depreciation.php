<?php

require_once 'vendor/autoload.php';

use App\Models\Aset;
use Illuminate\Support\Facades\DB;

// Update existing asset with new depreciation calculation
echo "Updating asset depreciation values...\n";

try {
    // Find the asset (assuming ID 17 based on the URL)
    $asset = Aset::find(17);
    
    if (!$asset) {
        echo "Asset with ID 17 not found!\n";
        exit;
    }
    
    echo "Current values:\n";
    echo "- Penyusutan per Tahun: Rp " . number_format($asset->penyusutan_per_tahun, 2) . "\n";
    echo "- Penyusutan per Bulan: Rp " . number_format($asset->penyusutan_per_bulan, 2) . "\n";
    echo "- Tarif: " . $asset->tarif_penyusutan . "%\n";
    echo "- Harga Perolehan: Rp " . number_format($asset->harga_perolehan, 2) . "\n\n";
    
    // Update with new calculation
    $asset->updatePenyusutanValues();
    
    echo "Updated values:\n";
    echo "- Penyusutan per Tahun: Rp " . number_format($asset->penyusutan_per_tahun, 2) . "\n";
    echo "- Penyusutan per Bulan: Rp " . number_format($asset->penyusutan_per_bulan, 2) . "\n";
    
    // Show calculation
    $total = $asset->harga_perolehan + $asset->biaya_perolehan;
    $expectedTahunan = $total * ($asset->tarif_penyusutan / 100);
    $expectedBulanan = $expectedTahunan / 12;
    
    echo "\nExpected calculation:\n";
    echo "- Total Perolehan: Rp " . number_format($total, 2) . "\n";
    echo "- Tarif: " . $asset->tarif_penyusutan . "%\n";
    echo "- Expected Tahunan: Rp " . number_format($expectedTahunan, 2) . "\n";
    echo "- Expected Bulanan: Rp " . number_format($expectedBulanan, 2) . "\n";
    
    echo "\nAsset updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
