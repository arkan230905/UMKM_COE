<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Testing penjualan after stock layer fix...\n\n";

// Check if stock layers exist for product ID 2
$stockLayers = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->get();

echo "Stock layers for product ID 2: " . $stockLayers->count() . "\n";
foreach ($stockLayers as $layer) {
    echo "Layer ID: {$layer->id}, Remaining: {$layer->remaining_qty}, Unit Cost: {$layer->unit_cost}\n";
}

// Check available stock
$available = \App\Models\StockLayer::where('item_type', 'product')
    ->where('item_id', 2)
    ->sum('remaining_qty');

echo "Available stock: {$available}\n";

// Test if we can consume 150 units
$requestedQty = 150;
echo "Requested quantity: {$requestedQty}\n";
echo "Can consume: " . ($requestedQty <= $available ? 'YES' : 'NO') . "\n";

if ($requestedQty <= $available) {
    echo "\n=== TESTING STOCK SERVICE CONSUME ===\n";
    
    try {
        $stockService = new \App\Services\StockService();
        
        // Simulate the consume operation
        $cogs = $stockService->consume('product', 2, $requestedQty, 'pcs', 'test_sale', 999, '2026-04-08');
        
        echo "Consume successful! COGS: {$cogs}\n";
        
        // Check remaining stock
        $newAvailable = \App\Models\StockLayer::where('item_type', 'product')
            ->where('item_id', 2)
            ->sum('remaining_qty');
        
        echo "Remaining stock after consume: {$newAvailable}\n";
        
        // Clean up test movement
        \App\Models\StockMovement::where('ref_type', 'test_sale')
            ->where('ref_id', 999)
            ->delete();
        
        echo "Test movement cleaned up\n";
        
    } catch (\Exception $e) {
        echo "Error during consume: " . $e->getMessage() . "\n";
    }
} else {
    echo "Cannot consume - insufficient stock\n";
}

echo "\n=== CHECKING RECENT PENJUALAN ATTEMPTS ===\n";

// Check for any recent penjualan that might have failed
$recentPenjualan = \App\Models\Penjualan::whereDate('tanggal', '2026-04-08')
    ->orderBy('id', 'desc')
    ->get();

foreach ($recentPenjualan as $penjualan) {
    echo "Penjualan ID: {$penjualan->id} - {$penjualan->nomor_penjualan}\n";
    
    $details = \App\Models\PenjualanDetail::where('penjualan_id', $penjualan->id)
        ->get();
    
    foreach ($details as $detail) {
        echo "  Detail: Produk {$detail->produk_id}, Qty: {$detail->jumlah}\n";
    }
    
    // Check if stock movements exist
    $movementCount = \App\Models\StockMovement::where('ref_type', 'sale')
        ->where('ref_id', $penjualan->id)
        ->count();
    
    echo "  Stock movements: {$movementCount}\n";
}

echo "\nDone.\n";
