<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

echo "Checking pembelian_details for purchase ID 4:\n";

$stmt = $pdo->prepare('SELECT * FROM pembelian_details WHERE pembelian_id = 4');
$stmt->execute();
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($details) . " details\n";

foreach ($details as $detail) {
    echo "Detail ID: {$detail['id']}\n";
    echo "  bahan_baku_id: " . ($detail['bahan_baku_id'] ?? 'NULL') . "\n";
    echo "  bahan_pendukung_id: " . ($detail['bahan_pendukung_id'] ?? 'NULL') . "\n";
    echo "  jumlah: {$detail['jumlah']}\n";
    echo "---\n";
}

// Also check if there are any purchases with actual details
echo "\nChecking other purchases with details:\n";
$stmt2 = $pdo->prepare('
    SELECT p.id, p.tanggal, COUNT(pd.id) as detail_count,
           SUM(CASE WHEN pd.bahan_baku_id IS NOT NULL THEN 1 ELSE 0 END) as bahan_baku_count,
           SUM(CASE WHEN pd.bahan_pendukung_id IS NOT NULL THEN 1 ELSE 0 END) as bahan_pendukung_count
    FROM pembelians p 
    LEFT JOIN pembelian_details pd ON p.id = pd.pembelian_id 
    GROUP BY p.id 
    ORDER BY p.id DESC 
    LIMIT 10
');
$stmt2->execute();
$purchases = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($purchases as $purchase) {
    echo "Purchase {$purchase['id']}: {$purchase['detail_count']} details, {$purchase['bahan_baku_count']} bahan baku, {$purchase['bahan_pendukung_count']} bahan pendukung\n";
}