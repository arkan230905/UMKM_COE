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
    
    // Cek apakah tabel migrations ada
    $result = $pdo->query("SHOW TABLES LIKE 'migrations'");
    
    if ($result->rowCount() > 0) {
        echo "Tabel 'migrations' sudah ada.\n";
        
        // Hitung jumlah migrasi
        $count = $pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
        echo "Jumlah entri di tabel migrations: $count\n";
        
        if ($count > 0) {
            $lastBatch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
            echo "Batch terakhir: $lastBatch\n";
        }
    } else {
        echo "Tabel 'migrations' belum ada.\n";
        
        // Coba buat tabel migrations
        try {
            $pdo->exec("CREATE TABLE migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL
            )");
            
            echo "Tabel 'migrations' berhasil dibuat.\n";
        } catch (PDOException $e) {
            echo "Gagal membuat tabel 'migrations': " . $e->getMessage() . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
