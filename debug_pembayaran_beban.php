<?php

echo "=== DEBUG PEMBAYARAN BEBAN ===\n";

// Connect to database
$host = 'localhost';
$dbname = 'your_database_name'; // Ganti dengan nama database yang benar
$username = 'your_username';     // Ganti dengan username yang benar
$password = 'your_password';     // Ganti dengan password yang benar

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully\n\n";
    
    // Check total pembayaran beban
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pembayaran_beban");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total Pembayaran Beban: " . $result['total'] . "\n";
    
    // Check pembayaran beban by user_id
    $stmt = $pdo->query("SELECT user_id, COUNT(*) as count FROM pembayaran_beban GROUP BY user_id");
    echo "\nPembayaran Beban by User ID:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "User ID {$row['user_id']}: {$row['count']} records\n";
    }
    
    // Show sample data
    $stmt = $pdo->query("SELECT id, user_id, tanggal, jumlah, keterangan FROM pembayaran_beban LIMIT 5");
    echo "\nSample Pembayaran Beban Data:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, User: {$row['user_id']}, Tanggal: {$row['tanggal']}, Jumlah: {$row['jumlah']}, Keterangan: {$row['keterangan']}\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database credentials in this script\n";
}

echo "\n=== DEBUG COMPLETED ===\n";
