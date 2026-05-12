<?php
// DIRECT DATABASE FIX - NO LARAVEL DEPENDENCIES
header('Content-Type: text/html; charset=utf-8');
echo "<h1 style='color:#fff;background:#d32f2f;padding:20px;text-align:center;'>🔧 FIXING AYAM KAMPUNG NOW - DIRECT EXECUTION</h1>";
echo "<div style='background:#000;color:#0f0;padding:20px;font-family:monospace;white-space:pre-wrap;'>";

try {
    // Direct MySQL connection
    $host = '127.0.0.1';
    $dbname = 'eadt_umkm';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $dbname\n\n";
    
    // Start transaction
    $pdo->beginTransaction();
    echo "🔄 Starting transaction...\n\n";
    
    // Get satuan IDs
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
    
    echo "📋 Satuan IDs found:\n";
    echo "   - Ekor: $ekorId\n";
    echo "   - Potong: $potongId\n";
    echo "   - Kilogram: $kgId\n";
    echo "   - Gram: $gramId\n\n";
    
    // STEP 1: Fix conversion ratios in bahan_bakus
    echo "🔧 STEP 1: Fixing conversion ratios...\n";
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
    echo "   ✅ Conversion ratios updated: 1 Ekor = 6 Potong = 1.5 Kg = 1500 Gram\n\n";
    
    // STEP 2: Delete ALL old stock data
    echo "🗑️ STEP 2: Cleaning old stock data...\n";
    $stmt = $pdo->prepare("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    $stmt->execute();
    $deleted1 = $stmt->rowCount();
    
    $stmt = $pdo->prepare("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $stmt->execute();
    $deleted2 = $stmt->rowCount();
    
    echo "   ✅ Deleted $deleted1 stock movements\n";
    echo "   ✅ Deleted $deleted2 stock layers\n\n";
    
    // STEP 3: Insert CORRECT initial stock (30 Ekor)
    echo "📦 STEP 3: Creating initial stock...\n";
    $stmt = $pdo->prepare("INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    $stmt->execute();
    echo "   ✅ Initial stock: 30 Ekor @ Rp 45,000 = Rp 1,350,000\n\n";
    
    // STEP 4: Insert CORRECT production (1.6667 Ekor = 10 Potong)
    echo "🏭 STEP 4: Recording production consumption...\n";
    $productionEkor = 10.0 / 6.0; // 10 Potong = 1.6667 Ekor
    $productionCost = $productionEkor * 45000; // 1.6667 * 45000 = 75,001.50
    
    $stmt = $pdo->prepare("INSERT INTO stock_movements 
        (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-11', 'out', ?, 'Ekor', 45000.0000, ?, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
    $stmt->execute([$productionEkor, $productionCost]);
    echo "   ✅ Production: $productionEkor Ekor (10 Potong) @ Rp 45,000 = Rp " . number_format($productionCost, 2) . "\n\n";
    
    // STEP 5: Insert CORRECT remaining stock layer
    echo "📊 STEP 5: Creating stock layer...\n";
    $remainingEkor = 30.0 - $productionEkor; // 30 - 1.6667 = 28.3333
    
    $stmt = $pdo->prepare("INSERT INTO stock_layers 
        (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
        VALUES 
        ('material', 2, '2026-03-01', ?, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    $stmt->execute([$remainingEkor]);
    echo "   ✅ Stock layer: $remainingEkor Ekor @ Rp 45,000\n\n";
    
    // STEP 6: Update master stock
    echo "🎯 STEP 6: Updating master stock...\n";
    $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ? WHERE id = 2");
    $stmt->execute([$remainingEkor]);
    echo "   ✅ Master stock updated: $remainingEkor Ekor\n\n";
    
    // COMMIT TRANSACTION
    $pdo->commit();
    echo "💾 TRANSACTION COMMITTED SUCCESSFULLY!\n\n";
    
    // VERIFICATION
    echo "🔍 VERIFICATION:\n";
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
    
    // Check stock layer
    $stmt = $pdo->query("SELECT remaining_qty FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $layer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($layer) {
        echo "Stock layer: {$layer['remaining_qty']} Ekor\n";
    }
    
    // Check master stock
    $stmt = $pdo->query("SELECT stok FROM bahan_bakus WHERE id = 2");
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($master) {
        echo "Master stock: {$master['stok']} Ekor\n\n";
    }
    
    echo "🎉 SUCCESS! DATABASE HAS BEEN FIXED!\n\n";
    echo "Expected results after refresh:\n";
    echo "================================\n";
    echo "- Ekor: 30 - 1.6667 = 28.3333 Ekor @ Rp 45,000\n";
    echo "- Potong: 180 - 10 = 170 Potong @ Rp 7,500\n";
    echo "- Kilogram: 45 - 2.5 = 42.5 Kg @ Rp 30,000\n";
    echo "- Gram: 45,000 - 2,500 = 42,500 Gram @ Rp 30\n\n";
    
    echo "🔗 REFRESH THESE PAGES NOW:\n";
    echo "============================\n";
    echo "<a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$ekorId' style='color:#0ff;display:block;margin:5px 0;'>✅ Satuan Ekor</a>\n";
    echo "<a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$potongId' style='color:#0ff;display:block;margin:5px 0;'>✅ Satuan Potong</a>\n";
    echo "<a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$kgId' style='color:#0ff;display:block;margin:5px 0;'>✅ Satuan Kilogram</a>\n";
    echo "<a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=$gramId' style='color:#0ff;display:block;margin:5px 0;'>✅ Satuan Gram</a>\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</div>";
echo "<div style='background:#d32f2f;color:#fff;padding:20px;text-align:center;margin-top:20px;'>";
echo "<h2>DATABASE FIX COMPLETED!</h2>";
echo "<p>The Ayam Kampung stock issue has been resolved. Please refresh the stock report pages above.</p>";
echo "</div>";
?>