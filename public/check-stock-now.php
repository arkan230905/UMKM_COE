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
    <title>Check Stock NOW</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #0f0; padding: 8px; text-align: left; }
        th { background: #003300; }
        .error { color: #f00; font-weight: bold; }
        .success { color: #0f0; font-weight: bold; }
    </style>
</head>
<body>
<h1>🔍 CHECKING AYAM KAMPUNG STOCK NOW</h1>
<?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>1. STOCK MOVEMENTS (item_id=2)</h2>";
    $stmt = $pdo->query("SELECT * FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal, id");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($movements) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Tanggal</th><th>Direction</th><th>Qty</th><th>Satuan</th><th>Unit Cost</th><th>Total Cost</th><th>Ref Type</th><th>Ref ID</th></tr>";
        foreach ($movements as $m) {
            echo "<tr>";
            echo "<td>{$m['id']}</td>";
            echo "<td>{$m['tanggal']}</td>";
            echo "<td>{$m['direction']}</td>";
            echo "<td>{$m['qty']}</td>";
            echo "<td>{$m['satuan']}</td>";
            echo "<td>" . number_format($m['unit_cost'], 2) . "</td>";
            echo "<td>" . number_format($m['total_cost'], 2) . "</td>";
            echo "<td>{$m['ref_type']}</td>";
            echo "<td>{$m['ref_id']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>NO MOVEMENTS FOUND!</p>";
    }
    
    echo "<h2>2. STOCK LAYERS (item_id=2)</h2>";
    $stmt = $pdo->query("SELECT * FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $layers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($layers) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Tanggal</th><th>Remaining Qty</th><th>Unit Cost</th><th>Satuan</th><th>Ref Type</th></tr>";
        $totalQty = 0;
        foreach ($layers as $l) {
            $totalQty += $l['remaining_qty'];
            echo "<tr>";
            echo "<td>{$l['id']}</td>";
            echo "<td>{$l['tanggal']}</td>";
            echo "<td>{$l['remaining_qty']}</td>";
            echo "<td>" . number_format($l['unit_cost'], 2) . "</td>";
            echo "<td>{$l['satuan']}</td>";
            echo "<td>{$l['ref_type']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p class='error'>TOTAL FROM LAYERS: $totalQty (THIS IS WHAT SHOWS AS 57!)</p>";
    } else {
        echo "<p class='error'>NO LAYERS FOUND!</p>";
    }
    
    echo "<h2>3. MASTER DATA (bahan_bakus id=2)</h2>";
    $stmt = $pdo->query("SELECT id, nama_bahan, stok, harga_satuan FROM bahan_bakus WHERE id = 2");
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($master) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nama</th><th>Stok</th><th>Harga Satuan</th></tr>";
        echo "<tr>";
        echo "<td>{$master['id']}</td>";
        echo "<td>{$master['nama_bahan']}</td>";
        echo "<td>{$master['stok']}</td>";
        echo "<td>" . number_format($master['harga_satuan'], 2) . "</td>";
        echo "</tr>";
        echo "</table>";
    }
    
    echo "<h2>4. CALCULATION CHECK</h2>";
    echo "<pre>";
    $runningQty = 0;
    foreach ($movements as $m) {
        if ($m['direction'] == 'in') {
            $runningQty += $m['qty'];
            echo "{$m['tanggal']} | IN  | +{$m['qty']} | Running: $runningQty | {$m['ref_type']}\n";
        } else {
            $runningQty -= $m['qty'];
            echo "{$m['tanggal']} | OUT | -{$m['qty']} | Running: $runningQty | {$m['ref_type']}\n";
        }
    }
    echo "\nFINAL CALCULATED: $runningQty\n";
    echo "LAYERS TOTAL: $totalQty\n";
    
    if ($runningQty == 28 && $totalQty == 28) {
        echo "\n<span class='success'>✅ DATA IS CORRECT!</span>\n";
    } else {
        echo "\n<span class='error'>❌ DATA IS WRONG!</span>\n";
        echo "Expected: 28\n";
        echo "Got from movements: $runningQty\n";
        echo "Got from layers: $totalQty\n";
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>