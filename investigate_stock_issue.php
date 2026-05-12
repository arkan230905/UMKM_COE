<?php

/**
 * Deep Investigation of Stock Issue
 * 
 * This script will thoroughly investigate why the stock is still 130kg
 * and identify the exact source of the problem.
 */

require_once 'vendor/autoload.php';

use App\Models\BahanBaku;
use App\Models\StockMovement;
use App\Models\PembelianDetail;

echo "=== DEEP STOCK INVESTIGATION ===\n\n";

// Find Ayam Potong (assuming it's the one with 130kg)
$ayamPotong = BahanBaku::where('nama_bahan', 'LIKE', '%ayam%')
    ->orWhere('nama_bahan', 'LIKE', '%potong%')
    ->with('satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3')
    ->first();

if (!$ayamPotong) {
    // Try to find by stock value
    $ayamPotong = BahanBaku::where('stok', 130)->first();
}

if (!$ayamPotong) {
    echo "Could not find Ayam Potong. Let's check all materials with high stock:\n";
    $highStockMaterials = BahanBaku::where('stok', '>', 100)->get();
    foreach ($highStockMaterials as $material) {
        echo "ID: {$material->id} | Name: {$material->nama_bahan} | Stock: {$material->stok}\n";
    }
    exit;
}

echo "INVESTIGATING: {$ayamPotong->nama_bahan} (ID: {$ayamPotong->id})\n";
echo "Current Stock: {$ayamPotong->stok}\n";
echo "Base Unit: " . ($ayamPotong->satuan->nama ?? 'Unknown') . "\n\n";

// 1. Check all purchase details
echo "1. PURCHASE DETAILS ANALYSIS\n";
echo "----------------------------\n";

$purchaseDetails = PembelianDetail::where('bahan_baku_id', $ayamPotong->id)
    ->with('pembelian')
    ->orderBy('created_at', 'asc')
    ->get();

$totalPurchaseStock = 0;
$purchaseCount = 0;

foreach ($purchaseDetails as $detail) {
    $purchaseCount++;
    
    echo "Purchase #{$purchaseCount}:\n";
    echo "  Pembelian ID: {$detail->pembelian_id}\n";
    echo "  Date: " . ($detail->pembelian->tanggal ?? 'Unknown') . "\n";
    echo "  Qty Input: {$detail->jumlah}\n";
    echo "  Satuan: {$detail->satuan}\n";
    echo "  Faktor Konversi: {$detail->faktor_konversi}\n";
    
    // Check if jumlah_satuan_utama exists
    if (isset($detail->jumlah_satuan_utama) && $detail->jumlah_satuan_utama !== null) {
        $qtyInBaseUnit = $detail->jumlah_satuan_utama;
        echo "  Qty Satuan Utama (stored): {$qtyInBaseUnit}\n";
    } else {
        $qtyInBaseUnit = $detail->jumlah * $detail->faktor_konversi;
        echo "  Qty Satuan Utama (calculated): {$qtyInBaseUnit}\n";
    }
    
    $totalPurchaseStock += $qtyInBaseUnit;
    echo "  Running Total: {$totalPurchaseStock}\n\n";
}

echo "TOTAL FROM PURCHASES: {$totalPurchaseStock}\n\n";

// 2. Check stock movements
echo "2. STOCK MOVEMENTS ANALYSIS\n";
echo "---------------------------\n";

$stockMovements = StockMovement::where('material_type', 'material')
    ->where('material_id', $ayamPotong->id)
    ->orderBy('created_at', 'asc')
    ->get();

$totalMovementStock = 0;
$movementCount = 0;

foreach ($stockMovements as $movement) {
    $movementCount++;
    
    echo "Movement #{$movementCount}:\n";
    echo "  Date: {$movement->created_at}\n";
    echo "  Type: {$movement->movement_type}\n";
    echo "  Quantity: {$movement->quantity}\n";
    echo "  Reference: {$movement->reference_type} #{$movement->reference_id}\n";
    
    if ($movement->movement_type === 'in') {
        $totalMovementStock += $movement->quantity;
    } else {
        $totalMovementStock -= $movement->quantity;
    }
    
    echo "  Running Total: {$totalMovementStock}\n\n";
}

echo "TOTAL FROM MOVEMENTS: {$totalMovementStock}\n\n";

// 3. Check for duplicate entries
echo "3. DUPLICATE DETECTION\n";
echo "----------------------\n";

// Group movements by reference
$movementsByReference = $stockMovements->groupBy(function($movement) {
    return $movement->reference_type . '_' . $movement->reference_id;
});

foreach ($movementsByReference as $reference => $movements) {
    if ($movements->count() > 1) {
        echo "⚠️  DUPLICATE FOUND: {$reference}\n";
        foreach ($movements as $movement) {
            echo "    ID: {$movement->id} | Qty: {$movement->quantity} | Date: {$movement->created_at}\n";
        }
        echo "\n";
    }
}

// 4. Check conversion factors
echo "4. CONVERSION FACTORS\n";
echo "---------------------\n";

echo "Sub Satuan 1: " . ($ayamPotong->subSatuan1->nama ?? 'None') . " | Konversi: " . ($ayamPotong->sub_satuan_1_konversi ?? 'None') . "\n";
echo "Sub Satuan 2: " . ($ayamPotong->subSatuan2->nama ?? 'None') . " | Konversi: " . ($ayamPotong->sub_satuan_2_konversi ?? 'None') . "\n";
echo "Sub Satuan 3: " . ($ayamPotong->subSatuan3->nama ?? 'None') . " | Konversi: " . ($ayamPotong->sub_satuan_3_konversi ?? 'None') . "\n\n";

// 5. Summary and diagnosis
echo "5. DIAGNOSIS\n";
echo "------------\n";

echo "Current Stock in DB: {$ayamPotong->stok}\n";
echo "Stock from Purchases: {$totalPurchaseStock}\n";
echo "Stock from Movements: {$totalMovementStock}\n";

$purchaseVsCurrent = $ayamPotong->stok - $totalPurchaseStock;
$movementVsCurrent = $ayamPotong->stok - $totalMovementStock;

echo "Difference (Current - Purchases): {$purchaseVsCurrent}\n";
echo "Difference (Current - Movements): {$movementVsCurrent}\n";

// Possible causes
echo "\nPOSSIBLE CAUSES:\n";

if (abs($purchaseVsCurrent) < 0.0001) {
    echo "✅ Stock matches purchases exactly - no issue with purchase calculation\n";
} elseif (abs($ayamPotong->stok - ($totalPurchaseStock * 2)) < 0.0001) {
    echo "❌ DOUBLE COUNTING: Stock is exactly 2x purchases - classic double counting issue\n";
} elseif ($movementsByReference->filter(fn($movements) => $movements->count() > 1)->count() > 0) {
    echo "❌ DUPLICATE MOVEMENTS: Found duplicate stock movement entries\n";
} else {
    echo "❓ UNKNOWN ISSUE: Need further investigation\n";
}

// 6. Suggested fix
echo "\n6. SUGGESTED FIX\n";
echo "----------------\n";

if (abs($ayamPotong->stok - ($totalPurchaseStock * 2)) < 0.0001) {
    $correctStock = $totalPurchaseStock;
    echo "Set stock to: {$correctStock}\n";
    echo "SQL: UPDATE bahan_bakus SET stok = {$correctStock} WHERE id = {$ayamPotong->id};\n";
} elseif ($totalMovementStock > 0 && abs($totalMovementStock - $totalPurchaseStock) < 0.0001) {
    echo "Use movement-based stock: {$totalMovementStock}\n";
    echo "SQL: UPDATE bahan_bakus SET stok = {$totalMovementStock} WHERE id = {$ayamPotong->id};\n";
} else {
    echo "Manual investigation required - check individual transactions\n";
}

echo "\n=== INVESTIGATION COMPLETE ===\n";