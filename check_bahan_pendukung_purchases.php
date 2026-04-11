<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

echo "=== CHECKING BAHAN PENDUKUNG PURCHASES ===\n\n";

// Check for purchases with bahan pendukung details
$stmt = $pdo->prepare('
    SELECT p.id, p.tanggal, p.vendor_id, v.nama_vendor,
           COUNT(pd.id) as total_details,
           SUM(CASE WHEN pd.bahan_pendukung_id IS NOT NULL THEN 1 ELSE 0 END) as bahan_pendukung_count,
           SUM(CASE WHEN pd.bahan_baku_id IS NOT NULL THEN 1 ELSE 0 END) as bahan_baku_count
    FROM pembelians p
    LEFT JOIN vendors v ON p.vendor_id = v.id
    LEFT JOIN pembelian_details pd ON p.id = pd.pembelian_id
    GROUP BY p.id
    HAVING bahan_pendukung_count > 0
    ORDER BY p.id DESC
');
$stmt->execute();
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($purchases) . " purchases with bahan pendukung:\n\n";

foreach ($purchases as $purchase) {
    echo "Purchase ID: {$purchase['id']}\n";
    echo "Date: {$purchase['tanggal']}\n";
    echo "Vendor: {$purchase['nama_vendor']}\n";
    echo "Details: {$purchase['total_details']} total, {$purchase['bahan_pendukung_count']} bahan pendukung, {$purchase['bahan_baku_count']} bahan baku\n";
    
    // Check if journal exists
    $stmt2 = $pdo->prepare('SELECT COUNT(*) as count FROM journal_entries WHERE reference_type = ? AND reference_id = ?');
    $stmt2->execute(['purchase', $purchase['id']]);
    $journalCount = $stmt2->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "Journal entries: {$journalCount}\n";
    
    if ($journalCount == 0) {
        echo "❌ NO JOURNAL - needs to be created\n";
    } else {
        echo "✅ Journal exists\n";
    }
    echo "---\n";
}

// Check bahan pendukung COA mappings
echo "\n=== BAHAN PENDUKUNG COA MAPPINGS ===\n";
$stmt3 = $pdo->prepare('
    SELECT bp.id, bp.nama_bahan, bp.coa_persediaan_id, c.nama_akun
    FROM bahan_pendukungs bp
    LEFT JOIN coas c ON bp.coa_persediaan_id = c.kode_akun
    ORDER BY bp.nama_bahan
');
$stmt3->execute();
$bahanPendukungs = $stmt3->fetchAll(PDO::FETCH_ASSOC);

foreach ($bahanPendukungs as $bp) {
    echo "- {$bp['nama_bahan']}: ";
    if ($bp['coa_persediaan_id']) {
        if ($bp['nama_akun']) {
            echo "✅ COA {$bp['coa_persediaan_id']} ({$bp['nama_akun']})\n";
        } else {
            echo "❌ COA {$bp['coa_persediaan_id']} (NOT FOUND)\n";
        }
    } else {
        echo "❌ No COA mapping\n";
    }
}