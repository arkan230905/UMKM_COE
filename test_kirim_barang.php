<?php

// Test Kirim Barang action
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ReturController;
use App\Models\PurchaseReturn;
use App\Models\KartuStok;

echo "Testing Kirim Barang Action\n";
echo "===========================\n\n";

$retur = PurchaseReturn::find(1);
echo "Current status: {$retur->status}\n";

// Count stock movements before
$stockCountBefore = KartuStok::where('item_id', 1)
    ->where('item_type', 'bahan_baku')
    ->count();
echo "Stock movements before: {$stockCountBefore}\n\n";

try {
    $controller = new ReturController();
    $response = $controller->kirim(1);
    
    echo "✅ Kirim method executed successfully\n";
    
    // Check new status
    $retur->refresh();
    echo "New status: {$retur->status}\n";
    
    // Count stock movements after
    $stockCountAfter = KartuStok::where('item_id', 1)
        ->where('item_type', 'bahan_baku')
        ->count();
    echo "Stock movements after: {$stockCountAfter}\n";
    
    if ($stockCountAfter > $stockCountBefore) {
        echo "✅ New stock movement created!\n";
        
        // Show the latest stock movement
        $latestMovement = KartuStok::where('item_id', 1)
            ->where('item_type', 'bahan_baku')
            ->orderBy('id', 'desc')
            ->first();
            
        if ($latestMovement) {
            echo "Latest movement: -{$latestMovement->qty_keluar} on {$latestMovement->tanggal->format('Y-m-d')}\n";
            echo "Keterangan: {$latestMovement->keterangan}\n";
        }
    } else {
        echo "⚠️  No new stock movement created\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}