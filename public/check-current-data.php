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
    <title>Check Current Data</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        .error { color: #f00; font-weight: bold; }
        .success { color: #0f0; font-weight: bold; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #0f0; padding: 8px; }
        th { background: #003300; }
    </style>
</head>
<body>
<h1>🔍 CURRENT AYAM KAMPUNG DATA</h1>
<?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>1. CONVERSION RATIOS</h2>";
    $stmt = $pdo->query("
        SELECT 
            bb.sub_satuan_1_konversi as potong_conv,
            bb.sub_satuan_2_konversi as kg_conv,
            bb.sub_satuan_3_konversi as gram_conv
        FROM bahan_bakus bb
        WHERE bb.id = 2
    ");
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Satuan</th><th>Konversi</th><th>Status</th></tr>";
    echo "<tr><td>Potong</td><td>{$conv['potong_conv']}</td><td>" . ($conv['potong_conv'] == 6 ? "<span class='success'>✓ CORRECT</span>" : "<span class='error'>✗ WRONG (should be 6)</span>") . "</td></tr>";
    echo "<tr><td>Kilogram</td><td>{$conv['kg_conv']}</td><td>" . ($conv['kg_conv'] == 1.5 ? "<span class='success'>✓ CORRECT</span>" : "<span class='error'>✗ WRONG (should be 1.5)</span>") . "</td></tr>";
    echo "<tr><td>Gram</td><td>{$conv['gram_conv']}</td><td>" . ($conv['gram_conv'] == 1500 ? "<span class='success'>✓ CORRECT</span>" : "<span class='error'>✗ WRONG (should be 1500)</span>") . "</td></tr>";
    echo "</table>";
    
    echo "<h2>2. STOCK MOVEMENTS</h2>";
    $stmt = $pdo->query("SELECT * FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Tanggal</th><th>Direction</th><th>Qty</th><th>Satuan</th><th>Unit Cost</th><th>Total Cost</th><th>Ref Type</th></tr>";
    foreach ($movements as $m) {
        echo "<tr>";
        echo "<td>{$m['tanggal']}</td>";
        echo "<td>{$m['direction']}</td>";
        echo "<td>{$m['qty']}</td>";
        echo "<td>{$m['satuan']}</td>";
        echo "<td>" . number_format($m['unit_cost'], 2) . "</td>";
        echo "<td>" . number_format($m['total_cost'], 2) . "</td>";
        echo "<td>{$m['ref_type']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. EXPECTED vs ACTUAL</h2>";
    echo "<table>";
    echo "<tr><th>Item</th><th>Expected</th><th>Actual</th><th>Status</th></tr>";
    
    // Check production qty
    $prodQty = 0;
    foreach ($movements as $m) {
        if ($m['ref_type'] == 'production') {
            $prodQty = $m['qty'];
        }
    }
    $expectedProdQty = 1.6667;
    echo "<tr><td>Production Qty</td><td>$expectedProdQty Ekor</td><td>$prodQty Ekor</td><td>" . (abs($prodQty - $expectedProdQty) < 0.01 ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td></tr>";
    
    // Check production cost
    $prodCost = 0;
    foreach ($movements as $m) {
        if ($m['ref_type'] == 'production') {
            $prodCost = $m['total_cost'];
        }
    }
    $expectedProdCost = 75000;
    echo "<tr><td>Production Cost</td><td>Rp $expectedProdCost</td><td>Rp $prodCost</td><td>" . (abs($prodCost - $expectedProdCost) < 100 ? "<span class='success'>✓</span>" : "<span class='error'>✗</span>") . "</td></tr>";
    
    echo "</table>";
    
    if ($conv['potong_conv'] != 6 || $conv['kg_conv'] != 1.5 || $conv['gram_conv'] != 1500 || abs($prodQty - $expectedProdQty) > 0.01) {
        echo "<h2><span class='error'>❌ DATA IS WRONG!</span></h2>";
        echo "<p>Please run: <a href='fix-all-ayam-kampung-final.php' style='color: #0ff;'>fix-all-ayam-kampung-final.php</a></p>";
    } else {
        echo "<h2><span class='success'>✅ DATA IS CORRECT!</span></h2>";
        echo "<p>If display is still wrong, clear cache:</p>";
        echo "<pre>php artisan cache:clear\nphp artisan view:clear\nphp artisan config:clear</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>