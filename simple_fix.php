<?php
// Simple fix without Laravel bootstrap
$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Check current table structure
    $stmt = $pdo->query("DESCRIBE penggajians");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Add missing columns
    $columnsToAdd = [
        "ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS tarif_per_jam DECIMAL(15,2) DEFAULT 0 AFTER gaji_pokok",
        "ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS asuransi DECIMAL(15,2) DEFAULT 0 AFTER tunjangan", 
        "ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS bonus DECIMAL(15,2) DEFAULT 0 AFTER asuransi",
        "ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS coa_kasbank VARCHAR(10) AFTER tanggal_penggajian",
        "ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS status_pembayaran ENUM('belum_lunas', 'lunas', 'dibatalkan') DEFAULT 'belum_lunas' AFTER coa_kasbank"
    ];
    
    foreach ($columnsToAdd as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Executed: " . substr($sql, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "⚠️ Warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Database structure updated successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}