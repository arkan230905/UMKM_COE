<?php

// Manual depreciation calculation test without Laravel bootstrap
echo "=== MANUAL DEPRECIATION CALCULATION TEST ===\n\n";

// Test data (simulating asset data)
$testAssets = [
    [
        'nama' => 'Test Straight Line Asset',
        'metode' => 'garis_lurus',
        'harga_perolehan' => 1000000,
        'nilai_residu' => 100000,
        'umur_manfaat' => 5, // years
        'tanggal_akuisisi' => '2024-01-01'
    ],
    [
        'nama' => 'Test Double Declining Asset',
        'metode' => 'saldo_menurun',
        'harga_perolehan' => 1000000,
        'nilai_residu' => 100000,
        'umur_manfaat' => 5, // years
        'tanggal_akuisisi' => '2024-01-01'
    ],
    [
        'nama' => 'Test Sum of Years Asset',
        'metode' => 'sum_of_years_digits',
        'harga_perolehan' => 1000000,
        'nilai_residu' => 100000,
        'umur_manfaat' => 5, // years
        'tanggal_akuisisi' => '2024-01-01'
    ]
];

foreach ($testAssets as $asset) {
    echo "ASSET: {$asset['nama']}\n";
    echo "Method: {$asset['metode']}\n";
    echo "Cost: " . number_format($asset['harga_perolehan']) . "\n";
    echo "Residual: " . number_format($asset['nilai_residu']) . "\n";
    echo "Life: {$asset['umur_manfaat']} years\n";
    echo "Acquisition: {$asset['tanggal_akuisisi']}\n";
    
    $totalCost = $asset['harga_perolehan'];
    $residualValue = $asset['nilai_residu'];
    $usefulLife = $asset['umur_manfaat'];
    $depreciableAmount = $totalCost - $residualValue;
    
    echo "Depreciable Amount: " . number_format($depreciableAmount) . "\n";
    
    // Calculate months elapsed from acquisition to now (April 2026)
    $acquisitionDate = new DateTime($asset['tanggal_akuisisi']);
    $currentDate = new DateTime('2026-04-20'); // Current date from context
    
    // Apply the rule: if day > 15, start next month
    if ($acquisitionDate->format('d') > 15) {
        $acquisitionDate->modify('first day of next month');
    } else {
        $acquisitionDate->modify('first day of this month');
    }
    
    $monthsElapsed = $acquisitionDate->diff($currentDate)->m + ($acquisitionDate->diff($currentDate)->y * 12);
    $currentYear = ceil(($monthsElapsed + 1) / 12);
    
    echo "Months Elapsed: {$monthsElapsed}\n";
    echo "Current Year of Depreciation: {$currentYear}\n";
    
    switch ($asset['metode']) {
        case 'garis_lurus':
            echo "\n--- STRAIGHT LINE METHOD ---\n";
            $monthlyDepreciation = $depreciableAmount / ($usefulLife * 12);
            $accumulatedDepreciation = $monthlyDepreciation * $monthsElapsed;
            $bookValue = $totalCost - $accumulatedDepreciation;
            
            echo "Monthly Depreciation: " . number_format($monthlyDepreciation) . "\n";
            echo "Accumulated Depreciation: " . number_format($accumulatedDepreciation) . "\n";
            echo "Current Book Value: " . number_format($bookValue) . "\n";
            break;
            
        case 'saldo_menurun':
            echo "\n--- DOUBLE DECLINING BALANCE METHOD ---\n";
            $annualRate = 2 / $usefulLife; // 2/5 = 0.4 = 40%
            $monthlyRate = $annualRate / 12;
            
            echo "Annual Rate: " . ($annualRate * 100) . "%\n";
            echo "Monthly Rate: " . ($monthlyRate * 100) . "%\n";
            
            // Calculate accumulated depreciation month by month
            $bookValue = $totalCost;
            $accumulatedDepreciation = 0;
            
            for ($month = 1; $month <= $monthsElapsed; $month++) {
                $monthlyDepreciation = $bookValue * $monthlyRate;
                
                // Don't exceed residual value
                if ($bookValue - $monthlyDepreciation < $residualValue) {
                    $monthlyDepreciation = $bookValue - $residualValue;
                }
                
                $accumulatedDepreciation += $monthlyDepreciation;
                $bookValue -= $monthlyDepreciation;
                
                if ($bookValue <= $residualValue) {
                    break;
                }
            }
            
            // Current month depreciation
            $currentMonthlyDepreciation = $bookValue * $monthlyRate;
            if ($bookValue - $currentMonthlyDepreciation < $residualValue) {
                $currentMonthlyDepreciation = $bookValue - $residualValue;
            }
            
            echo "Current Monthly Depreciation: " . number_format($currentMonthlyDepreciation) . "\n";
            echo "Accumulated Depreciation: " . number_format($accumulatedDepreciation) . "\n";
            echo "Current Book Value: " . number_format($bookValue) . "\n";
            break;
            
        case 'sum_of_years_digits':
            echo "\n--- SUM OF YEARS DIGITS METHOD ---\n";
            $sumOfYears = ($usefulLife * ($usefulLife + 1)) / 2; // 5+4+3+2+1 = 15
            echo "Sum of Years: {$sumOfYears}\n";
            
            $accumulatedDepreciation = 0;
            
            // Calculate depreciation for completed years
            $completedYears = floor($monthsElapsed / 12);
            for ($year = 1; $year <= $completedYears; $year++) {
                $remainingLife = $usefulLife - $year + 1;
                $yearlyDepreciation = ($depreciableAmount * $remainingLife) / $sumOfYears;
                $accumulatedDepreciation += $yearlyDepreciation;
            }
            
            // Calculate depreciation for current year (partial)
            $monthsInCurrentYear = $monthsElapsed % 12;
            if ($monthsInCurrentYear > 0 && $currentYear <= $usefulLife) {
                $remainingLife = $usefulLife - $currentYear + 1;
                $currentYearDepreciation = ($depreciableAmount * $remainingLife) / $sumOfYears;
                $monthlyDepreciationCurrentYear = $currentYearDepreciation / 12;
                $accumulatedDepreciation += $monthlyDepreciationCurrentYear * $monthsInCurrentYear;
            }
            
            // Current month depreciation
            $currentMonthlyDepreciation = 0;
            if ($currentYear <= $usefulLife) {
                $remainingLife = $usefulLife - $currentYear + 1;
                $currentYearDepreciation = ($depreciableAmount * $remainingLife) / $sumOfYears;
                $currentMonthlyDepreciation = $currentYearDepreciation / 12;
            }
            
            $bookValue = $totalCost - $accumulatedDepreciation;
            
            echo "Current Year: {$currentYear}\n";
            echo "Remaining Life: " . ($usefulLife - $currentYear + 1) . " years\n";
            echo "Current Monthly Depreciation: " . number_format($currentMonthlyDepreciation) . "\n";
            echo "Accumulated Depreciation: " . number_format($accumulatedDepreciation) . "\n";
            echo "Current Book Value: " . number_format($bookValue) . "\n";
            break;
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "Manual calculation completed!\n";
echo "\nKEY DIFFERENCES BETWEEN METHODS:\n";
echo "1. STRAIGHT LINE: Constant monthly depreciation\n";
echo "2. DOUBLE DECLINING: Decreasing monthly depreciation based on current book value\n";
echo "3. SUM OF YEARS: Decreasing yearly depreciation, constant within each year\n";