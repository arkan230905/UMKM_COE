<?php

// Check current stock for retur items
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseReturn;
use App\Models\KartuStok;
use App\Services\StockService;

echo "Checking Stock for Retur Items\n";
echo "==============================\n\n";

$retur = PurchaseReturn::with(['items.bahanBaku', 'items.bahanPendukung'])->find(1);

if (!$retur) {
    echo "Retur not found\n";
    exit;
}

echo "Retur ID: {$retur->id}\n";
echo "Return Number: {$retur->return_number}\n";
echo "Status: {$retur->status}\n\n";

$stockService = new StockService();

echo "Items in this retur:\n";
foreach ($retur->items as $item) {
    echo "Item ID: {$item->id}\n";
    echo "Quantity to return: {$item->quantity}\n";
    
    if ($item->bahan_baku_id) {
        $itemType = 'bahan_baku';
        $itemId = $item->bahan_baku_id;
        $itemName = $item->bahanBaku->nama_bahan ?? 'Unknown';
    } else {
        $itemType = 'bahan_pendukung';
        $itemId = $item->bahan_pendukung_id;
        $itemName = $item->bahanPendukung->nama_bahan ?? 'Unknown';
    }
    
    echo "Item: {$itemName} (ID: {$itemId}, Type: {$itemType})\n";
    
    $currentStock = $stockService->getCurrentStock($itemId, $itemType);
    echo "Current Stock: {$currentStock}\n";
    
    if ($currentStock < $item->quantity) {
        echo "❌ INSUFFICIENT STOCK! Need: {$item->quantity}, Have: {$currentStock}\n";
    } else {
        echo "✅ Stock sufficient\n";
    }
    
    // Show stock movements
    echo "\nStock movements for this item:\n";
    $movements = KartuStok::where('item_id', $itemId)
        ->where('item_type', $itemType)
        ->orderBy('tanggal', 'desc')
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get();
        
    foreach ($movements as $movement) {
        echo "  {$movement->tanggal->format('Y-m-d')}: ";
        if ($movement->qty_masuk) {
            echo "+{$movement->qty_masuk} (IN) - {$movement->keterangan}\n";
        } else {
            echo "-{$movement->qty_keluar} (OUT) - {$movement->keterangan}\n";
        }
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}