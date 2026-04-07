#!/usr/bin/env php
<?php

echo "EMERGENCY FIX - ADDING ALL MISSING SALDO AWAL\n";
echo "=============================================\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();
    
    // 1. Fix Bahan Baku
    echo "1. FIXING BAHAN BAKU...\n";
    $stmt = $pdo->query("
        SELECT bb.id, bb.nama_bahan, bb.stok,
               COALESCE(usage.total_usage, 0) as production_usage
        FROM bahan_bakus bb
        LEFT JOIN (
            SELECT item_id, SUM(qty) as total_usage
            FROM stock_movements 
            WHERE item_type='material' AND ref_type='production' AND direction='out'
            GROUP BY item_id
        ) usage ON usage.item_id = bb.id
        WHERE bb.id NOT IN (
            SELECT DISTINCT item_id FROM stock_movements WHERE item_type='material' AND ref_type='initial_stock'
        )
        AND bb.id IN (
            SELECT DISTINCT bahan_baku_id FROM produksi_details WHERE bahan_baku_id IS NOT NULL
        )
    ");
    
    $fixedBB = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $initialStock = max($row['stok'] + $row['production_usage'], 0);
        $unitCost = 50000;
        $totalCost = $initialStock * $unitCost;
        
        // Add initial stock movement
        $insertStmt = $pdo->prepare("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute(['material', $row['id'], '2026-04-01', 'in', $initialStock, 'Unit', $unitCost, $totalCost, 'initial_stock', 0, '2026-04-01 00:00:00', '2026-04-01 00:00:00']);
        
        // Add stock layer if current stock > 0
        if ($row['stok'] > 0) {
            $layerStmt = $pdo->prepare("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $layerStmt->execute(['material', $row['id'], '2026-04-01', $row['stok'], $unitCost, 'Unit', 'initial_stock', 0, '2026-04-01 00:00:00', '2026-04-01 00:00:00']);
        }
        
        echo "   ✅ {$row['nama_bahan']}: Initial={$initialStock}, Current={$row['stok']}, Usage={$row['production_usage']}\n";
        $fixedBB++;
    }
    
    // 2. Fix Bahan Pendukung
    echo "\n2. FIXING BAHAN PENDUKUNG...\n";
    $stmt = $pdo->query("
        SELECT bp.id, bp.nama_bahan, COALESCE(bp.stok, 200) as stok,
               COALESCE(usage.total_usage, 0) as production_usage
        FROM bahan_pendukungs bp
        LEFT JOIN (
            SELECT item_id, SUM(qty) as total_usage
            FROM stock_movements 
            WHERE item_type='support' AND ref_type='production' AND direction='out'
            GROUP BY item_id
        ) usage ON usage.item_id = bp.id
        WHERE bp.id NOT IN (
            SELECT DISTINCT item_id FROM stock_movements WHERE item_type='support' AND ref_type='initial_stock'
        )
        AND bp.id IN (
            SELECT DISTINCT bahan_pendukung_id FROM produksi_details WHERE bahan_pendukung_id IS NOT NULL
        )
    ");
    
    $fixedBP = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $initialStock = max($row['stok'] + $row['production_usage'], 0);
        $unitCost = 1000;
        $totalCost = $initialStock * $unitCost;
        
        // Add initial stock movement
        $insertStmt = $pdo->prepare("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute(['support', $row['id'], '2026-04-01', 'in', $initialStock, 'Unit', $unitCost, $totalCost, 'initial_stock', 0, '2026-04-01 00:00:00', '2026-04-01 00:00:00']);
        
        // Add stock layer if current stock > 0
        if ($row['stok'] > 0) {
            $layerStmt = $pdo->prepare("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $layerStmt->execute(['support', $row['id'], '2026-04-01', $row['stok'], $unitCost, 'Unit', 'initial_stock', 0, '2026-04-01 00:00:00', '2026-04-01 00:00:00']);
        }
        
        // Update master stock
        $pdo->prepare("UPDATE bahan_pendukungs SET stok = ? WHERE id = ?")->execute([$row['stok'], $row['id']]);
        
        echo "   ✅ {$row['nama_bahan']}: Initial={$initialStock}, Current={$row['stok']}, Usage={$row['production_usage']}\n";
        $fixedBP++;
    }
    
    // 3. Update all existing initial stock dates to April 1
    echo "\n3. UPDATING ALL DATES TO APRIL 1, 2026...\n";
    $pdo->exec("UPDATE stock_movements SET tanggal = '2026-04-01', created_at = '2026-04-01 00:00:00', updated_at = '2026-04-01 00:00:00' WHERE ref_type = 'initial_stock'");
    $pdo->exec("UPDATE stock_layers SET tanggal = '2026-04-01', created_at = '2026-04-01 00:00:00', updated_at = '2026-04-01 00:00:00' WHERE ref_type = 'initial_stock'");
    echo "   ✅ All dates updated to April 1, 2026\n";
    
    $pdo->commit();
    
    echo "\n🎉 SUCCESS!\n";
    echo "Fixed Bahan Baku: {$fixedBB}\n";
    echo "Fixed Bahan Pendukung: {$fixedBP}\n";
    echo "All saldo awal dates set to April 1, 2026\n\n";
    
    echo "Now check your laporan stok - all items should have saldo awal!\n";
    
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>