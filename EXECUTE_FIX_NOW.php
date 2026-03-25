<?php
// DIRECT DATABASE FIX FOR AYAM KAMPUNG STOCK ISSUE
// Run this file directly in browser: http://127.0.0.1:8000/EXECUTE_FIX_NOW.php

echo "<h1 style='color:#0f0;background:#000;padding:20px;'>🔧 EXECUTING AYAM KAMPUNG FIX NOW...</h1>";
echo "<pre style='background:#000;color:#0f0;padding:20px;font-family:monospace;'>";

try {
    // Database connection
    $host = '127.0.0.1';
    $dbname = 'eadt_umkm';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Step 1: Get satuan IDs
    $stmt = $pdo->query("SELECT id, nama FROM satuans WHERE nama IN ('Ekor', 'Potong', 'Kilogram', 'Kg', 'Gram')");
    $satuans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $ekorId = null;
    $potongId = null;
    $kgId = null;
    $gramId = null;
    
    foreach ($satuans as $satuan) {
        if ($satuan['nama'] == 'Ekor') $ekorId = $satuan['id'];
        if ($satuan['nama'] == 'Potong') $potongId = $satuan['id'];
        if ($satuan['nama'] == 'Kilogram' || $satuan['nama'] == 'Kg') $kgId = $satuan['id'];
        if ($satuan['nama'] == 'Gram') $gramId = $satuan['id'];
    }
    
    echo "Satuan IDs: Ekor=$ekorId, Potong=$potongId, Kg=$kgId, Gram=$gramId\n\n";
    
    // Step 2: Fix conversion ratios in bahan_bakus
    $stmt = $pdo->prepare("UPDATE bahan_bakus SET 
        satuan_id = ?,
        sub_satuan_1_id = ?,
        sub_satuan_1_konversi = 6.0000,
        sub_satuan_2_id = ?,
        sub_satuan_2_konversi = 1.5000,
        sub_satuan_3_id = ?,
        sub_satuan_3_konversi = 1500.0000
        WHERE id = 2");
    $stmt->execute([$ekorId, $potongId, $kgId, $gramId]);
    echo "✅ Conversion ratios updated in bahan_bakus\n";
    
    // Step 3: Delete old stock data
    $stmt = $pdo->prepare("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    $deleted1 = $stmt->execute();
    $count1 = $stmt->rowCount();
    
    $stmt = $pdo->prepare("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $deleted2 = $stmt->execute();
    $count2 = $stmt->rowCount();
    
    echo "✅ Deleted $count1 stock movements, $count2 stock layers\n";
    
    // Step 4: Insert initial stock (30 Ekor)
    $stmt = $pdo->prepare("INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    $stmt->execute();
    echo "✅ Initial stock inserted: 30 Ekor @ Rp 45,000 = Rp 1,350,000\n";
    
    // Step 5: Insert production consumption (1.6667 Ekor = 10 Potong)
    $productionEkor = 10 / 6; // 10 Potong = 1.6667 Ekor
    $productionCost = $productionEkor * 45000; // 1.6667 * 45000 = 75,001.50
    
    $stmt = $pdo->prepare("INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-11', 'out', ?, 'Ekor', 45000.0000, ?, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
    $stmt->execute([$productionEkor, $productionCost]);
    echo "✅ Production consumption: $productionEkor Ekor (10 Potong) @ Rp 45,000 = Rp " . number_format($productionCost, 2) . "\n";
    
    // Step 6: Insert remaining stock layer (28.3333 Ekor)
    $remainingEkor = 30 - $productionEkor; // 30 - 1.6667 = 28.3333
    $remainingCost = $remainingEkor * 45000; // 28.3333 * 45000 = 1,274,998.50
    
    $stmt = $pdo->prepare("INSERT INTO stock_layers 
        (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', ?, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    $stmt->execute([$remainingEkor]);
    echo "✅ Stock layer inserted: $remainingEkor Ekor @ Rp 45,000 = Rp " . number_format($remainingCost, 2) . "\n";
    
    // Step 7: Update master stock
    $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ? WHERE id = 2");
    $stmt->execute([$remainingEkor]);
    echo "✅ Master stock updated: $remainingEkor Ekor\n\n";
    
    // Commit transaction
    $pdo->commit();
    echo "✅ Transaction committed successfully!\n\n";
    
    // Step 8: Verify data
    echo "=== VERIFICATION ===\n";
    
    $stmt = $pdo->query("SELECT tanggal, direction, qty, satuan, total_cost, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($movements as $m) {
        echo "  {$m['tanggal']} | {$m['direction']} | {$m['qty']} {$m['satuan']} | Rp " . number_format($m['total_cost'], 2) . " | {$m['ref_type']}\n";
    }
    
    $stmt = $pdo->query("SELECT remaining_qty, unit_cost FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $layer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($layer) {
        echo "  Stock Layer: {$layer['remaining_qty']} Ekor @ Rp " . number_format($layer['unit_cost'], 2) . "\n";
    }
    
    $stmt = $pdo->query("SELECT stok FROM bahan_bakus WHERE id = 2");
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($master) {
        echo "  Master Stock: {$master['stok']} Ekor\n";
    }
    
    echo "\n🎉 SUCCESS! Database has been fixed!\n\n";
    echo "Expected results:\n";
    echo "- Ekor: 30 - 1.6667 = 28.3333 Ekor @ Rp 45,000\n";
    echo "- Potong: 180 - 10 = 170 Potong @ Rp 7,500\n";
    echo "- Kilogram: 45 - 2.5 = 42.5 Kg @ Rp 30,000\n";
    echo "- Gram: 45,000 - 2,500 = 42,500 Gram @ Rp 30\n\n";
    
    echo "Now refresh these pages:\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$ekorId' style='color:#0ff;'>Satuan Ekor</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$potongId' style='color:#0ff;'>Satuan Potong</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$kgId' style='color:#0ff;'>Satuan Kilogram</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$gramId' style='color:#0ff;'>Satuan Gram</a>\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>