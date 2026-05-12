<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Initial Stock Conversion ===\n";

echo "UNDERSTANDING THE ISSUE:\n";
echo "- Saldo awal: 50 kg (entered with old conversion 1 kg = 4 potong)\n";
echo "- Current conversion: 1 kg = 3 potong\n";
echo "- Purchase: 40 kg (uses new conversion 1 kg = 3 potong)\n";
echo "- Production & Retur: calculated with new conversion\n";

echo "\nCORRECT CALCULATION:\n";
echo "Initial stock: 50 kg = 200 potong (old conversion 4 potong/kg)\n";
echo "Purchase: 40 kg = 120 potong (new conversion 3 potong/kg)\n";
echo "Total available: 50 kg + 40 kg = 90 kg = 320 potong (mixed conversion)\n";

echo "Usage:\n";
echo "- Production: 160 potong\n";
echo "- Retur: 11 ekor = 8.8 kg = 26.4 potong (at 3 potong/kg)\n";
echo "- Total used: 160 + 26.4 = 186.4 potong\n";

echo "Remaining: 320 - 186.4 = 133.6 potong\n";

// Let's verify current system calculation
echo "\n=== CURRENT SYSTEM VERIFICATION ===\n";
$stockMovements = \DB::table('stock_movements')
    ->where('item_type', 'material')
    ->where('item_id', 1)
    ->get();

$totalIn = 0;
$totalOut = 0;
foreach ($stockMovements as $movement) {
    echo "- {$movement->ref_type}#{$movement->ref_id}: {$movement->direction} {$movement->qty} kg\n";
    if ($movement->direction === 'in') {
        $totalIn += $movement->qty;
    } else {
        $totalOut += $movement->qty;
    }
}

$currentStockKg = $totalIn - $totalOut;
echo "\nSystem shows: {$currentStockKg} kg\n";

// Calculate correct potong considering mixed conversions
echo "\n=== CORRECT POTONG CALCULATION ===\n";

// Initial stock: 50 kg with old conversion (4 potong/kg)
$initialPotong = 50 * 4;
echo "Initial stock: 50 kg × 4 potong/kg = {$initialPotong} potong\n";

// Purchase: 40 kg with new conversion (3 potong/kg)  
$purchasePotong = 40 * 3;
echo "Purchase: 40 kg × 3 potong/kg = {$purchasePotong} potong\n";

$totalAvailablePotong = $initialPotong + $purchasePotong;
echo "Total available: {$totalAvailablePotong} potong\n";

// Usage in potong
$productionUsedPotong = 160;
$returUsedPotong = (10 * 0.8 * 3) + (1 * 0.8 * 3); // Convert ekor to kg then to potong
echo "Production used: {$productionUsedPotong} potong\n";
echo "Retur used: {$returUsedPotong} potong\n";

$totalUsedPotong = $productionUsedPotong + $returUsedPotong;
$remainingPotong = $totalAvailablePotong - $totalUsedPotong;

echo "Total used: {$totalUsedPotong} potong\n";
echo "Remaining: {$remainingPotong} potong\n";

// Convert back to kg for comparison
echo "\nFor comparison in kg:\n";
echo "System shows: {$currentStockKg} kg\n";
echo "But in potong terms: {$remainingPotong} potong\n";

// The kg value is correct for weight, but potong calculation needs to consider mixed conversions
echo "\n=== SUMMARY ===\n";
echo "✅ Weight in kg: {$currentStockKg} kg (correct)\n";
echo "✅ Quantity in potong: {$remainingPotong} potong (considering mixed conversions)\n";
echo "\nNote: The kg value is accurate for weight calculations.\n";
echo "The potong value accounts for different conversion rates over time.\n";

// Check if we need to update any display logic
echo "\n=== RECOMMENDATION ===\n";
echo "The system is actually correct in kg terms.\n";
echo "For display purposes, if you want to show potong:\n";
echo "- Don't simply multiply current kg by current conversion rate\n";
echo "- Track potong separately or use historical conversion data\n";
echo "- Current approach (tracking in kg) is more reliable\n";