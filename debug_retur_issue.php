<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debugging Retur Issue ===\n";

// Check stock movements for retur
$movements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1) // Ayam Potong
    ->where('ref_type', 'retur')
    ->orderBy('tanggal', 'asc')
    ->get();

echo "Retur movements found: " . $movements->count() . "\n\n";

foreach($movements as $movement) {
    echo "Movement Details:\n";
    echo "- ID: {$movement->id}\n";
    echo "- Date: {$movement->tanggal}\n";
    echo "- Ref Type: '{$movement->ref_type}'\n";
    echo "- Direction: '{$movement->direction}'\n";
    echo "- Qty: {$movement->qty}\n";
    echo "- Unit Cost: " . ($movement->unit_cost ?? 0) . "\n";
    echo "- Total Cost: " . ($movement->total_cost ?? 0) . "\n";
    echo "---\n";
}

// Test the logic conditions
echo "\n=== Testing Logic Conditions ===\n";
$testMovement = $movements->first();
if($testMovement) {
    $tipe = 'material';
    
    echo "Test movement ref_type: '{$testMovement->ref_type}'\n";
    echo "Test movement direction: '{$testMovement->direction}'\n";
    echo "Tipe: '{$tipe}'\n";
    
    // Test conditions
    echo "\nCondition tests:\n";
    echo "ref_type === 'retur': " . ($testMovement->ref_type === 'retur' ? 'TRUE' : 'FALSE') . "\n";
    echo "direction === 'in': " . ($testMovement->direction === 'in' ? 'TRUE' : 'FALSE') . "\n";
    echo "direction === 'out': " . ($testMovement->direction === 'out' ? 'TRUE' : 'FALSE') . "\n";
    echo "tipe === 'material': " . ($tipe === 'material' ? 'TRUE' : 'FALSE') . "\n";
    
    // Test the combined condition
    $condition1 = ($testMovement->ref_type === 'retur' && ($tipe === 'material' || $tipe === 'bahan_pendukung'));
    echo "Combined condition (retur + material): " . ($condition1 ? 'TRUE' : 'FALSE') . "\n";
}

echo "\n=== Debug Complete ===\n";