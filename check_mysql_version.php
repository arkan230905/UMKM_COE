<?php
// Konfigurasi koneksi database
$host = '127.0.0.1';
$username = 'root';
$password = '';

try {
    // Buat koneksi PDO
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Dapatkan versi MySQL
    $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "Versi MySQL: $version\n";
    
    // Daftar database yang ada
    echo "\nDaftar database:\n";
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($databases as $db) {
        echo "- $db\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
