<?php

/**
 * Stock Consistency Test Script
 * 
 * This script tests the stock logic to ensure:
 * 1. No double counting
 * 2. All calculations use base unit (satuan utama)
 * 3. Conversions are consistent
 */

require_once 'vendor/autoload.php';

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

echo "=== STOCK CONSISTENCY TEST ===\n\n";

// Test 1: Conversion Logic Test
echo "1. TESTING CONVERSION LOGIC\n";
echo "----------------------------\n";

$bahanBaku = BahanBaku::with('satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3')->first();
if ($bahanBaku) {
    echo "Testing with: {$bahanBaku->nama_bahan} (ID: {$bahanBaku->id})\n";
    echo "Base Unit: " . ($bahanBaku->satuan->nama ?? 'KG') . "\n";
    
    // Test different unit conversions
    $testCases = [
        ['qty' => 1, 'unit' => 'KG'],
        ['qty' => 1000, 'unit' => 'G'],
        ['qty' => 10, 'unit' => 'ONS'],
        ['qty' => 1, 'unit' => 'EKOR'],
        ['qty' => 1, 'unit' => 'POTONG'],
    ];
    
    foreach ($testCases as $test) {
        try {
            $converted = $bahanBaku->convertToSatuanUtama($test['qty'], $test['unit']);
            echo sprintf("  %d %s = %.4f %s\n", 
                $test['qty'], 
                $test['unit'], 
                $converted, 
                $bahanBaku->satuan->nama ?? 'KG'
            );
        } catch (\Exception $e) {
            echo "  ERROR: {$test['qty']} {$test['unit']} - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
} else {
    echo "No BahanBaku records found!\n\n";
}

// Test 2: Stock Update Consistency
echo "2. TESTING STOCK UPDATE CONSISTENCY\n";
echo "-----------------------------------\n";

$bahanBaku = BahanBaku::first();
if ($bahanBaku) {
    $originalStock = $bahanBaku->stok;
    echo "Original Stock: {$originalStock}\n";
    
    // Test adding stock in different units
    $testQty = 2;
    $testUnit = 'KG';
    
    echo "Adding {$testQty} {$testUnit}...\n";
    
    // Convert to base unit
    $convertedQty = $bahanBaku->convertToSatuanUtama($testQty, $testUnit);
    echo "Converted to base unit: {$convertedQty}\n";
    
    // Update stock
    $result = $bahanBaku->updateStok($convertedQty, 'in', 'Test stock update');
    
    $bahanBaku->refresh();
    $newStock = $bahanBaku->stok;
    $expectedStock = $originalStock + $convertedQty;
    
    echo "Expected Stock: {$expectedStock}\n";
    echo "Actual Stock: {$newStock}\n";
    echo "Update Success: " . ($result ? 'YES' : 'NO') . "\n";
    echo "Stock Correct: " . (abs($newStock - $expectedStock) < 0.0001 ? 'YES' : 'NO') . "\n";
    
    // Revert the change
    $bahanBaku->updateStok($convertedQty, 'out', 'Test stock revert');
    $bahanBaku->refresh();
    echo "Reverted Stock: {$bahanBaku->stok}\n\n";
} else {
    echo "No BahanBaku records found!\n\n";
}

// Test 3: Stock Movement vs Master Stock Comparison
echo "3. COMPARING STOCK MOVEMENTS VS MASTER STOCK\n";
echo "--------------------------------------------\n";

$materials = BahanBaku::with('satuan')->limit(5)->get();

foreach ($materials as $material) {
    // Calculate stock from movements
    $movements = StockMovement::where('material_type', 'material')
        ->where('material_id', $material->id)
        ->get();
    
    $calculatedStock = 0;
    foreach ($movements as $movement) {
        if ($movement->movement_type === 'in') {
            $calculatedStock += $movement->quantity;
        } else {
            $calculatedStock -= $movement->quantity;
        }
    }
    
    $masterStock = (float) $material->stok;
    $difference = abs($calculatedStock - $masterStock);
    
    $status = $difference < 0.0001 ? "✅ CONSISTENT" : "❌ INCONSISTENT";
    
    echo sprintf("ID: %d | %s\n", $material->id, substr($material->nama_bahan, 0, 30));
    echo sprintf("  Master Stock: %.4f %s\n", $masterStock, $material->satuan->nama ?? 'KG');
    echo sprintf("  Calculated Stock: %.4f\n", $calculatedStock);
    echo sprintf("  Difference: %.4f | %s\n\n", $difference, $status);
}

// Test 4: Validate No Double Counting
echo "4. VALIDATING NO DOUBLE COUNTING\n";
echo "---------------------------------\n";

echo "Checking recent purchases for double counting...\n";

$recentPurchases = \App\Models\Pembelian::with('details.bahanBaku')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

foreach ($recentPurchases as $purchase) {
    echo "Purchase ID: {$purchase->id} | Date: {$purchase->tanggal}\n";
    
    foreach ($purchase->details as $detail) {
        if ($detail->bahanBaku) {
            $material = $detail->bahanBaku;
            
            // Check if there are corresponding stock movements
            $movements = StockMovement::where('material_type', 'material')
                ->where('material_id', $material->id)
                ->where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->get();
            
            $movementCount = $movements->count();
            $totalMovementQty = $movements->sum('quantity');
            
            $expectedQty = $detail->jumlah_satuan_utama ?? ($detail->jumlah * $detail->faktor_konversi);
            
            echo sprintf("  Material: %s\n", $material->nama_bahan);
            echo sprintf("    Expected Qty: %.4f\n", $expectedQty);
            echo sprintf("    Movement Records: %d\n", $movementCount);
            echo sprintf("    Total Movement Qty: %.4f\n", $totalMovementQty);
            
            if ($movementCount > 1) {
                echo "    ⚠️  WARNING: Multiple movement records detected!\n";
            }
            
            if (abs($totalMovementQty - $expectedQty) > 0.0001) {
                echo "    ❌ ERROR: Movement quantity doesn't match expected!\n";
            } else {
                echo "    ✅ Movement quantity matches expected\n";
            }
            echo "\n";
        }
    }
}

echo "=== TEST COMPLETE ===\n";
echo "\nKEY VALIDATION POINTS:\n";
echo "1. ✅ Conversion logic should be consistent\n";
echo "2. ✅ Stock updates should use converted quantities only\n";
echo "3. ✅ Master stock should match calculated stock from movements\n";
echo "4. ✅ No double counting in stock movements\n";
echo "5. ✅ All stock values should be in base unit (satuan utama)\n";