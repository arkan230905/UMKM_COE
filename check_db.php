<?php
// Check nomor_penjualan column
$pdo = new PDO('mysql:host=127.0.0.1;dbname=umkm_coe', 'root', '');

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM penjualans LIKE '%nomor%'");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns containing 'nomor':\n";
    foreach ($result as $row) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    // Check if nomor_penjualan exists
    $stmt = $pdo->query("SHOW COLUMNS FROM penjualans WHERE Field = 'nomor_penjualan'");
    $hasColumn = $stmt->rowCount() > 0;
    
    echo "\nHas 'nomor_penjualan' column: " . ($hasColumn ? 'YES' : 'NO') . "\n";
    
    if ($hasColumn) {
        // Get some sample data
        $stmt = $pdo->query("SELECT id, nomor_penjualan, tanggal FROM penjualans LIMIT 3");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nSample data:\n";
        foreach ($data as $row) {
            echo "ID: {$row['id']}, Nomor: " . ($row['nomor_penjualan'] ?? 'NULL') . ", Tanggal: {$row['tanggal']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
