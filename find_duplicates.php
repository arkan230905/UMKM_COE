<?php
$host = '127.0.0.1';
$db = 'eadt_umkm';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query untuk cari duplikasi
    $sql = "
    SELECT 
        je1.id as entry1_id,
        je2.id as entry2_id,
        je1.entry_date,
        je1.description,
        je1.created_at as created1,
        je2.created_at as created2
    FROM journal_entries je1
    JOIN journal_entries je2 ON 
        DATE(je1.entry_date) = DATE(je2.entry_date) AND
        je1.description = je2.description AND
        je1.id < je2.id
    WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
    ";
    
    $stmt = $pdo->query($sql);
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== DUPLIKASI DITEMUKAN ===\n";
    echo "Total: " . count($duplicates) . "\n\n";
    
    foreach ($duplicates as $dup) {
        echo "Entry " . $dup['entry1_id'] . " dan Entry " . $dup['entry2_id'] . "\n";
        echo "  Tanggal: " . $dup['entry_date'] . "\n";
        echo "  Deskripsi: " . $dup['description'] . "\n";
        echo "  Created: " . $dup['created1'] . " vs " . $dup['created2'] . "\n";
        echo "  → Hapus Entry " . $dup['entry2_id'] . " (yang lebih baru)\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
