<?php
// Simple database check without Laravel
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CURRENT AYAM KAMPUNG STOCK DATA ===\n\n";
    
    // Check stock movements
    echo "Stock Movements:\n";
    $stmt = $pdo->query("SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Date: {$row['tanggal']}, Direction: {$row['direction']}, Qty: {$row['qty']}, Cost: {$row['unit_cost']}, Total: {$row['total_cost']}, Type: {$row['ref_type']}, Ref: {$row['ref_id']}\n";
    }
    
    echo "\nStock Layers:\n";
    $stmt = $pdo->query("SELECT id, remaining_qty, unit_cost, ref_type FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Remaining: {$row['remaining_qty']}, Cost: {$row['unit_cost']}, Type: {$row['ref_type']}\n";
    }
    
    echo "\nMaster Data:\n";
    $stmt = $pdo->query("SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Name: {$row['nama_bahan']}, Stock: {$row['stok']}\n";
    }
    
    // Calculate total from stock layers
    $stmt = $pdo->query("SELECT SUM(remaining_qty) as total_remaining FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal from stock_layers: " . ($total['total_remaining'] ?? 0) . "\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>