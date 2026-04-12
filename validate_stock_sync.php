<?php

/**
 * Stock Synchronization Validation Script
 * 
 * This script validates that stock movements are properly synchronized
 * between the stock movement system (for reports) and master table stok fields.
 * 
 * Usage: php validate_stock_sync.php
 */

require_once 'vendor/autoload.php';

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

echo "=== STOCK SYNCHRONIZATION VALIDATION ===\n\n";

// Test Bahan Baku synchronization
echo "1. VALIDATING BAHAN BAKU STOCK SYNC\n";
echo "-----------------------------------\n";

$bahanBakus = BahanBaku::with('satuan')->limit(10)->get();

foreach ($bahanBakus as $bahan) {
    // Calculate stock from movements (like in reports)
    $movements = StockMovement::where('material_type', 'material')
        ->where('material_id', $bahan->id)
        ->get();
    
    $stockFromMovements = 0;
    foreach ($movements as $movement) {
        if ($movement->movement_type === 'in') {
            $stockFromMovements += $movement->quantity;
        } else {
            $stockFromMovements -= $movement->quantity;
        }
    }
    
    // Compare with master table stock
    $masterStock = (float) $bahan->stok;
    $difference = abs($stockFromMovements - $masterStock);
    
    $status = $difference < 0.0001 ? "✅ SYNC" : "❌ NOT SYNC";
    
    echo sprintf(
        "ID: %d | %s | Master: %.4f %s | Movements: %.4f | Diff: %.4f | %s\n",
        $bahan->id,
        substr($bahan->nama_bahan, 0, 20),
        $masterStock,
        $bahan->satuan->nama ?? 'KG',
        $stockFromMovements,
        $difference,
        $status
    );
}

echo "\n2. VALIDATING BAHAN PENDUKUNG STOCK SYNC\n";
echo "---------------------------------------\n";

$bahanPendukungs = BahanPendukung::with('satuanRelation')->limit(10)->get();

foreach ($bahanPendukungs as $bahan) {
    // Calculate stock from movements (like in reports)
    $movements = StockMovement::where('material_type', 'support')
        ->where('material_id', $bahan->id)
        ->get();
    
    $stockFromMovements = 0;
    foreach ($movements as $movement) {
        if ($movement->movement_type === 'in') {
            $stockFromMovements += $movement->quantity;
        } else {
            $stockFromMovements -= $movement->quantity;
        }
    }
    
    // Compare with master table stock
    $masterStock = (float) $bahan->stok;
    $difference = abs($stockFromMovements - $masterStock);
    
    $status = $difference < 0.0001 ? "✅ SYNC" : "❌ NOT SYNC";
    
    echo sprintf(
        "ID: %d | %s | Master: %.4f %s | Movements: %.4f | Diff: %.4f | %s\n",
        $bahan->id,
        substr($bahan->nama_bahan, 0, 20),
        $masterStock,
        $bahan->satuanRelation->nama ?? 'unit',
        $stockFromMovements,
        $difference,
        $status
    );
}

echo "\n=== VALIDATION COMPLETE ===\n";
echo "✅ SYNC = Stock is synchronized\n";
echo "❌ NOT SYNC = Stock needs synchronization\n\n";

echo "NOTES:\n";
echo "- All stock updates now use converted quantities (qty_konversi) in base unit\n";
echo "- Purchase transactions update both stock movements AND master table stok\n";
echo "- Return processing updates both stock movements AND master table stok\n";
echo "- Helper functions ensure consistent stock updates with validation\n";