<?php

// Direct database fix without Laravel bootstrap
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING AYAM KAMPUNG STOCK ===\n\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Step 1: Check current problematic data
    echo "BEFORE FIX:\n";
    $stmt = $pdo->query("SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Stock Movements: " . count($movements) . " records\n";
    foreach ($movements as $m) {
        echo "  - {$m['tanggal']} | {$m['direction']} | {$m['qty']} | {$m['ref_type']}\n";
    }
    
    $stmt = $pdo->query("SELECT SUM(remaining_qty) as total FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Stock Layers Total: " . ($total['total'] ?? 0) . " (THIS IS THE PROBLEM - should be 28)\n\n";
    
    // Step 2: DELETE ALL existing data
    echo "Cleaning up...\n";
    $pdo->exec("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    $pdo->exec("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $pdo->exec("UPDATE bahan_bakus SET stok = 0 WHERE id = 2");
    echo "✓ Deleted all old data\n\n";
    
    // Step 3: Insert correct data
    echo "Inserting correct data...\n";
    
    // Initial stock: 30 Ekor
    $pdo->exec("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    echo "✓ Created initial stock movement: 30 Ekor\n";
    
    $pdo->exec("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    echo "✓ Created stock layer: 30 Ekor\n";
    
    // Production consumption: 2 Ekor
    $pdo->exec("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
    echo "✓ Created production consumption: 2 Ekor\n";
    
    // Update stock layer to 28
    $pdo->exec("UPDATE stock_layers SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE item_type = 'material' AND item_id = 2");
    echo "✓ Updated stock layer to 28 remaining\n";
    
    // Update master data
    $pdo->exec("UPDATE bahan_bakus SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE id = 2");
    echo "✓ Updated master stock to 28\n\n";
    
    // Step 4: Verify
    echo "AFTER FIX:\n";
    $stmt = $pdo->query("SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Stock Movements: " . count($movements) . " records\n";
    foreach ($movements as $m) {
        echo "  - {$m['tanggal']} | {$m['direction']} | {$m['qty']} | {$m['ref_type']}\n";
    }
    
    $stmt = $pdo->query("SELECT SUM(remaining_qty) as total FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Stock Layers Total: " . ($total['total'] ?? 0) . " (SHOULD BE 28)\n";
    
    $stmt = $pdo->query("SELECT stok FROM bahan_bakus WHERE id = 2");
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Master Stock: " . ($master['stok'] ?? 0) . " (SHOULD BE 28)\n\n";
    
    // Commit transaction
    $pdo->commit();
    
    echo "✅ SUCCESS! Stock has been fixed to 28 Ekor.\n";
    echo "\nPlease refresh the page: http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2\n";
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL is running\n";
    echo "2. Database 'eadt_umkm' exists\n";
    echo "3. Username/password is correct\n";
}
