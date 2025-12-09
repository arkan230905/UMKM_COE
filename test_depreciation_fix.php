<?php

require_once 'vendor/autoload.php';

use App\Services\AssetDepreciationService;
use App\Models\Aset;
use Carbon\Carbon;

// Create a mock asset for testing
$asset = new Aset();
$asset->id = 1;
$asset->harga_perolehan = 500000000; // Rp 500 juta
$asset->biaya_perolehan = 0;
$asset->nilai_residu = 0;
$asset->umur_manfaat = 5; // 5 tahun
$asset->tanggal_beli = '2025-02-01'; // 1 Februari 2025
$asset->metode_penyusutan = 'garis_lurus'; // Straight line method
$asset->tarif_penyusutan = 20; // 20% per tahun

echo "Test Asset Depreciation Calculation\n";
echo "====================================\n";
echo "Asset Details:\n";
echo "- Acquisition Date: " . $asset->tanggal_beli . "\n";
echo "- Cost: Rp " . number_format($asset->harga_perolehan, 2) . "\n";
echo "- Useful Life: " . $asset->umur_manfaat . " years\n";
echo "- Method: " . $asset->metode_penyusutan . "\n\n";

// Calculate expected values
$cost = $asset->harga_perolehan + $asset->biaya_perolehan;
$annualDepreciation = $cost / $asset->umur_manfaat;
$bulanBeli = Carbon::parse($asset->tanggal_beli)->month; // February = 2
$monthsInFirstYear = 12 - $bulanBeli + 1; // 11 months
$firstYearDepreciation = $annualDepreciation * ($monthsInFirstYear / 12);

echo "Expected Calculation:\n";
echo "- Annual Depreciation: Rp " . number_format($annualDepreciation, 2) . "\n";
echo "- Months in First Year: " . $monthsInFirstYear . " (Feb-Dec)\n";
echo "- First Year Depreciation: Rp " . number_format($firstYearDepreciation, 2) . "\n";
echo "- Formula: (" . $monthsInFirstYear . "/12) × Rp " . number_format($annualDepreciation, 2) . " = Rp " . number_format($firstYearDepreciation, 2) . "\n\n";

echo "Verification:\n";
echo "- First Year / Annual Ratio: " . ($firstYearDepreciation / $annualDepreciation) . " (should be " . ($monthsInFirstYear / 12) . ")\n";
