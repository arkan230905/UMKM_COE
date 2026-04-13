<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Price Calculation Issue ===\n";

// Check stock movements with cost data
$stockMovements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->orderBy('tanggal')
    ->get();

echo "1. Stock movements with cost data:\n";
$runningQty = 0;
$runningValue = 0;

foreach ($stockMovements as $movement) {
    $change = $movement->direction === 'in' ? $movement->qty : -$movement->qty;
    $valueChange = $movement->direction === 'in' ? $movement->total_cost : -$movement->total_cost;
    
    $runningQty += $change;
    $runningValue += $valueChange;
    
    $unitCost = $movement->qty > 0 ? $movement->total_cost / $movement->qty : 0;
    $avgCost = $runningQty > 0 ? $runningValue / $runningQty : 0;
    
    echo "- {$movement->tanggal} | {$movement->ref_type} | {$movement->direction} {$movement->qty} PCS\n";
    echo "  Unit Cost: Rp " . number_format($unitCost, 2) . "\n";
    echo "  Total Cost: Rp " . number_format($movement->total_cost, 2) . "\n";
    echo "  Running Qty: {$runningQty} PCS\n";
    echo "  Running Value: Rp " . number_format($runningValue, 2) . "\n";
    echo "  Average Cost: Rp " . number_format($avgCost, 2) . "\n\n";
}

echo "2. Expected price calculation logic:\n";
echo "For inventory valuation, we should use:\n";
echo "- FIFO (First In, First Out) method, OR\n";
echo "- Weighted Average method\n";

echo "\n3. Current issue analysis:\n";
echo "The system seems to be calculating unit price as:\n";
echo "Unit Price = Total Remaining Value / Remaining Quantity\n";
echo "This causes price to fluctuate wildly as quantity changes.\n";

echo "\n4. Correct approach should be:\n";
echo "- Production cost: Rp 21.056 per PCS (from BOM calculation)\n";
echo "- All units should maintain this cost until new production\n";
echo "- Sales should use FIFO or average cost\n";

echo "\n5. Checking production cost calculation:\n";
$produksi = \App\Models\ProsesProduksi::find(2);
if ($produksi) {
    echo "Production #2 details:\n";
    
    // Check if there's BOM cost calculation
    $bomCost = \App\Http\Controllers\LaporanController::getBiayaBahanPerUnit(2);
    echo "BOM cost per unit: Rp " . number_format($bomCost, 2) . "\n";
    
    // Check production movement cost
    $productionMovement = \App\Models\StockMovement::where('ref_type', 'production')
        ->where('ref_id', 2)
        ->where('item_id', 2)
        ->first();
    
    if ($productionMovement) {
        $actualUnitCost = $productionMovement->total_cost / $productionMovement->qty;
        echo "Actual production unit cost: Rp " . number_format($actualUnitCost, 2) . "\n";
        
        if (abs($actualUnitCost - $bomCost) > 1) {
            echo "❌ Production cost mismatch!\n";
        } else {
            echo "✅ Production cost matches BOM\n";
        }
    }
}

echo "\n6. SOLUTION:\n";
echo "Need to fix inventory valuation method:\n";
echo "- Use consistent unit cost (Rp 21.056) for all units from same production batch\n";
echo "- Don't recalculate unit price based on remaining quantity\n";
echo "- Implement proper FIFO or weighted average method\n";