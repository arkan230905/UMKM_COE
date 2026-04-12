<?php

// Check stock movements vs stock layers for product #2
echo "<h1>Stock Analysis for Product #2</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check product info
    $stmt = $pdo->prepare("SELECT id, nama_produk, stok FROM produks WHERE id = 2");
    $stmt->execute();
    $produk = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<h2>Product Info</h2>";
    echo "<p>Name: {$produk->nama_produk}</p>";
    echo "<p>System Stock: {$produk->stok}</p>";
    
    // Check StockMovements
    echo "<h2>Stock Movements</h2>";
    $stmt = $pdo->prepare("
        SELECT tanggal, direction, qty, ref_type, ref_id, total_cost 
        FROM stock_movements 
        WHERE item_type = 'product' AND item_id = 2 
        ORDER BY tanggal DESC, id DESC
    ");
    $stmt->execute();
    $movements = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Total movements: " . count($movements) . "</p>";
    
    $totalIn = 0;
    $totalOut = 0;
    
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Date</th><th>Direction</th><th>Qty</th><th>Ref Type</th><th>Ref ID</th><th>Cost</th></tr>";
    
    foreach ($movements as $m) {
        echo "<tr>";
        echo "<td>{$m->tanggal}</td>";
        echo "<td>{$m->direction}</td>";
        echo "<td>{$m->qty}</td>";
        echo "<td>{$m->ref_type}</td>";
        echo "<td>{$m->ref_id}</td>";
        echo "<td>{$m->total_cost}</td>";
        echo "</tr>";
        
        if ($m->direction === 'in') {
            $totalIn += $m->qty;
        } else {
            $totalOut += $m->qty;
        }
    }
    echo "</table>";
    
    $calculatedStock = $totalIn - $totalOut;
    echo "<p><strong>Calculated Stock from Movements: {$calculatedStock}</strong></p>";
    
    // Check StockLayers
    echo "<h2>Stock Layers</h2>";
    $stmt = $pdo->prepare("
        SELECT qty, remaining_qty, unit_cost, ref_type, ref_id, tanggal 
        FROM stock_layers 
        WHERE item_type = 'product' AND item_id = 2 
        ORDER BY tanggal DESC, id DESC
    ");
    $stmt->execute();
    $layers = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Total layers: " . count($layers) . "</p>";
    
    $totalRemaining = 0;
    
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Date</th><th>Qty</th><th>Remaining</th><th>Cost</th><th>Ref Type</th><th>Ref ID</th></tr>";
    
    foreach ($layers as $l) {
        echo "<tr>";
        echo "<td>{$l->tanggal}</td>";
        echo "<td>{$l->qty}</td>";
        echo "<td>{$l->remaining_qty}</td>";
        echo "<td>{$l->unit_cost}</td>";
        echo "<td>{$l->ref_type}</td>";
        echo "<td>{$l->ref_id}</td>";
        echo "</tr>";
        
        $totalRemaining += $l->remaining_qty;
    }
    echo "</table>";
    
    echo "<p><strong>Total Remaining from Layers: {$totalRemaining}</strong></p>";
    
    // Solution
    if ($calculatedStock > 0 && $totalRemaining == 0) {
        echo "<h2>🔧 SOLUTION: Sync StockMovements to StockLayers</h2>";
        
        // Find the production movement that created the stock
        $productionMovement = null;
        foreach ($movements as $m) {
            if ($m->direction === 'in' && $m->ref_type === 'production') {
                $productionMovement = $m;
                break;
            }
        }
        
        if ($productionMovement) {
            echo "<p>Found production movement: {$productionMovement->qty} units from ref_id {$productionMovement->ref_id}</p>";
            
            // Create corresponding stock layer
            $stmt = $pdo->prepare("
                INSERT INTO stock_layers (item_type, item_id, qty, remaining_qty, unit_cost, ref_type, ref_id, tanggal, created_at, updated_at) 
                VALUES ('product', 2, ?, ?, ?, 'production', ?, ?, NOW(), NOW())
            ");
            
            $unitCost = $productionMovement->total_cost / $productionMovement->qty;
            $stmt->execute([
                $productionMovement->qty,
                $productionMovement->qty, 
                $unitCost,
                $productionMovement->ref_id,
                $productionMovement->tanggal
            ]);
            
            echo "<p style='color:green;'>✅ Created stock layer from production movement!</p>";
            echo "<p>Now StockService should see {$productionMovement->qty} units available</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='/transaksi/penjualan/create'>Try creating sale again</a></p>";
?>