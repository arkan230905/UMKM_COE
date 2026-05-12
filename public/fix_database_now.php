<?php
// SIMPLE DATABASE FIX - NO DEPENDENCIES
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Ayam Kampung Database</title>
    <style>
        body { font-family: monospace; background: #000; color: #0f0; padding: 20px; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .info { color: #ff0; }
    </style>
</head>
<body>
    <h1>🔧 FIXING AYAM KAMPUNG DATABASE</h1>
    <pre>
<?php
try {
    // Connect to database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<span class='success'>✅ Connected to database</span>\n\n";
    
    // Start transaction
    $pdo->beginTransaction();
    echo "<span class='info'>🔄 Starting transaction...</span>\n\n";
    
    // Get satuan IDs
    $stmt = $pdo->query("SELECT id, nama FROM satuans WHERE nama IN ('Ekor', 'Potong', 'Kilogram', 'Kg', 'Gram')");
    $satuans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $ekorId = null; $potongId = null; $kgId = null; $gramId = null;
    foreach ($satuans as $satuan) {
        if ($satuan['nama'] == 'Ekor') $ekorId = $satuan['id'];
        if ($satuan['nama'] == 'Potong') $potongId = $satuan['id'];
        if ($satuan['nama'] == 'Kilogram' || $satuan['nama'] == 'Kg') $kgId = $satuan['id'];
        if ($satuan['nama'] == 'Gram') $gramId = $satuan['id'];
    }
    
    echo "<span class='info'>📋 Satuan IDs:</span>\n";
    echo "   Ekor: $ekorId\n";
    echo "   Potong: $potongId\n";
    echo "   Kilogram: $kgId\n";
    echo "   Gram: $gramId\n\n";
    
    // 1. Fix conversion ratios
    echo "<span class='info'>🔧 Step 1: Fixing conversion ratios...</span>\n";
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
    echo "<span class='success'>   ✅ Conversion ratios updated: 1 Ekor = 6 Potong = 1.5 Kg = 1500 Gram</span>\n\n";
    
    // 2. Delete old data
    echo "<span class='info'>🗑️ Step 2: Cleaning old data...</span>\n";
    $stmt1 = $pdo->prepare("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    $stmt1->execute();
    $deleted1 = $stmt1->rowCount();
    
    $stmt2 = $pdo->prepare("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $stmt2->execute();
    $deleted2 = $stmt2->rowCount();
    
    echo "<span class='success'>   ✅ Deleted $deleted1 stock movements</span>\n";
    echo "<span class='success'>   ✅ Deleted $deleted2 stock layers</span>\n\n";
    
    // 3. Insert initial stock
    echo "<span class='info'>📦 Step 3: Creating initial stock...</span>\n";
    $stmt = $pdo->prepare("INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    $stmt->execute();
    echo "<span class='success'>   ✅ Initial stock: 30 Ekor @ Rp 45,000 = Rp 1,350,000</span>\n\n";
    
    // 4. Insert production
    echo "<span class='info'>🏭 Step 4: Recording production...</span>\n";
    $productionEkor = 10.0 / 6.0; // 1.6667 Ekor
    $productionCost = $productionEkor * 45000; // 75,001.50
    
    $stmt = $pdo->prepare("INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-11', 'out', ?, 'Ekor', 45000.0000, ?, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
    $stmt->execute([$productionEkor, $productionCost]);
    echo "<span class='success'>   ✅ Production: " . number_format($productionEkor, 4) . " Ekor (10 Potong) @ Rp 45,000 = Rp " . number_format($productionCost, 2) . "</span>\n\n";
    
    // 5. Insert stock layer
    echo "<span class='info'>📊 Step 5: Creating stock layer...</span>\n";
    $remainingEkor = 30.0 - $productionEkor; // 28.3333
    
    $stmt = $pdo->prepare("INSERT INTO stock_layers 
        (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', ?, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>   ✅ Stock layer: " . number_format($remainingEkor, 4) . " Ekor @ Rp 45,000</span>\n\n";
    
    // 6. Update master stock
    echo "<span class='info'>🎯 Step 6: Updating master stock...</span>\n";
    $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ? WHERE id = 2");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>   ✅ Master stock updated: " . number_format($remainingEkor, 4) . " Ekor</span>\n\n";
    
    // Commit transaction
    $pdo->commit();
    echo "<span class='success'>💾 TRANSACTION COMMITTED SUCCESSFULLY!</span>\n\n";
    
    // Verification
    echo "<span class='info'>🔍 VERIFICATION:</span>\n";
    echo "================\n";
    
    // Check conversion ratios
    $stmt = $pdo->query("SELECT sub_satuan_1_konversi, sub_satuan_2_konversi, sub_satuan_3_konversi FROM bahan_bakus WHERE id = 2");
    $ratios = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Conversion ratios:\n";
    echo "  - 1 Ekor = {$ratios['sub_satuan_1_konversi']} Potong\n";
    echo "  - 1 Ekor = {$ratios['sub_satuan_2_konversi']} Kilogram\n";
    echo "  - 1 Ekor = {$ratios['sub_satuan_3_konversi']} Gram\n\n";
    
    // Check stock movements
    $stmt = $pdo->query("SELECT tanggal, direction, qty, satuan, total_cost, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Stock movements:\n";
    foreach ($movements as $m) {
        echo "  {$m['tanggal']} | {$m['direction']} | {$m['qty']} {$m['satuan']} | Rp " . number_format($m['total_cost'], 2) . " | {$m['ref_type']}\n";
    }
    echo "\n";
    
    // Check final stock
    $stmt = $pdo->query("SELECT stok FROM bahan_bakus WHERE id = 2");
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($master) {
        echo "Final master stock: {$master['stok']} Ekor\n\n";
    }
    
    echo "<span class='success'>🎉 SUCCESS! DATABASE HAS BEEN FIXED!</span>\n\n";
    echo "Expected results:\n";
    echo "=================\n";
    echo "- Ekor: 30 - 1.6667 = 28.3333 Ekor @ Rp 45,000\n";
    echo "- Potong: 180 - 10 = 170 Potong @ Rp 7,500\n";
    echo "- Kilogram: 45 - 2.5 = 42.5 Kg @ Rp 30,000\n";
    echo "- Gram: 45,000 - 2,500 = 42,500 Gram @ Rp 30\n\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>Stack trace:</span>\n" . $e->getTraceAsString() . "\n";
}
?>
    </pre>
    
    <div style="background:#333;padding:20px;margin-top:20px;border-radius:5px;">
        <h2 style="color:#0f0;">🔗 REFRESH THESE PAGES NOW:</h2>
        <p><a href="http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2" style="color:#0ff;">📊 Laporan Stok Ayam Kampung</a></p>
        <p><a href="http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=7" style="color:#0ff;">🐔 Satuan Ekor</a></p>
        <p><a href="http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=8" style="color:#0ff;">🍗 Satuan Potong</a></p>
    </div>
</body>
</html>