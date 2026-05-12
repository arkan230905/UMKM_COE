<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Historical Conversion for Initial Stock ===\n";

// The issue is that the view uses current conversion rate (3 potong/kg) for all data
// But initial stock should use historical conversion rate (4 potong/kg)

// Solution: Add a field to track historical conversion rates for stock movements
// or modify the view logic to handle different conversion rates based on date/type

echo "PROBLEM IDENTIFIED:\n";
echo "- Initial stock: 50 kg should show as 200 potong (4 potong/kg)\n";
echo "- But view calculates: 50 kg × 3 potong/kg = 150 potong\n";
echo "- Purchase: 40 kg correctly shows as 120 potong (3 potong/kg)\n";

echo "\nSOLUTION OPTIONS:\n";
echo "1. Add historical_conversion_rate field to stock_movements table\n";
echo "2. Modify view logic to use different rates based on ref_type\n";
echo "3. Store conversion rate at time of transaction\n";

echo "\nImplementing Solution 2 (modify view logic)...\n";

// Check current bahan_baku conversion data
$ayamPotong = \App\Models\BahanBaku::find(1);
echo "Current conversion in master data: {$ayamPotong->sub_satuan_2_nilai} potong/kg\n";

// We need to modify the LaporanController to pass historical conversion info
// Let's create a simple fix by adding conversion metadata to stock movements

echo "\nAdding conversion metadata to initial stock movement...\n";

$initialMovement = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', 1)
    ->where('ref_type', 'initial_stock')
    ->first();

if ($initialMovement) {
    echo "Found initial stock movement ID: {$initialMovement->id}\n";
    
    // Add a note in keterangan to indicate historical conversion
    $currentKeterangan = $initialMovement->keterangan ?? 'Stok Awal';
    if (!strpos($currentKeterangan, 'HISTORICAL_CONVERSION_4')) {
        $newKeterangan = $currentKeterangan . ' [HISTORICAL_CONVERSION_4]';
        $initialMovement->keterangan = $newKeterangan;
        $initialMovement->save();
        echo "✅ Added historical conversion marker to initial stock\n";
    } else {
        echo "Historical conversion marker already exists\n";
    }
}

echo "\nNow we need to modify the view to detect this marker and use 4 potong/kg for initial stock\n";

// Create a simple helper function for the view
echo "\nCreating helper function for conversion calculation...\n";

$helperCode = '
// Helper function to get correct conversion rate based on transaction type and date
function getCorrectConversionRate($transaction, $defaultRate) {
    // Check if this is initial stock with historical conversion
    if (isset($transaction["ref_type"]) && $transaction["ref_type"] === "initial_stock") {
        // Check for historical conversion marker
        if (isset($transaction["keterangan"]) && strpos($transaction["keterangan"], "HISTORICAL_CONVERSION_4") !== false) {
            return 4.0; // Use historical rate for initial stock
        }
    }
    
    return $defaultRate; // Use current rate for other transactions
}
';

echo "Helper function created (to be added to view)\n";

echo "\n=== SUMMARY ===\n";
echo "✅ Added historical conversion marker to initial stock movement\n";
echo "📝 Next step: Modify view to use different conversion rates:\n";
echo "   - Initial stock: 4 potong/kg (historical)\n";
echo "   - Other transactions: 3 potong/kg (current)\n";
echo "\nThis will show:\n";
echo "- Saldo awal: 50 kg = 200 potong ✅\n";
echo "- Pembelian: 40 kg = 120 potong ✅\n";
echo "- Total tersedia: 90 kg = 320 potong ✅\n";