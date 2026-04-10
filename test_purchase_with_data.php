<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

// Let's check purchase 3 which has bahan baku data
echo "Checking purchase 3 (which has bahan baku data):\n";

$stmt = $pdo->prepare('
    SELECT pd.*, bb.nama_bahan as bahan_baku_nama
    FROM pembelian_details pd
    LEFT JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
    WHERE pd.pembelian_id = 3
');
$stmt->execute();
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($details) . " details for purchase 3:\n";

$hasBahanBaku = false;
$hasBahanPendukung = false;

foreach ($details as $detail) {
    echo "Detail ID: {$detail['id']}\n";
    echo "  bahan_baku_id: " . ($detail['bahan_baku_id'] ?? 'NULL') . "\n";
    echo "  bahan_pendukung_id: " . ($detail['bahan_pendukung_id'] ?? 'NULL') . "\n";
    echo "  nama_bahan: " . ($detail['bahan_baku_nama'] ?? 'N/A') . "\n";
    echo "  jumlah: {$detail['jumlah']}\n";
    
    if ($detail['bahan_baku_id']) {
        $hasBahanBaku = true;
    }
    if ($detail['bahan_pendukung_id']) {
        $hasBahanPendukung = true;
    }
    echo "---\n";
}

// Determine category
if ($hasBahanBaku && !$hasBahanPendukung) {
    $kategori = 'bahan_baku';
} elseif ($hasBahanPendukung && !$hasBahanBaku) {
    $kategori = 'bahan_pendukung';
} else {
    $kategori = 'mixed';
}

echo "\nCategory for purchase 3: {$kategori}\n";

// Expected display
$showBahanBaku = ($kategori !== 'bahan_pendukung') ? 'block' : 'none';
$showBahanPendukung = ($kategori !== 'bahan_baku') ? 'block' : 'none';

echo "Expected display:\n";
echo "- Bahan Baku section: {$showBahanBaku}\n";
echo "- Bahan Pendukung section: {$showBahanPendukung}\n";

// If you edit purchase 3, it should only show Bahan Baku section