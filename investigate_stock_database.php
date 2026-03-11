<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE STOCK DATABASE INVESTIGATION ===\n\n";

// 1. Check all tables that might contain purchase-related data
echo "1. CHECKING ALL PURCHASE-RELATED TABLES:\n";
echo "   - pembelians: " . \App\Models\Pembelian::count() . " records\n";
echo "   - pembelian_details: " . \App\Models\PembelianDetail::count() . " records\n";

// 2. Check stock movements in detail
echo "\n2. STOCK MOVEMENTS ANALYSIS:\n";
$stockMovements = \App\Models\StockMovement::all();
echo "   Total stock movements: " . $stockMovements->count() . "\n";

// Group by ref_type and direction
$byRefType = $stockMovements->groupBy('ref_type');
foreach ($byRefType as $refType => $movements) {
    echo "   - {$refType}: " . $movements->count() . " movements\n";
    
    $byDirection = $movements->groupBy('direction');
    foreach ($byDirection as $direction => $dirMovements) {
        echo "     * {$direction}: " . $dirMovements->count() . "\n";
    }
}

// 3. Check for any movements that might have wrong ref_id pointing to non-existent purchases
echo "\n3. CHECKING FOR INCONSISTENT REFERENCE IDs:\n";
$suspiciousMovements = \App\Models\StockMovement::where('ref_type', 'purchase')->get();
echo "   Movements with ref_type='purchase': " . $suspiciousMovements->count() . "\n";

if ($suspiciousMovements->count() > 0) {
    foreach ($suspiciousMovements as $sm) {
        echo "   - ID: {$sm->id}, ref_id: {$sm->ref_id}, item: {$sm->item_type}#{$sm->item_id}\n";
    }
}

// 4. Check initial_stock movements that might be problematic
echo "\n4. INITIAL STOCK MOVEMENTS DETAILS:\n";
$initialStockMovements = \App\Models\StockMovement::where('ref_type', 'initial_stock')->get();
echo "   Total initial_stock movements: " . $initialStockMovements->count() . "\n";

// Check if any have suspicious ref_id values
$suspiciousInitial = $initialStockMovements->where('ref_id', '>', 0);
echo "   Initial stock with ref_id > 0: " . $suspiciousInitial->count() . "\n";

if ($suspiciousInitial->count() > 0) {
    echo "   Suspicious initial stock movements:\n";
    foreach ($suspiciousInitial->take(5) as $sm) {
        echo "   - ID: {$sm->id}, ref_id: {$sm->ref_id}, qty: {$sm->qty}, date: {$sm->tanggal}\n";
    }
}

// 5. Check stock layers
echo "\n5. STOCK LAYERS ANALYSIS:\n";
$stockLayers = \App\Models\StockLayer::all();
echo "   Total stock layers: " . $stockLayers->count() . "\n";

$layersByRefType = $stockLayers->groupBy('ref_type');
foreach ($layersByRefType as $refType => $layers) {
    echo "   - {$refType}: " . $layers->count() . " layers\n";
}

// 6. Check for any other tables that might contain purchase references
echo "\n6. CHECKING OTHER POTENTIAL SOURCES:\n";

// Check if there are any journal entries with purchase ref_type
$purchaseJournals = \App\Models\JournalEntry::where('ref_type', 'purchase')->count();
echo "   Journal entries with ref_type='purchase': " . $purchaseJournals . "\n";

// Check if there are any other models that might be creating confusion
try {
    $suppliers = \App\Models\Supplier::count();
    echo "   Suppliers: " . $suppliers . "\n";
} catch (\Exception $e) {
    echo "   Suppliers table: Not accessible\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";
echo "Looking for data that needs cleanup...\n";

echo "\nDone!\n";