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
    
    // Cek apakah tabel migrations sudah ada
    $result = $pdo->query("SHOW TABLES LIKE 'migrations'");
    
    if ($result->rowCount() > 0) {
        echo "Tabel 'migrations' sudah ada. Menghapus...\n";
        $pdo->exec("DROP TABLE migrations");
    }
    
    // Buat tabel migrations
    echo "Membuat tabel 'migrations'...\n";
    $pdo->exec("CREATE TABLE migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL
    )");
    
    echo "Tabel 'migrations' berhasil dibuat.\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
