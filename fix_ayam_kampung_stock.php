<?php

// Simple fix without Laravel bootstrap
try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING AYAM KAMPUNG INITIAL STOCK ===\n\n";
    
    $pdo->beginTransaction();
    
    // Check if initial stock exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stock_movements WHERE item_type='material' AND item_id=2 AND ref_type='initial_stock'");
    $stmt->execute();
    $hasInitialStock = $stmt->fetchColumn() > 0;
    
    if (!$hasInitialStock) {
        echo "❌ Initial stock missing! Adding...\n";
        
        // Add initial stock
        $stmt = $pdo->prepare("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00'
        ]);
        echo "✅ Initial stock added: 30 Ekor\n";
        
        // Calculate remaining stock
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(qty), 0) FROM stock_movements WHERE item_type='material' AND item_id=2 AND ref_type='production' AND direction='out'");
        $stmt->execute();
        $productionUsage = $stmt->fetchColumn();
        
        $remainingStock = 30.0 - $productionUsage;
        echo "Production usage: $productionUsage Ekor\n";
        echo "Remaining stock: $remainingStock Ekor\n";
        
        // Delete old stock layers
        $stmt = $pdo->prepare("DELETE FROM stock_layers WHERE item_type='material' AND item_id=2");
        $stmt->execute();
        
        // Add new stock layer
        if ($remainingStock > 0) {
            $stmt = $pdo->prepare("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'material', 2, '2026-03-01', $remainingStock, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00'
            ]);
            echo "✅ Stock layer added: $remainingStock Ekor\n";
        }
        
        // Update master stock
        $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ? WHERE id = 2");
        $stmt->execute([$remainingStock]);
        echo "✅ Master stock updated: $remainingStock Ekor\n";
        
    } else {
        echo "✅ Initial stock already exists\n";
    }
    
    $pdo->commit();
    echo "\n🎉 SUCCESS! Check laporan stok now.\n";
    
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}