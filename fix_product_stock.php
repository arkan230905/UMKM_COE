<?php

// Simple fix for product stock
echo "<h1>Fix Product #2 Stock</h1>";

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check current stock
    $stmt = $pdo->prepare("SELECT nama_produk, stok FROM produks WHERE id = 2");
    $stmt->execute();
    $produk = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($produk) {
        echo "<p>Product: {$produk->nama_produk}</p>";
        echo "<p>Current system stock: {$produk->stok}</p>";
        
        // Check stock layers
        $stmt = $pdo->prepare("SELECT SUM(remaining_qty) as total FROM stock_layers WHERE item_type = 'product' AND item_id = 2");
        $stmt->execute();
        $realtime = $stmt->fetch(PDO::FETCH_OBJ);
        
        echo "<p>Realtime stock: {$realtime->total}</p>";
        
        if ($produk->stok > 0 && $realtime->total == 0) {
            // Add stock layer
            $stmt = $pdo->prepare("
                INSERT INTO stock_layers (item_type, item_id, qty, remaining_qty, unit_cost, ref_type, ref_id, tanggal, created_at, updated_at) 
                VALUES ('product', 2, ?, ?, 50000, 'initial_stock', 0, NOW(), NOW(), NOW())
            ");
            $stmt->execute([$produk->stok, $produk->stok]);
            
            echo "<p style='color:green;'>✅ Stock layer created successfully!</p>";
            echo "<p>Added {$produk->stok} units to stock layer</p>";
        } else {
            echo "<p style='color:orange;'>ℹ️ No sync needed</p>";
        }
        
    } else {
        echo "<p style='color:red;'>❌ Product #2 not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='/transaksi/penjualan/create'>Try creating sale again</a></p>";
?>