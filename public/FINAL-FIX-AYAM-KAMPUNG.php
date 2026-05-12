<?php
// FINAL FIX - Standalone PHP script
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>FINAL FIX - Ayam Kampung</title>
    <style>
        body { font-family: 'Courier New', monospace; padding: 20px; background: #000; color: #0f0; }
        .success { color: #0f0; font-weight: bold; font-size: 18px; }
        .error { color: #f00; font-weight: bold; font-size: 18px; }
        .info { color: #0ff; font-size: 16px; }
        pre { background: #111; padding: 15px; border-radius: 5px; border: 2px solid #0f0; }
        a { color: #0ff; text-decoration: none; font-weight: bold; }
        a:hover { color: #fff; }
    </style>
</head>
<body>
<h1 style="color: #0ff;">🔧 FINAL FIX - AYAM KAMPUNG STOCK</h1>
<pre><?php

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<span class='info'>Starting fix process...</span>\n\n";
    
    $pdo->beginTransaction();
    
    // ========================================
    // STEP 1: FIX CONVERSION RATIOS
    // ========================================
    echo "<span class='info'>STEP 1: Fixing conversion ratios...</span>\n";
    
    $stmt = $pdo->query("SELECT id, nama FROM satuans WHERE nama IN ('Ekor', 'Potong', 'Kilogram', 'Kg', 'Gram')");
    $satuans = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $satuans[$row['nama']] = $row['id'];
    }
    
    $ekorId = $satuans['Ekor'] ?? null;
    $potongId = $satuans['Potong'] ?? null;
    $kgId = $satuans['Kilogram'] ?? $satuans['Kg'] ?? null;
    $gramId = $satuans['Gram'] ?? null;
    
    if (!$ekorId || !$potongId || !$kgId || !$gramId) {
        throw new Exception("Satuan not found! Ekor=$ekorId, Potong=$potongId, Kg=$kgId, Gram=$gramId");
    }
    
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
    
    echo "<span class='success'>✓ Conversion ratios updated:</span>\n";
    echo "  - 1 Ekor = 6 Potong\n";
    echo "  - 1 Ekor = 1.5 Kilogram\n";
    echo "  - 1 Ekor = 1,500 Gram\n\n";
    
    // ========================================
    // STEP 2: CLEAN OLD DATA
    // ========================================
    echo "<span class='info'>STEP 2: Cleaning old data...</span>\n";
    
    $deleted1 = $pdo->exec("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    $deleted2 = $pdo->exec("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    
    echo "<span class='success'>✓ Deleted $deleted1 stock movements</span>\n";
    echo "<span class='success'>✓ Deleted $deleted2 stock layers</span>\n\n";
    
    // ========================================
    // STEP 3: CREATE CORRECT DATA
    // ========================================
    echo "<span class='info'>STEP 3: Creating correct stock data...</span>\n";
    
    // Initial stock: 30 Ekor @ Rp 45,000
    $pdo->exec("
        INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')
    ");
    echo "<span class='success'>✓ Initial stock: 30 Ekor @ Rp 45,000 = Rp 1,350,000</span>\n";
    
    // Production: 1.6667 Ekor (10 Potong) @ Rp 45,000
    $productionEkor = 10.0 / 6.0; // 1.666666...
    $productionCost = $productionEkor * 45000.0; // 75,000
    
    $stmt = $pdo->prepare("
        INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-11', 'out', ?, 'Ekor', 45000.0000, ?, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')
    ");
    $stmt->execute([$productionEkor, $productionCost]);
    echo "<span class='success'>✓ Production: " . number_format($productionEkor, 4) . " Ekor (10 Potong) @ Rp 45,000 = Rp " . number_format($productionCost, 2) . "</span>\n\n";
    
    // Stock layer: 30 - 1.6667 = 28.3333 Ekor
    $remainingEkor = 30.0 - $productionEkor;
    $stmt = $pdo->prepare("
        INSERT INTO stock_layers 
        (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', ?, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')
    ");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>✓ Stock layer: " . number_format($remainingEkor, 4) . " Ekor remaining</span>\n";
    
    // Master data
    $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ?, updated_at = NOW() WHERE id = 2");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>✓ Master stock: " . number_format($remainingEkor, 4) . " Ekor</span>\n\n";
    
    // ========================================
    // STEP 4: VERIFICATION
    // ========================================
    echo "<span class='info'>STEP 4: Verification...</span>\n";
    
    $stmt = $pdo->query("SELECT * FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Stock Movements: " . count($movements) . " records\n";
    foreach ($movements as $m) {
        echo "  - {$m['tanggal']} | {$m['direction']} | {$m['qty']} {$m['satuan']} @ Rp " . number_format($m['unit_cost'], 2) . " = Rp " . number_format($m['total_cost'], 2) . " | {$m['ref_type']}\n";
    }
    echo "\n";
    
    $stmt = $pdo->query("SELECT * FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $layer = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Stock Layer: {$layer['remaining_qty']} {$layer['satuan']} @ Rp " . number_format($layer['unit_cost'], 2) . "\n\n";
    
    // ========================================
    // EXPECTED DISPLAY
    // ========================================
    echo "<span class='info'>EXPECTED DISPLAY IN EACH UNIT:</span>\n\n";
    
    $finalEkor = $remainingEkor;
    $finalPotong = $finalEkor * 6;
    $finalKg = $finalEkor * 1.5;
    $finalGram = $finalEkor * 1500;
    
    echo "<span class='success'>SATUAN EKOR:</span>\n";
    echo "  Stok Awal: 30 Ekor @ Rp 45,000 = Rp 1,350,000\n";
    echo "  Produksi: " . number_format($productionEkor, 4) . " Ekor @ Rp 45,000 = Rp " . number_format($productionCost, 2) . "\n";
    echo "  TOTAL: " . number_format($finalEkor, 4) . " Ekor @ Rp 45,000 = Rp " . number_format($finalEkor * 45000, 2) . "\n\n";
    
    echo "<span class='success'>SATUAN POTONG (1 Ekor = 6 Potong):</span>\n";
    echo "  Stok Awal: 180 Potong @ Rp 7,500 = Rp 1,350,000\n";
    echo "  Produksi: 10 Potong @ Rp 7,500 = Rp 75,000\n";
    echo "  TOTAL: " . number_format($finalPotong, 4) . " Potong @ Rp 7,500 = Rp " . number_format($finalPotong * 7500, 2) . "\n\n";
    
    echo "<span class='success'>SATUAN KILOGRAM (1 Ekor = 1.5 Kg):</span>\n";
    echo "  Stok Awal: 45 Kg @ Rp 30,000 = Rp 1,350,000\n";
    echo "  Produksi: 2.5 Kg @ Rp 30,000 = Rp 75,000\n";
    echo "  TOTAL: " . number_format($finalKg, 4) . " Kg @ Rp 30,000 = Rp " . number_format($finalKg * 30000, 2) . "\n\n";
    
    echo "<span class='success'>SATUAN GRAM (1 Ekor = 1,500 Gram):</span>\n";
    echo "  Stok Awal: 45,000 Gram @ Rp 30 = Rp 1,350,000\n";
    echo "  Produksi: 2,500 Gram @ Rp 30 = Rp 75,000\n";
    echo "  TOTAL: " . number_format($finalGram, 0) . " Gram @ Rp 30 = Rp " . number_format($finalGram * 30, 2) . "\n\n";
    
    $pdo->commit();
    
    echo "<span class='success'>✅✅✅ DATABASE FIX COMPLETE! ✅✅✅</span>\n\n";
    echo "<span class='info'>Now refresh these pages:</span>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2'>Satuan Ekor</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$potongId'>Satuan Potong</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$kgId'>Satuan Kilogram</a>\n";
    echo "- <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$gramId'>Satuan Gram</a>\n\n";
    
    echo "<span class='info'>If display is still wrong, press Ctrl+F5 to hard refresh!</span>\n";
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<span class='error'>❌ DATABASE ERROR: " . $e->getMessage() . "</span>\n";
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}

?></pre>
</body>
</html>