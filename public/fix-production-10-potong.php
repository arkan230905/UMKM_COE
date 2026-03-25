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
    <title>Fix Production 10 Potong</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        .success { color: #0f0; font-weight: bold; }
        .error { color: #f00; font-weight: bold; }
        pre { background: #111; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
<h1>🔧 FIX PRODUCTION TO USE 10 POTONG</h1>
<pre><?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();
    
    echo "FIXING PRODUCTION TO USE 10 POTONG...\n\n";
    
    // Konversi: 10 Potong = 1.6667 Ekor (karena 1 Ekor = 6 Potong)
    $potong = 10;
    $ekor = $potong / 6; // 1.6667 Ekor
    $hargaPerEkor = 45000;
    $hargaPerPotong = $hargaPerEkor / 6; // 7500
    $totalCost = $potong * $hargaPerPotong; // 75000
    
    echo "CALCULATION:\n";
    echo "Production uses: 10 Potong\n";
    echo "Converted to Ekor: $ekor Ekor (10 ÷ 6)\n";
    echo "Price per Ekor: Rp " . number_format($hargaPerEkor, 2) . "\n";
    echo "Price per Potong: Rp " . number_format($hargaPerPotong, 2) . "\n";
    echo "Total Cost: Rp " . number_format($totalCost, 2) . "\n\n";
    
    // Update stock movement for production
    echo "Updating stock_movements...\n";
    $stmt = $pdo->prepare("UPDATE stock_movements SET qty = ?, unit_cost = ?, total_cost = ? WHERE item_type = 'material' AND item_id = 2 AND ref_type = 'production' AND ref_id = 1");
    $stmt->execute([$ekor, $hargaPerEkor, $totalCost]);
    echo "<span class='success'>✓ Updated production stock movement to $ekor Ekor</span>\n\n";
    
    // Update stock layer: 30 - 1.6667 = 28.3333 Ekor
    $remainingEkor = 30 - $ekor;
    echo "Updating stock_layers...\n";
    $stmt = $pdo->prepare("UPDATE stock_layers SET remaining_qty = ? WHERE item_type = 'material' AND item_id = 2");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>✓ Updated stock layer to $remainingEkor Ekor remaining</span>\n\n";
    
    // Update master data
    echo "Updating bahan_bakus...\n";
    $stmt = $pdo->prepare("UPDATE bahan_bakus SET stok = ? WHERE id = 2");
    $stmt->execute([$remainingEkor]);
    echo "<span class='success'>✓ Updated master stock to $remainingEkor Ekor</span>\n\n";
    
    // Verify
    echo "VERIFICATION:\n";
    $stmt = $pdo->query("SELECT qty, satuan, unit_cost, total_cost FROM stock_movements WHERE item_type = 'material' AND item_id = 2 AND ref_type = 'production'");
    $movement = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Stock Movement: {$movement['qty']} {$movement['satuan']} @ Rp " . number_format($movement['unit_cost'], 2) . " = Rp " . number_format($movement['total_cost'], 2) . "\n";
    
    $stmt = $pdo->query("SELECT remaining_qty FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $layer = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Stock Layer: {$layer['remaining_qty']} Ekor remaining\n";
    
    $stmt = $pdo->query("SELECT stok FROM bahan_bakus WHERE id = 2");
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Master Stock: {$master['stok']} Ekor\n\n";
    
    echo "EXPECTED DISPLAY IN EACH UNIT:\n";
    echo "- Ekor: 30 - 1.6667 = 28.3333 Ekor\n";
    echo "- Potong: 180 - 10 = 170 Potong (30×6 - 10)\n";
    echo "- Kilogram: 45 - 2.5 = 42.5 Kg (30×1.5 - 1.6667×1.5)\n";
    echo "- Gram: 45,000 - 2,500 = 42,500 Gram\n\n";
    
    $pdo->commit();
    
    echo "<span class='success'>✅ SUCCESS! Production now uses 10 Potong (1.6667 Ekor)</span>\n";
    echo "\nRefresh: <a href='http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2&satuan_id=7' style='color: #0ff;'>Laporan Stok (Potong)</a>\n";
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}
?></pre>
</body>
</html>