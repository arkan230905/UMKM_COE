<?php

// Fix missing stock entries for existing purchases
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pembelian;
use App\Models\KartuStok;
use App\Services\StockService;

echo "Fixing Missing Purchase Stock Entries\n";
echo "====================================\n\n";

$stockService = new StockService();

// Get the specific purchase
$pembelian = Pembelian::with('details.bahanBaku', 'details.bahanPendukung')->find(1);

if (!$pembelian) {
    echo "Pembelian not found\n";
    exit;
}

echo "Processing Pembelian ID: {$pembelian->id}\n";
echo "Nomor: {$pembelian->nomor_pembelian}\n";
echo "Tanggal: {$pembelian->tanggal->format('Y-m-d')}\n\n";

foreach ($pembelian->details as $detail) {
    if ($detail->bahan_baku_id) {
        $itemType = KartuStok::ITEM_TYPE_BAHAN_BAKU;
        $itemId = $detail->bahan_baku_id;
        $itemName = $detail->bahanBaku->nama_bahan ?? 'Unknown';
    } elseif ($detail->bahan_pendukung_id) {
        $itemType = KartuStok::ITEM_TYPE_BAHAN_PENDUKUNG;
        $itemId = $detail->bahan_pendukung_id;
        $itemName = $detail->bahanPendukung->nama_bahan ?? 'Unknown';
    } else {
        echo "⚠️  Skipping detail {$detail->id} - no item reference\n";
        continue;
    }
    
    echo "Processing: {$itemName} (ID: {$itemId})\n";
    echo "Quantity: {$detail->jumlah}\n";
    
    // Check if stock entry already exists
    $existingEntry = KartuStok::where('item_id', $itemId)
        ->where('item_type', $itemType)
        ->where('ref_type', KartuStok::REF_TYPE_PEMBELIAN)
        ->where('ref_id', $pembelian->id)
        ->first();
        
    if ($existingEntry) {
        echo "✅ Stock entry already exists\n";
    } else {
        try {
            // Create stock entry
            $stockService->addStock(
                $itemId,
                $itemType,
                $detail->jumlah,
                "Pembelian #{$pembelian->nomor_pembelian}",
                KartuStok::REF_TYPE_PEMBELIAN,
                $pembelian->id,
                $pembelian->tanggal->format('Y-m-d')
            );
            
            echo "✅ Created stock entry: +{$detail->jumlah}\n";
            
            // Check new stock balance
            $newBalance = $stockService->getCurrentStock($itemId, $itemType);
            echo "New stock balance: {$newBalance}\n";
            
        } catch (Exception $e) {
            echo "❌ Error creating stock entry: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat('-', 40) . "\n\n";
}

echo "Stock fix completed!\n";