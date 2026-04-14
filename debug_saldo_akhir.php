<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Saldo Akhir Calculation ===\n";

// Get all stock movements for Ayam Potong
$stockMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

echo "All stock movements:\n";
$runningKg = 0;
foreach ($stockMovements as $movement) {
    $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
    $runningKg += $change;
    echo "- {$movement->tanggal} | {$movement->ref_type}#{$movement->ref_id} | {$movement->direction} {$movement->qty} kg | Running: {$runningKg} kg\n";
}

echo "\nFinal stock in kg: {$runningKg} kg\n";

echo "\nCONVERSION ANALYSIS:\n";
echo "The issue is in how we calculate final stock in potong:\n";

echo "\nCORRECT CALCULATION (mixed conversion):\n";
echo "1. Initial stock: 50 kg × 4 potong/kg = 200 potong\n";
echo "2. Purchase: 40 kg × 3 potong/kg = 120 potong\n";
echo "3. Total available: 320 potong\n";
echo "4. Production used: 160 potong\n";
echo "5. Retur: 8.8 kg × 3 potong/kg = 26.4 potong\n";
echo "6. Total used: 186.4 potong\n";
echo "7. Remaining: 320 - 186.4 = 133.6 potong\n";

echo "\nWRONG CALCULATION (current system):\n";
echo "Final kg × current conversion = {$runningKg} kg × 3 potong/kg = " . ($runningKg * 3) . " potong\n";

echo "\nPROBLEM:\n";
echo "The system calculates final stock by multiplying final kg with current conversion rate.\n";
echo "But this ignores the fact that initial stock used different conversion rate.\n";

echo "\nSOLUTION:\n";
echo "We need to track potong separately or calculate final potong by:\n";
echo "Final potong = Initial potong + Purchase potong - Production potong - Retur potong\n";
echo "Final potong = 200 + 120 - 160 - 26.4 = 133.6 potong\n";

// Let's check what the view is actually calculating
echo "\n=== VIEW CALCULATION SIMULATION ===\n";

// Simulate the view calculation for saldo akhir
$finalKg = $runningKg;
$currentConversion = 3.0;
$viewCalculation = $finalKg * $currentConversion;

echo "View calculates: {$finalKg} kg × {$currentConversion} potong/kg = {$viewCalculation} potong\n";
echo "But should be: 133.6 potong\n";
echo "Difference: " . ($viewCalculation - 133.6) . " potong\n";

echo "\nThe view needs to be fixed to calculate final stock correctly with mixed conversions.\n";