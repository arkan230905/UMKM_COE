<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

echo "=== Checking All Bahan Baku & Bahan Pendukung Conversions ===\n\n";

// Check all bahan baku
echo "BAHAN BAKU CONVERSIONS:\n";
$bahanBakus = BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->get();

foreach ($bahanBakus as $bb) {
    echo "\n--- {$bb->nama_bahan} (ID: {$bb->id}) ---\n";
    $satuanName = $bb->satuan ? $bb->satuan->nama : 'unit';
    echo "Current Stock: {$bb->stok} {$satuanName}\n";
    
    echo "Units:\n";
    $satuanId = $bb->satuan_id ? $bb->satuan_id : 'N/A';
    $mainUnitName = $bb->satuan ? $bb->satuan->nama : 'N/A';
    echo "  Main Unit: {$mainUnitName} (ID: {$satuanId})\n";
    
    if ($bb->sub_satuan_1_id && $bb->subSatuan1) {
        echo "  Sub Unit 1: {$bb->subSatuan1->nama} (ID: {$bb->sub_satuan_1_id}) - Conversion: {$bb->sub_satuan_1_konversi}\n";
    } else {
        echo "  Sub Unit 1: Not set\n";
    }
    
    if ($bb->sub_satuan_2_id && $bb->subSatuan2) {
        echo "  Sub Unit 2: {$bb->subSatuan2->nama} (ID: {$bb->sub_satuan_2_id}) - Conversion: {$bb->sub_satuan_2_konversi}\n";
    } else {
        echo "  Sub Unit 2: Not set\n";
    }
    
    if ($bb->sub_satuan_3_id && $bb->subSatuan3) {
        echo "  Sub Unit 3: {$bb->subSatuan3->nama} (ID: {$bb->sub_satuan_3_id}) - Conversion: {$bb->sub_satuan_3_konversi}\n";
    } else {
        echo "  Sub Unit 3: Not set\n";
    }
    
    // Check if has production movements
    $hasProductionMovement = StockMovement::where('item_type', 'material')
        ->where('item_id', $bb->id)
        ->where('ref_type', 'production')
        ->exists();
    
    echo "  Has Production Movement: " . ($hasProductionMovement ? 'YES' : 'NO') . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "BAHAN PENDUKUNG CONVERSIONS:\n";

$bahanPendukungs = BahanPendukung::with(['satuanRelation', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->get();

foreach ($bahanPendukungs as $bp) {
    echo "\n--- {$bp->nama_bahan} (ID: {$bp->id}) ---\n";
    $satuanName = $bp->satuanRelation ? $bp->satuanRelation->nama : 'unit';
    echo "Current Stock: {$bp->stok} {$satuanName}\n";
    
    echo "Units:\n";
    $satuanId = $bp->satuan_id ? $bp->satuan_id : 'N/A';
    $mainUnitName = $bp->satuanRelation ? $bp->satuanRelation->nama : 'N/A';
    echo "  Main Unit: {$mainUnitName} (ID: {$satuanId})\n";
    
    if ($bp->sub_satuan_1_id && $bp->subSatuan1) {
        echo "  Sub Unit 1: {$bp->subSatuan1->nama} (ID: {$bp->sub_satuan_1_id}) - Conversion: {$bp->sub_satuan_1_konversi}\n";
    } else {
        echo "  Sub Unit 1: Not set\n";
    }
    
    if ($bp->sub_satuan_2_id && $bp->subSatuan2) {
        echo "  Sub Unit 2: {$bp->subSatuan2->nama} (ID: {$bp->sub_satuan_2_id}) - Conversion: {$bp->sub_satuan_2_konversi}\n";
    } else {
        echo "  Sub Unit 2: Not set\n";
    }
    
    if ($bp->sub_satuan_3_id && $bp->subSatuan3) {
        echo "  Sub Unit 3: {$bp->subSatuan3->nama} (ID: {$bp->sub_satuan_3_id}) - Conversion: {$bp->sub_satuan_3_konversi}\n";
    } else {
        echo "  Sub Unit 3: Not set\n";
    }
    
    // Check if has production movements
    $hasProductionMovement = StockMovement::where('item_type', 'support')
        ->where('item_id', $bp->id)
        ->where('ref_type', 'production')
        ->exists();
    
    echo "  Has Production Movement: " . ($hasProductionMovement ? 'YES' : 'NO') . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "ITEMS WITH PRODUCTION MOVEMENTS:\n";

// Show items that have production movements and their conversion details
$materialsWithProduction = StockMovement::where('item_type', 'material')
    ->where('ref_type', 'production')
    ->distinct()
    ->pluck('item_id');

$supportsWithProduction = StockMovement::where('item_type', 'support')
    ->where('ref_type', 'production')
    ->distinct()
    ->pluck('item_id');

echo "\nBahan Baku with Production:\n";
foreach ($materialsWithProduction as $itemId) {
    $bb = BahanBaku::find($itemId);
    if ($bb) {
        echo "- {$bb->nama_bahan} (ID: {$itemId})\n";
    }
}

echo "\nBahan Pendukung with Production:\n";
foreach ($supportsWithProduction as $itemId) {
    $bp = BahanPendukung::find($itemId);
    if ($bp) {
        echo "- {$bp->nama_bahan} (ID: {$itemId})\n";
    }
}

echo "\nDone!\n";