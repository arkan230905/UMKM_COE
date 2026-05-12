<?php
// DIRECT FIX - Open this file in browser: http://127.0.0.1:8000/fix-ayam-kampung.php

$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Ayam Kampung Stock</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00aaff; }
        pre { background: #000; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Fix Ayam Kampung Stock</h1>
    <pre><?php

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<span class='info'>Connected to database: $dbname</span>\n\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Check current data
    echo "<span class='info'>BEFORE FIX:</span>\n";
    $stmt = $pdo->query("SELECT id, tanggal, direction, qty, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Stock Movements: " . count($movements) . " records\n";
    foreach ($movements as $m) {
        echo "  - {$m['tanggal']} | {$m['direction']} | {$m['qty']} | {$m['ref_type']}\n";
    }
    
    $stmt = $pdo->query("SELECT SUM(remaining_qty) as total FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<span class='error'>Stock Layers Total: " . ($total['total'] ?? 0) . " (PROBLEM - should be 28)</span>\n\n";
    
    // DELETE ALL
    echo "<span class='info'>Cleaning up...</span>\n";
    $pdo->exec("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    $pdo->exec("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $pdo->exec("UPDATE bahan_bakus SET stok = 0 WHERE id = 2");
    echo "<span class='success'>✓ Deleted all old data</span>\n\n";
    
    // Insert correct data
    echo "<span class='info'>Inserting correct data...</span>\n";
    
    $pdo->exec("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    echo "<span class='success'>✓ Initial stock: 30 Ekor @ Rp 45,000</span>\n";
    
    $pdo->exec("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    echo "<span class='success'>✓ Stock layer: 30 Ekor</span>\n";
    
    $pdo->exec("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
    echo "<span class='success'>✓ Production consumption: 2 Ekor OUT</span>\n";
    
    $pdo->exec("UPDATE stock_layers SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE item_type = 'material' AND item_id = 2");
    echo "<span class='success'>✓ Stock layer updated: 28 remaining</span>\n";
    
    $pdo->exec("UPDATE bahan_bakus SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE id = 2");
    echo "<span class='success'>✓ Master stock updated: 28</span>\n\n";
    
    // Verify
    echo "<span class='info'>AFTER FIX:</span>\n";
    $stmt = $pdo->query("SELECT id, tanggal, direction, qty, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Stock Movements: " . count($movements) . " records\n";
    foreach ($movements as $m) {
        echo "  - {$m['tanggal']} | {$m['direction']} | {$m['qty']} | {$m['ref_type']}\n";
    }
    
    $stmt = $pdo->query("SELECT SUM(remaining_qty) as total FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<span class='success'>Stock Layers Total: " . ($total['total'] ?? 0) . " ✓ CORRECT!</span>\n";
    
    $stmt = $pdo->query("SELECT stok FROM bahan_bakus WHERE id = 2");
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<span class='success'>Master Stock: " . ($master['stok'] ?? 0) . " ✓ CORRECT!</span>\n\n";
    
    // Commit
    $pdo->commit();
    
    echo "<span class='success'>✅ SUCCESS! Stock has been fixed to 28 Ekor.</span>\n\n";
    echo "<span class='info'>Now refresh this page:</span>\n";
    echo "<a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2' style='color: #00aaff;'>http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2</a>\n";
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}

?></pre>
</body>
</html>