<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Sales Structure ===\n";

// Check penjualan_details table structure
echo "1. Penjualan details table structure:\n";
try {
    $columns = \DB::select('DESCRIBE penjualan_details');
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check actual sales data
echo "\n2. Actual sales details data:\n";
$details = \DB::table('penjualan_details')->whereIn('penjualan_id', [4, 5])->get();
foreach ($details as $detail) {
    echo "Detail ID {$detail->id}:\n";
    foreach ((array)$detail as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
    echo "\n";
}

echo "3. PROBLEM ANALYSIS:\n";
echo "The issue is that sale movements exist in stock_movements table,\n";
echo "but they're not showing up in the Penjualan column of the report.\n";

echo "\n4. Controller logic check:\n";
echo "In LaporanController, for OUT movements:\n";
echo "- if (\$m->ref_type === 'sale' && \$tipe === 'product') {\n";
echo "    // Should go to penjualan column\n";
echo "    \$dailySaleQty = (float)\$m->qty;\n";
echo "}\n";

echo "\nThis logic should work. Let me check if there's an issue with:\n";
echo "a) The condition not being met\n";
echo "b) The data not being passed to view correctly\n";
echo "c) The view not displaying the data\n";

// Check stock movements again with detailed analysis
echo "\n5. Detailed stock movement analysis:\n";
$movements = \App\Models\StockMovement::where('item_type', 'product')
    ->where('item_id', 2)
    ->get();

foreach ($movements as $movement) {
    echo "Movement ID {$movement->id}:\n";
    echo "- ref_type: '{$movement->ref_type}'\n";
    echo "- direction: '{$movement->direction}'\n";
    echo "- qty: {$movement->qty}\n";
    echo "- total_cost: {$movement->total_cost}\n";
    
    // Simulate controller logic
    if ($movement->direction === 'out') {
        if ($movement->ref_type === 'sale') {
            echo "→ Should appear in Penjualan column: {$movement->qty} PCS\n";
        } elseif (strpos($movement->ref_type, 'retur') !== false) {
            echo "→ Should appear in Penjualan column: {$movement->qty} PCS (retur)\n";
        }
    }
    echo "\n";
}

echo "NEXT STEPS:\n";
echo "1. Check if controller logic is working correctly\n";
echo "2. Check if view is displaying penjualan data\n";
echo "3. Verify that sales have proper cost data\n";