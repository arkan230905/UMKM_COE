<?php
// Konfigurasi koneksi database
$host = '127.0.0.1';
$dbname = 'eadt_umkm_lama';
$username = 'root';
$password = '';

try {
    // Buat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Koneksi ke database berhasil!\n";
    
    // Cek apakah database ada
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    echo "Database saat ini: $currentDb\n";
    
    // Cek tabel yang ada
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Jumlah tabel: " . count($tables) . "\n";
    
    if (count($tables) > 0) {
        echo "Daftar tabel:\n";
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Coba buat database jika belum ada
    if ($e->getCode() == 1049) { // Error: Unknown database
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Database '$dbname' berhasil dibuat.\n";
        } catch(PDOException $e) {
            echo "Gagal membuat database: " . $e->getMessage() . "\n";
        }
    }
}
