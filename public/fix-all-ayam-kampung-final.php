<?php
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix All Ayam Kampung - FINAL</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        .success { color: #0f0; font-weight: bold; }
        .error { color: #f00; font-weight: bold; }
        .info { color: #0ff; }
        pre { background: #111; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
<h1>🔧 FIX ALL AYAM KAMPUNG DATA - FINAL</h1>
<pre><?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();
    
    echo "<span class='info'>STEP 1: FIX CONVERSION RATIOS</span>\n";
    echo "Setting correct conversion ratios for Ayam Kampung...\n";
    
    // Get satuan IDs
    $stmt = $pdo->query("SELECT id, nama FROM satuans WHERE nama IN ('Ekor', 'Potong', 'Kilogram', 'Kg', 'Gram')");
    $satuans = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $satuans[$row['nama']] = $row['id'];
    }
    
    $ekorId = $satuans['Ekor'] ?? null;
    $potongId = $satuans['Potong'] ?? null;
    $kgId = $satuans['Kilogram'] ?? $satuans['Kg'] ?? null;
    $gramId = $satuans['Gram'] ?? null;
    
    // Update conversion ratios
    $stmt = $pdo->prepare("
        UPDATE bahan_bakus SET
            satuan_id = ?,
            sub_satuan_1_id = ?,
            sub_satuan_1_konversi = 6.0000,
            sub_satuan_2_id = ?,
            sub_satuan_2_konversi = 1.5000,
            sub_satuan_3_id = ?,
            sub_satuan_3_konversi = 1500.0000
        WHERE id = 2
    ");
    $stmt->execute([$ekorId, $potongId, $kgId, $gramId]);
    echo "<span class='success'>✓ Conversion ratios updated</span>\n";
    echo "  - 1 Ekor = 6 Potong\n";
    echo "  - 1 Ekor = 1.5 Kilogram\n";
    echo "  - 1 Ekor = 1,500 Gram\n\n";
    
    echo "<span class='info'>STEP 2: CLEAN UP OLD DATA</span>\n";
    $pdo->exec("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    $pdo->exec("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    echo "<span class='success'>✓ Deleted old stock data</span>\n\n";
    
    echo "<span class='info'>STEP 3: CREATE CORRECT STOCK DATA</span>\n";
    
    // Initial stock: 30 Ekor at Rp 45,000
    echo "Creating initial stock: 30 Ekor @ Rp 45,000...\n";
    $pdo->exec("
        INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')
    ");
    echo "<span class='success'>✓ Initial stock created</span>\n\n";
    
    // Production: 1.6667 Ekor (= 10 Potong) at Rp 45,000
    echo "Creating production consumption: 1.6667 Ekor (10 Potong) @ Rp 45,000...\n";
    $productionEkor = 10 / 6; // 1.6667 Ekor
    $productionCost = $productionEkor * 45000; // 75,000
    
    $stmt = $pdo->prepare("
        INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-11', 'out', ?, 'Ekor', 45000.0000, ?, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')
    ");
    $stmt->execute([$productionEkor, $productionCost]);
    echo "<span class='success'>✓ Production consumption created</span>\n";
    echo "  Qty: $productionEkor Ekor (10 Potong)\n";
    echo "  Cost: Rp " . number_format($productionCost, 2) . "\n\n";
    
    // Stock layer: 30 - 1.6667 = 28.3333 Ekor
    $remainingEkor = 30 - $productionEkor;
    echo "Creating stock layer: $remainingEkor Ekor remaining...\n";
    $stmt = $pdo->prepare("
        INSERT INTO stock_layers 
        (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', ?, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')
    ");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>✓ Stock layer created</span>\n\n";
    
    // Update master data
    echo "Updating master stock...\n";
    $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ? WHERE id = 2");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>✓ Master stock updated</span>\n\n";
    
    echo "<span class='info'>STEP 4: VERIFICATION</span>\n";
    
    // Verify stock movements
    $stmt = $pdo->query("SELECT * FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Stock Movements: " . count($movements) . " records\n";
    foreach ($movements as $m) {
        echo "  - {$m['tanggal']} | {$m['direction']} | {$m['qty']} {$m['satuan']} @ Rp " . number_format($m['unit_cost'], 2) . " = Rp " . number_format($m['total_cost'], 2) . " | {$m['ref_type']}\n";
    }
    echo "\n";
    
    // Calculate expected values for each unit
    echo "<span class='info'>EXPECTED DISPLAY IN EACH UNIT:</span>\n\n";
    
    echo "SATUAN EKOR:\n";
    echo "  01/03/2026 | Stok Awal: 30 Ekor @ Rp 45,000 = Rp 1,350,000 | Total: 30 Ekor\n";
    echo "  11/03/2026 | Produksi: 1.6667 Ekor @ Rp 45,000 = Rp 75,000 | Total: 28.3333 Ekor @ Rp 45,000 = Rp 1,275,000\n\n";
    
    echo "SATUAN POTONG (1 Ekor = 6 Potong):\n";
    echo "  01/03/2026 | Stok Awal: 180 Potong @ Rp 7,500 = Rp 1,350,000 | Total: 180 Potong\n";
    echo "  11/03/2026 | Produksi: 10 Potong @ Rp 7,500 = Rp 75,000 | Total: 170 Potong @ Rp 7,500 = Rp 1,275,000\n\n";
    
    echo "SATUAN KILOGRAM (1 Ekor = 1.5 Kg):\n";
    echo "  01/03/2026 | Stok Awal: 45 Kg @ Rp 30,000 = Rp 1,350,000 | Total: 45 Kg\n";
    echo "  11/03/2026 | Produksi: 2.5 Kg @ Rp 30,000 = Rp 75,000 | Total: 42.5 Kg @ Rp 30,000 = Rp 1,275,000\n\n";
    
    echo "SATUAN GRAM (1 Ekor = 1,500 Gram):\n";
    echo "  01/03/2026 | Stok Awal: 45,000 Gram @ Rp 30 = Rp 1,350,000 | Total: 45,000 Gram\n";
    echo "  11/03/2026 | Produksi: 2,500 Gram @ Rp 30 = Rp 75,000 | Total: 42,500 Gram @ Rp 30 = Rp 1,275,000\n\n";
    
    $pdo->commit();
    
    echo "<span class='success'>✅✅✅ SUCCESS! ALL DATA FIXED! ✅✅✅</span>\n\n";
    echo "Now refresh these pages:\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2' style='color: #0ff;'>Satuan Ekor (Utama)</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=7' style='color: #0ff;'>Satuan Potong</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=2' style='color: #0ff;'>Satuan Kilogram</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=3' style='color: #0ff;'>Satuan Gram</a>\n";
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}
?></pre>
</body>
</html>