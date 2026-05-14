<?php

// Connect to database and check bahan_pendukungs table
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', '');

echo "=== Checking bahan_pendukungs table ===\n";

// Check table structure
$stmt = $pdo->prepare("DESCRIBE bahan_pendukungs");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Table structure:\n";
foreach ($columns as $column) {
    echo "  - {$column['Field']}: {$column['Type']}\n";
}

// Check for COA 530 usage
$coaKode = '530';
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bahan_pendukungs WHERE (coa_persediaan_id = ? OR coa_hpp_id = ? OR coa_pembelian_id = ?) AND user_id = ?");
$stmt->execute([$coaKode, $coaKode, $coaKode, 7]);
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

echo "\nCOA 530 usage in bahan_pendukungs: {$count}\n";

if ($count > 0) {
    echo "Records found:\n";
    $stmt = $pdo->prepare("SELECT id, nama, coa_persediaan_id, coa_hpp_id, coa_pembelian_id FROM bahan_pendukungs WHERE (coa_persediaan_id = ? OR coa_hpp_id = ? OR coa_pembelian_id = ?) AND user_id = ? LIMIT 5");
    $stmt->execute([$coaKode, $coaKode, $coaKode, 7]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($records as $record) {
        echo "  ID: {$record['id']} - {$record['nama']}\n";
        echo "    coa_persediaan_id: " . ($record['coa_persediaan_id'] ?? 'NULL') . "\n";
        echo "    coa_hpp_id: " . ($record['coa_hpp_id'] ?? 'NULL') . "\n";
        echo "    coa_pembelian_id: " . ($record['coa_pembelian_id'] ?? 'NULL') . "\n";
    }
}
