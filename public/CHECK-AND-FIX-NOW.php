<?php
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>CHECK AND FIX NOW</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        .error { color: #f00; font-weight: bold; }
        .success { color: #0f0; font-weight: bold; }
        .warning { color: #ff0; font-weight: bold; }
        button { background: #0f0; color: #000; padding: 15px 30px; font-size: 18px; font-weight: bold; border: none; cursor: pointer; margin: 10px; }
        button:hover { background: #0ff; }
    </style>
</head>
<body>
<h1>🔍 CHECK CURRENT DATA</h1>
<?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>CURRENT STOCK MOVEMENTS:</h2><pre>";
    $stmt = $pdo->query("SELECT * FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $isCorrect = true;
    foreach ($movements as $m) {
        echo "{$m['tanggal']} | {$m['direction']} | {$m['qty']} {$m['satuan']} | {$m['ref_type']}\n";
        
        if ($m['ref_type'] == 'production' && abs($m['qty'] - 1.6667) > 0.01) {
            echo "<span class='error'>  ❌ WRONG! Production should be 1.6667 Ekor, not {$m['qty']}</span>\n";
            $isCorrect = false;
        }
    }
    echo "</pre>";
    
    if (!$isCorrect) {
        echo "<h2><span class='error'>❌ DATA IS WRONG!</span></h2>";
        echo "<p><span class='warning'>Click button below to FIX NOW:</span></p>";
        echo "<form method='POST'>";
        echo "<button type='submit' name='fix' value='1'>🔧 FIX NOW!</button>";
        echo "</form>";
    } else {
        echo "<h2><span class='success'>✅ DATA IS CORRECT!</span></h2>";
        echo "<p>If display is still wrong, clear browser cache (Ctrl+Shift+Delete) or try incognito mode.</p>";
    }
    
    // Handle FIX button
    if (isset($_POST['fix'])) {
        echo "<hr><h2>RUNNING FIX...</h2><pre>";
        
        $pdo->beginTransaction();
        
        // Get satuan IDs
        $stmt = $pdo->query("SELECT id, nama FROM satuans WHERE nama IN ('Ekor', 'Potong', 'Kilogram', 'Kg', 'Gram')");
        $satuans = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $satuans[$row['nama']] = $row['id'];
        }
        
        $ekorId = $satuans['Ekor'];
        $potongId = $satuans['Potong'];
        $kgId = $satuans['Kilogram'] ?? $satuans['Kg'];
        $gramId = $satuans['Gram'];
        
        // Fix conversion ratios
        $pdo->prepare("UPDATE bahan_bakus SET satuan_id=?, sub_satuan_1_id=?, sub_satuan_1_konversi=6, sub_satuan_2_id=?, sub_satuan_2_konversi=1.5, sub_satuan_3_id=?, sub_satuan_3_konversi=1500 WHERE id=2")->execute([$ekorId, $potongId, $kgId, $gramId]);
        echo "<span class='success'>✓ Conversion ratios fixed</span>\n";
        
        // Delete old data
        $pdo->exec("DELETE FROM stock_movements WHERE item_type='material' AND item_id=2");
        $pdo->exec("DELETE FROM stock_layers WHERE item_type='material' AND item_id=2");
        echo "<span class='success'>✓ Old data deleted</span>\n";
        
        // Insert correct data
        $pdo->exec("INSERT INTO stock_movements (item_type,item_id,tanggal,direction,qty,satuan,unit_cost,total_cost,ref_type,ref_id,created_at,updated_at) VALUES ('material',2,'2026-03-01','in',30,'Ekor',45000,1350000,'initial_stock',0,'2026-03-01 00:00:00','2026-03-01 00:00:00')");
        echo "<span class='success'>✓ Initial stock created: 30 Ekor</span>\n";
        
        $productionEkor = 10.0 / 6.0;
        $productionCost = $productionEkor * 45000;
        $stmt = $pdo->prepare("INSERT INTO stock_movements (item_type,item_id,tanggal,direction,qty,satuan,unit_cost,total_cost,ref_type,ref_id,created_at,updated_at) VALUES ('material',2,'2026-03-11','out',?,'Ekor',45000,?,'production',1,'2026-03-11 22:09:05','2026-03-11 22:09:05')");
        $stmt->execute([$productionEkor, $productionCost]);
        echo "<span class='success'>✓ Production created: $productionEkor Ekor (10 Potong)</span>\n";
        
        $remainingEkor = 30 - $productionEkor;
        $stmt = $pdo->prepare("INSERT INTO stock_layers (item_type,item_id,tanggal,remaining_qty,unit_cost,satuan,ref_type,ref_id,created_at,updated_at) VALUES ('material',2,'2026-03-01',?,45000,'Ekor','initial_stock',0,'2026-03-01 00:00:00','2026-03-01 00:00:00')");
        $stmt->execute([$remainingEkor]);
        echo "<span class='success'>✓ Stock layer created: $remainingEkor Ekor</span>\n";
        
        $pdo->prepare("UPDATE bahan_bakus SET stok=? WHERE id=2")->execute([$remainingEkor]);
        echo "<span class='success'>✓ Master stock updated: $remainingEkor Ekor</span>\n";
        
        $pdo->commit();
        
        echo "\n<span class='success'>✅✅✅ FIX COMPLETE! ✅✅✅</span>\n";
        echo "\nNow refresh: <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=7' style='color:#0ff;'>Laporan Stok</a></pre>";
        
        echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>ERROR: " . $e->getMessage() . "</span>";
    if (isset($pdo)) $pdo->rollback();
}
?>
</body>
</html>