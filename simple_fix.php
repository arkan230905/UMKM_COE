<?php
$host = 'localhost';
$dbname = 'umkm_coe';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n";
    
    // Check current stock movements
    echo "\n=== Current Stock Movements ===\n";
    $stmt = $pdo->query("SELECT tanggal, direction, qty, satuan, ref_type FROM stock_movements WHERE item_type='material' AND item_id=2 ORDER BY tanggal");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['tanggal']} | {$row['direction']} | {$row['qty']} {$row['satuan']} | {$row['ref_type']}\n";
    }
    
    // Check if initial stock exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM stock_movements WHERE item_type='material' AND item_id=2 AND ref_type='initial_stock'");
    $hasInitial = $stmt->fetchColumn() > 0;
    
    if (!$hasInitial) {
        echo "\n=== Adding Initial Stock ===\n";
        
        $pdo->beginTransaction();
        
        // Add initial stock
        $stmt = $pdo->prepare("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'material', 2, '2026-03-01', 'in', 30.0, 'Ekor', 45000.0, 1350000.0, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00'
        ]);
        
        if ($result) {
            echo "✅ Initial stock added: 30 Ekor\n";
        } else {
            echo "❌ Failed to add initial stock\n";
        }
        
        // Calculate remaining stock
        $stmt = $pdo->query("SELECT COALESCE(SUM(qty), 0) FROM stock_movements WHERE item_type='material' AND item_id=2 AND ref_type='production' AND direction='out'");
        $productionUsage = $stmt->fetchColumn();
        $remainingStock = 30.0 - $productionUsage;
        
        echo "Production usage: $productionUsage Ekor\n";
        echo "Remaining stock: $remainingStock Ekor\n";
        
        // Delete old stock layers
        $pdo->exec("DELETE FROM stock_layers WHERE item_type='material' AND item_id=2");
        
        // Add new stock layer
        if ($remainingStock > 0) {
            $stmt = $pdo->prepare("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'material', 2, '2026-03-01', $remainingStock, 45000.0, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00'
            ]);
            echo "✅ Stock layer added: $remainingStock Ekor\n";
        }
        
        // Update master stock
        $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ? WHERE id = 2");
        $stmt->execute([$remainingStock]);
        echo "✅ Master stock updated: $remainingStock Ekor\n";
        
        $pdo->commit();
        echo "\n🎉 SUCCESS! Initial stock has been restored.\n";
        
    } else {
        echo "\n✅ Initial stock already exists\n";
    }
    
    // Show final result
    echo "\n=== Final Stock Movements ===\n";
    $stmt = $pdo->query("SELECT tanggal, direction, qty, satuan, ref_type FROM stock_movements WHERE item_type='material' AND item_id=2 ORDER BY tanggal");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['tanggal']} | {$row['direction']} | {$row['qty']} {$row['satuan']} | {$row['ref_type']}\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    if (isset($pdo)) {
        $pdo->rollback();
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>