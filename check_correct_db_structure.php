<?php

$pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');

echo "=== JOURNAL_LINES TABLE STRUCTURE (eadt_umkm) ===\n";

$stmt = $pdo->query('DESCRIBE journal_lines');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $column) {
    echo "Column: {$column['Field']} ({$column['Type']})\n";
}

echo "\n=== SAMPLE DATA ===\n";
$stmt2 = $pdo->query('SELECT * FROM journal_lines LIMIT 3');
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "No data in journal_lines table\n";
} else {
    foreach ($rows as $row) {
        echo "Row: " . json_encode($row) . "\n";
    }
}

// Check if there are any bahan pendukung purchases
echo "\n=== BAHAN PENDUKUNG PURCHASES ===\n";
$stmt3 = $pdo->prepare('
    SELECT p.id, p.tanggal, COUNT(pd.id) as detail_count
    FROM pembelians p
    LEFT JOIN pembelian_details pd ON p.id = pd.pembelian_id
    WHERE pd.bahan_pendukung_id IS NOT NULL
    GROUP BY p.id
    ORDER BY p.id DESC
    LIMIT 5
');
$stmt3->execute();
$purchases = $stmt3->fetchAll(PDO::FETCH_ASSOC);

if (empty($purchases)) {
    echo "No purchases with bahan pendukung found\n";
} else {
    foreach ($purchases as $purchase) {
        echo "Purchase {$purchase['id']}: {$purchase['tanggal']} - {$purchase['detail_count']} bahan pendukung items\n";
    }
}