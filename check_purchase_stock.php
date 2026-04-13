<?php

// Check if the original purchase created stock entries
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseReturn;
use App\Models\Pembelian;
use App\Models\KartuStok;

echo "Checking Original Purchase Stock\n";
echo "===============================\n\n";

$retur = PurchaseReturn::with(['pembelian.details.bahanBaku', 'pembelian.details.bahanPendukung'])->find(1);

if (!$retur || !$retur->pembelian) {
    echo "Retur or pembelian not found\n";
    exit;
}

$pembelian = $retur->pembelian;

echo "Pembelian ID: {$pembelian->id}\n";
echo "Nomor Pembelian: {$pembelian->nomor_pembelian}\n";
echo "Tanggal: {$pembelian->tanggal->format('Y-m-d')}\n\n";

echo "Pembelian Details:\n";
foreach ($pembelian->details as $detail) {
    echo "Detail ID: {$detail->id}\n";
    echo "Quantity: {$detail->jumlah}\n";
    
    if ($detail->bahan_baku_id) {
        $itemType = 'bahan_baku';
        $itemId = $detail->bahan_baku_id;
        $itemName = $detail->bahanBaku->nama_bahan ?? 'Unknown';
    } else {
        $itemType = 'bahan_pendukung';
        $itemId = $detail->bahan_pendukung_id;
        $itemName = $detail->bahanPendukung->nama_bahan ?? 'Unknown';
    }
    
    echo "Item: {$itemName} (ID: {$itemId}, Type: {$itemType})\n";
    
    // Check if stock entry exists for this purchase
    $stockEntry = KartuStok::where('item_id', $itemId)
        ->where('item_type', $itemType)
        ->where('ref_type', 'pembelian')
        ->where('ref_id', $pembelian->id)
        ->first();
        
    if ($stockEntry) {
        echo "✅ Stock entry exists: +{$stockEntry->qty_masuk} on {$stockEntry->tanggal->format('Y-m-d')}\n";
    } else {
        echo "❌ NO stock entry found for this purchase!\n";
    }
    
    echo "\n" . str_repeat('-', 30) . "\n\n";
}

// Check all stock entries
echo "All stock entries in kartu_stok:\n";
$allStock = KartuStok::orderBy('tanggal', 'desc')->orderBy('id', 'desc')->limit(10)->get();

if ($allStock->isEmpty()) {
    echo "❌ NO stock entries found in kartu_stok table!\n";
} else {
    foreach ($allStock as $stock) {
        echo "ID: {$stock->id} | {$stock->tanggal->format('Y-m-d')} | Item: {$stock->item_id} ({$stock->item_type}) | ";
        if ($stock->qty_masuk) {
            echo "+{$stock->qty_masuk}";
        } else {
            echo "-{$stock->qty_keluar}";
        }
        echo " | {$stock->keterangan}\n";
    }
}