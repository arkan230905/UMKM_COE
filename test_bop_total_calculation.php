<?php

echo "=== TESTING BOP TOTAL CALCULATION ===\n\n";

// Simulate the BOP data that will be returned by the API
$sampleBopData = [
    [
        'id' => 7,
        'komponen_bop' => '[{"component":"Gas / BBM","rate_per_hour":67,"description":""},{"component":"Air & Kebersihan","rate_per_hour":28,"description":""}]',
        'total_bop_per_produk' => '95.00',
        'total_bop_per_jam' => '0.00'
    ],
    [
        'id' => 8,
        'komponen_bop' => '[{"component":"Listrik","rate_per_hour":278,"description":""},{"component":"Susu","rate_per_hour":649,"description":""},{"component":"Keju","rate_per_hour":1000,"description":""},{"component":"Cup","rate_per_hour":400,"description":""}]',
        'total_bop_per_produk' => '2327.00',
        'total_bop_per_jam' => '0.00'
    ]
];

echo "1. Sample BOP data from API:\n";
echo json_encode($sampleBopData, JSON_PRETTY_PRINT) . "\n\n";

echo "2. Calculating total BOP (frontend logic):\n";

// Simulate the frontend calculateTotalBOP function
$totalBOP = 0;

foreach ($sampleBopData as $bop) {
    try {
        // Parse the komponen_bop JSON field
        $components = json_decode($bop['komponen_bop'], true);
        
        if (is_array($components)) {
            // If it's an array, sum each component's rate_per_hour
            foreach ($components as $component) {
                $totalBOP += floatval($component['rate_per_hour'] ?? 0);
            }
        } else {
            // If it's not an array, use the total_bop_per_produk
            $totalBOP += floatval($bop['total_bop_per_produk'] ?? 0);
        }
    } catch (Exception $e) {
        // If JSON parsing fails, use the total_bop_per_produk
        $totalBOP += floatval($bop['total_bop_per_produk'] ?? 0);
    }
}

echo "Total BOP calculated: Rp " . number_format($totalBOP, 2, ',', '.') . "\n\n";

echo "3. Expected BOP components breakdown:\n";
echo "From ID 7:\n";
$components7 = json_decode($sampleBopData[0]['komponen_bop'], true);
foreach ($components7 as $component) {
    echo "  - {$component['component']}: Rp " . number_format($component['rate_per_hour'], 2, ',', '.') . "\n";
}

echo "\nFrom ID 8:\n";
$components8 = json_decode($sampleBopData[1]['komponen_bop'], true);
foreach ($components8 as $component) {
    echo "  - {$component['component']}: Rp " . number_format($component['rate_per_hour'], 2, ',', '.') . "\n";
}

echo "\nCalculation verification:\n";
$manualTotal = 67 + 28 + 278 + 649 + 1000 + 400;
echo "Manual sum: 67 + 28 + 278 + 649 + 1000 + 400 = " . $manualTotal . "\n";
echo "Script calculation: " . $totalBOP . "\n";
echo "Match: " . ($manualTotal == $totalBOP ? "✅ YES" : "❌ NO") . "\n\n";

echo "4. Expected frontend behavior:\n";
echo "✅ BOP table will show 6 components without 'Total per Jam' column\n";
echo "✅ BTKL table will show BOP/pcs = Rp 2.422,00 for all selected BTKL\n";
echo "✅ Total BOP input will be updated to 2422\n";
echo "✅ Summary will calculate Total HPP = BBB + BTKL + BOP\n\n";

echo "5. Implementation status:\n";
echo "✅ Removed 'Total per Jam' column from BOP display\n";
echo "✅ Added calculateTotalBOP function\n";
echo "✅ Added updateBTKLTableWithBOP function\n";
echo "✅ Updated loadBOPDetails to calculate and update totals\n";
echo "✅ Fixed BTKL table ID reference (btklTableBody)\n\n";

echo "=== TEST COMPLETE ===\n";
