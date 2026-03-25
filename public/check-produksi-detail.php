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
    <title>Check Produksi Detail</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #0f0; padding: 8px; text-align: left; }
        th { background: #003300; }
        .error { color: #f00; font-weight: bold; }
    </style>
</head>
<body>
<h1>🔍 CHECKING PRODUKSI DETAIL FOR AYAM KAMPUNG</h1>
<?php
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>1. PRODUKSI RECORDS</h2>";
    $stmt = $pdo->query("SELECT * FROM produksis WHERE id = 1");
    $produksi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produksi) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Produk ID</th><th>Tanggal</th><th>Qty Produksi</th><th>Status</th></tr>";
        echo "<tr>";
        echo "<td>{$produksi['id']}</td>";
        echo "<td>{$produksi['produk_id']}</td>";
        echo "<td>{$produksi['tanggal']}</td>";
        echo "<td>{$produksi['qty_produksi']}</td>";
        echo "<td>{$produksi['status']}</td>";
        echo "</tr>";
        echo "</table>";
    }
    
    echo "<h2>2. PRODUKSI DETAILS (Bahan yang digunakan)</h2>";
    $stmt = $pdo->query("SELECT * FROM produksi_details WHERE produksi_id = 1 AND bahan_baku_id = 2");
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($details) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Bahan Baku ID</th><th>Qty Resep</th><th>Satuan Resep</th><th>Qty Konversi</th><th>Satuan</th><th>Harga Satuan</th><th>Subtotal</th></tr>";
        foreach ($details as $d) {
            echo "<tr>";
            echo "<td>{$d['id']}</td>";
            echo "<td>{$d['bahan_baku_id']}</td>";
            echo "<td>{$d['qty_resep']}</td>";
            echo "<td>{$d['satuan_resep']}</td>";
            echo "<td>{$d['qty_konversi']}</td>";
            echo "<td>{$d['satuan']}</td>";
            echo "<td>" . number_format($d['harga_satuan'], 2) . "</td>";
            echo "<td>" . number_format($d['subtotal'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>ANALYSIS:</h3>";
        echo "<pre>";
        foreach ($details as $d) {
            echo "Qty Resep: {$d['qty_resep']} {$d['satuan_resep']}\n";
            echo "Qty Konversi: {$d['qty_konversi']} {$d['satuan']}\n";
            
            if ($d['satuan_resep'] == 'Potong') {
                echo "✓ Produksi menggunakan POTONG\n";
                echo "  Seharusnya stock movement juga dalam POTONG\n";
            } elseif ($d['satuan_resep'] == 'Ekor') {
                echo "✓ Produksi menggunakan EKOR\n";
                echo "  Seharusnya stock movement juga dalam EKOR\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>NO DETAILS FOUND!</p>";
    }
    
    echo "<h2>3. STOCK MOVEMENTS FOR PRODUCTION #1</h2>";
    $stmt = $pdo->query("SELECT * FROM stock_movements WHERE item_type = 'material' AND item_id = 2 AND ref_type = 'production' AND ref_id = 1");
    $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($movements) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Tanggal</th><th>Direction</th><th>Qty</th><th>Satuan</th><th>Unit Cost</th><th>Total Cost</th></tr>";
        foreach ($movements as $m) {
            echo "<tr>";
            echo "<td>{$m['id']}</td>";
            echo "<td>{$m['tanggal']}</td>";
            echo "<td>{$m['direction']}</td>";
            echo "<td>{$m['qty']}</td>";
            echo "<td>{$m['satuan']}</td>";
            echo "<td>" . number_format($m['unit_cost'], 2) . "</td>";
            echo "<td>" . number_format($m['total_cost'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>PROBLEM CHECK:</h3>";
        echo "<pre>";
        foreach ($movements as $m) {
            echo "Stock Movement: {$m['qty']} {$m['satuan']}\n";
        }
        
        if (count($details) > 0 && count($movements) > 0) {
            $detail = $details[0];
            $movement = $movements[0];
            
            echo "\nCOMPARISON:\n";
            echo "Produksi Detail: {$detail['qty_resep']} {$detail['satuan_resep']}\n";
            echo "Stock Movement:  {$movement['qty']} {$movement['satuan']}\n";
            
            if ($detail['satuan_resep'] != $movement['satuan']) {
                echo "\n<span class='error'>❌ MISMATCH! Satuan tidak sama!</span>\n";
                echo "Produksi menggunakan: {$detail['satuan_resep']}\n";
                echo "Stock movement pakai: {$movement['satuan']}\n";
            } else {
                echo "\n✓ Satuan sudah sama\n";
            }
            
            if ($detail['qty_resep'] != $movement['qty']) {
                echo "\n<span class='error'>❌ MISMATCH! Qty tidak sama!</span>\n";
                echo "Produksi menggunakan: {$detail['qty_resep']}\n";
                echo "Stock movement: {$movement['qty']}\n";
            } else {
                echo "\n✓ Qty sudah sama\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>NO STOCK MOVEMENTS FOUND!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>ERROR: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>