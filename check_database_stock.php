<?php
// Simple script to check database stock values
// Run this outside of Laravel to avoid syntax errors

$host = 'localhost';
$dbname = 'your_database_name'; // Replace with your actual database name
$username = 'your_username';    // Replace with your actual username
$password = 'your_password';    // Replace with your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CURRENT BAHAN PENDUKUNG STOCK ===\n";
    
    // Check current stock values
    $stmt = $pdo->query("SELECT id, nama_bahan, stok, harga_satuan FROM bahan_pendukungs ORDER BY id");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "ID: {$row['id']}, Name: {$row['nama_bahan']}, Stock: {$row['stok']}, Price: {$row['harga_satuan']}\n";
    }
    
    echo "\n=== UPDATING STOCK TO 200 ===\n";
    
    // Update stock to 200
    $stmt = $pdo->prepare("UPDATE bahan_pendukungs SET stok = 200");
    $stmt->execute();
    $updated = $stmt->rowCount();
    
    echo "Updated $updated records\n";
    
    echo "\n=== AFTER UPDATE ===\n";
    
    // Check after update
    $stmt = $pdo->query("SELECT id, nama_bahan, stok, harga_satuan FROM bahan_pendukungs ORDER BY id");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "ID: {$row['id']}, Name: {$row['nama_bahan']}, Stock: {$row['stok']}, Price: {$row['harga_satuan']}\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPlease update the database credentials in this script:\n";
    echo "- host: $host\n";
    echo "- dbname: $dbname\n";
    echo "- username: $username\n";
    echo "- password: $password\n";
}
?>