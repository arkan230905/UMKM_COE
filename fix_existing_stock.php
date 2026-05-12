<?php

/**
 * Fix Existing Stock Script
 * 
 * This script recalculates stock values for all bahan baku
 * to fix the double counting issue in existing data.
 */

require_once 'vendor/autoload.php';

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;
use App\Models\PembelianDetail;

echo "=== FIXING EXISTING STOCK VALUES ===\n\n";

// Fix Bahan Baku Stock
echo "1. FIXING BAHAN BAKU STOCK\n";
echo "---------------------------\n";

$bahanBakus = BahanBaku::with('satuan')->get();

foreach ($bahanBakus as $bahan) {
    echo "Processing: {$bahan->nama_bahan} (ID: {$bahan->id})\n";
    
    $currentStock = (float) $bahan->stok;
    echo "  Current Stock: {$currentStock}\n";
    
    // Method 1: Calculate from stock movements (if available)
    $movements = StockMovement::where('material_type', 'material')
        ->where('material_id', $bahan->id)
        ->get();
    
    if ($movements->count() > 0) {
        $calculatedStock = 0;
        foreach ($movements as $movement) {
            if ($movement->movement_type === 'in') {
                $calculatedStock += $movement->quantity;
            } else {
                $calculatedStock -= $movement->quantity;
            }
        }
        
        echo "  Stock from movements: {$calculatedStock}\n";
        
        // Update if different
        if (abs($calculatedStock - $currentStock) > 0.0001) {
            $bahan->stok = $calculatedStock;
            $bahan->save();
            echo "  ✅ Updated to: {$calculatedStock}\n";
        } else {
            echo "  ✅ Already correct\n";
        }
    } else {
        // Method 2: Calculate from purchase details (if no movements)
        echo "  No movements found, calculating from purchases...\n";
        
        $purchaseDetails = PembelianDetail::where('bahan_baku_id', $bahan->id)->get();
        $calculatedStock = 0;
        
        foreach ($purchaseDetails as $detail) {
            // Use converted quantity if available, otherwise calculate
            $qtyInBaseUnit = $detail->jumlah_satuan_utama ?? ($detail->jumlah * $detail->faktor_konversi);
            $calculatedStock += $qtyInBaseUnit;
        }
        
        echo "  Stock from purchases: {$calculatedStock}\n";
        
        // Update stock
        $bahan->stok = $calculatedStock;
        $bahan->save();
        echo "  ✅ Updated to: {$calculatedStock}\n";
    }
    
    echo "\n";
}

// Fix Bahan Pendukung Stock
echo "2. FIXING BAHAN PENDUKUNG STOCK\n";
echo "-------------------------------\n";

$bahanPendukungs = BahanPendukung::with('satuanRelation')->get();

foreach ($bahanPendukungs as $bahan) {
    echo "Processing: {$bahan->nama_bahan} (ID: {$bahan->id})\n";
    
    $currentStock = (float) $bahan->stok;
    echo "  Current Stock: {$currentStock}\n";
    
    // Calculate from stock movements
    $movements = StockMovement::where('material_type', 'support')
        ->where('material_id', $bahan->id)
        ->get();
    
    if ($movements->count() > 0) {
        $calculatedStock = 0;
        foreach ($movements as $movement) {
            if ($movement->movement_type === 'in') {
                $calculatedStock += $movement->quantity;
            } else {
                $calculatedStock -= $movement->quantity;
            }
        }
        
        echo "  Stock from movements: {$calculatedStock}\n";
        
        // Update if different
        if (abs($calculatedStock - $currentStock) > 0.0001) {
            $bahan->stok = $calculatedStock;
            $bahan->save();
            echo "  ✅ Updated to: {$calculatedStock}\n";
        } else {
            echo "  ✅ Already correct\n";
        }
    } else {
        // Calculate from purchase details
        echo "  No movements found, calculating from purchases...\n";
        
        $purchaseDetails = PembelianDetail::where('bahan_pendukung_id', $bahan->id)->get();
        $calculatedStock = 0;
        
        foreach ($purchaseDetails as $detail) {
            $qtyInBaseUnit = $detail->jumlah_satuan_utama ?? ($detail->jumlah * $detail->faktor_konversi);
            $calculatedStock += $qtyInBaseUnit;
        }
        
        echo "  Stock from purchases: {$calculatedStock}\n";
        
        // Update stock
        $bahan->stok = $calculatedStock;
        $bahan->save();
        echo "  ✅ Updated to: {$calculatedStock}\n";
    }
    
    echo "\n";
}

echo "=== STOCK FIX COMPLETE ===\n";
echo "All stock values have been recalculated and corrected.\n";
echo "Please refresh your browser to see the updated values.\n";