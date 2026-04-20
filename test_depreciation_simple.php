<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Aset;
use Carbon\Carbon;

echo "=== TESTING DEPRECIATION CALCULATIONS ===\n\n";

// Get assets with different methods
$assets = Aset::whereIn('metode_penyusutan', ['garis_lurus', 'saldo_menurun', 'sum_of_years_digits'])
    ->take(3)
    ->get();

if ($assets->isEmpty()) {
    echo "No assets found. Creating test data...\n";
    
    // Create test assets if none exist
    $testAssets = [
        [
            'nama_aset' => 'Test Straight Line',
            'metode_penyusutan' => 'garis_lurus',
            'harga_perolehan' => 1000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 100000,
            'umur_manfaat' => 5,
            'tanggal_akuisisi' => '2024-01-01'
        ],
        [
            'nama_aset' => 'Test Double Declining',
            'metode_penyusutan' => 'saldo_menurun',
            'harga_perolehan' => 1000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 100000,
            'umur_manfaat' => 5,
            'tanggal_akuisisi' => '2024-01-01'
        ],
        [
            'nama_aset' => 'Test Sum of Years',
            'metode_penyusutan' => 'sum_of_years_digits',
            'harga_perolehan' => 1000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 100000,
            'umur_manfaat' => 5,
            'tanggal_akuisisi' => '2024-01-01'
        ]
    ];
    
    foreach ($testAssets as $data) {
        Aset::create($data);
    }
    
    $assets = Aset::whereIn('metode_penyusutan', ['garis_lurus', 'saldo_menurun', 'sum_of_years_digits'])
        ->take(3)
        ->get();
}

foreach ($assets as $aset) {
    echo "ASSET: {$aset->nama_aset}\n";
    echo "Method: {$aset->metode_penyusutan}\n";
    echo "Cost: " . number_format($aset->harga_perolehan) . "\n";
    echo "Residual: " . number_format($aset->nilai_residu) . "\n";
    echo "Life: {$aset->umur_manfaat} years\n";
    echo "Acquisition: {$aset->tanggal_akuisisi}\n";
    
    // Calculate values
    $totalCost = $aset->harga_perolehan + $aset->biaya_perolehan;
    $depreciableAmount = $totalCost - $aset->nilai_residu;
    
    echo "Depreciable Amount: " . number_format($depreciableAmount) . "\n";
    
    // Current calculations
    $accumulated = $aset->hitungAkumulasiPenyusutanSaatIni();
    $monthly = $aset->hitungPenyusutanPerBulanSaatIni();
    $bookValue = $aset->getNilaiBukuRealTimeAttribute();
    
    echo "Current Accumulated: " . number_format($accumulated) . "\n";
    echo "Current Monthly: " . number_format($monthly) . "\n";
    echo "Current Book Value: " . number_format($bookValue) . "\n";
    
    // Manual verification
    echo "\nMANUAL VERIFICATION:\n";
    
    switch ($aset->metode_penyusutan) {
        case 'garis_lurus':
            $manualMonthly = $depreciableAmount / ($aset->umur_manfaat * 12);
            echo "Expected Monthly (Straight Line): " . number_format($manualMonthly) . "\n";
            echo "Match: " . ($monthly == $manualMonthly ? "YES" : "NO") . "\n";
            break;
            
        case 'saldo_menurun':
            $rate = 2 / $aset->umur_manfaat;
            $monthlyRate = $rate / 12;
            $expectedMonthly = $bookValue * $monthlyRate;
            echo "DD Rate (annual): " . ($rate * 100) . "%\n";
            echo "DD Rate (monthly): " . ($monthlyRate * 100) . "%\n";
            echo "Expected Monthly (DD): " . number_format($expectedMonthly) . "\n";
            echo "Match: " . (abs($monthly - $expectedMonthly) < 1 ? "YES" : "NO") . "\n";
            break;
            
        case 'sum_of_years_digits':
            $sumOfYears = ($aset->umur_manfaat * ($aset->umur_manfaat + 1)) / 2;
            
            // Calculate current year
            $startDate = Carbon::parse($aset->tanggal_akuisisi);
            if ($startDate->day > 15) {
                $startDate->addMonth()->day(1);
            } else {
                $startDate->day(1);
            }
            
            $monthsElapsed = $startDate->diffInMonths(Carbon::now()->startOfMonth()) + 1;
            $currentYear = ceil($monthsElapsed / 12);
            $remainingLife = $aset->umur_manfaat - $currentYear + 1;
            
            $yearlyDepreciation = ($depreciableAmount * $remainingLife) / $sumOfYears;
            $expectedMonthly = $yearlyDepreciation / 12;
            
            echo "Sum of Years: {$sumOfYears}\n";
            echo "Current Year: {$currentYear}\n";
            echo "Remaining Life: {$remainingLife}\n";
            echo "Expected Monthly (SYD): " . number_format($expectedMonthly) . "\n";
            echo "Match: " . (abs($monthly - $expectedMonthly) < 1 ? "YES" : "NO") . "\n";
            break;
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "Test completed!\n";