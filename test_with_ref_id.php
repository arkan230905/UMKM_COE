<?php

$pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');

echo "=== TESTING WITH REF_ID FILTER ===\n\n";

// Test with ref_id=6 (from the URL)
$from = '2026-04-09';
$to = '2026-04-09';
$refType = 'purchase';
$refId = 6;

echo "Parameters: from={$from}, to={$to}, ref_type={$refType}, ref_id={$refId}\n\n";

$sql = "
    SELECT 
        je.*,
        jl.id as line_id,
        jl.debit,
        jl.credit,
        jl.memo as line_memo,
        coas.kode_akun,
        coas.nama_akun,
        coas.tipe_akun
    FROM journal_entries as je
    LEFT JOIN journal_lines as jl ON jl.journal_entry_id = je.id
    LEFT JOIN coas ON coas.id = jl.coa_id
    WHERE (jl.debit != 0 OR jl.credit != 0)
    AND DATE(je.tanggal) >= ?
    AND DATE(je.tanggal) <= ?
    AND je.ref_type = ?
    AND je.ref_id = ?
    ORDER BY je.tanggal ASC, je.id ASC, jl.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$from, $to, $refType, $refId]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Query with ref_id=6 returned " . count($results) . " rows\n\n";

if (empty($results)) {
    echo "❌ No results for ref_id=6!\n\n";
    
    // Check what ref_ids exist for purchase journals
    echo "Available ref_ids for purchase journals on 2026-04-09:\n";
    $stmt2 = $pdo->prepare("
        SELECT DISTINCT je.ref_id, je.memo
        FROM journal_entries je
        WHERE je.ref_type = 'purchase'
        AND DATE(je.tanggal) = '2026-04-09'
        ORDER BY je.ref_id
    ");
    $stmt2->execute();
    $refIds = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($refIds as $ref) {
        echo "- ref_id {$ref['ref_id']}: {$ref['memo']}\n";
    }
    
    echo "\nThe URL is filtering for ref_id=6, but that purchase might not have a journal.\n";
    echo "Try removing the ref_id parameter from the URL or use one of the available ref_ids above.\n";
    
} else {
    echo "✅ Results found for ref_id=6!\n";
    foreach ($results as $row) {
        echo "- {$row['nama_akun']} | Debit: {$row['debit']} | Credit: {$row['credit']}\n";
    }
}

// Check if purchase #6 exists and has details
echo "\n=== CHECKING PURCHASE #6 ===\n";
$stmt3 = $pdo->prepare("
    SELECT p.*, COUNT(pd.id) as detail_count
    FROM pembelians p
    LEFT JOIN pembelian_details pd ON p.id = pd.pembelian_id
    WHERE p.id = 6
    GROUP BY p.id
");
$stmt3->execute();
$purchase6 = $stmt3->fetch(PDO::FETCH_ASSOC);

if ($purchase6) {
    echo "Purchase #6 exists:\n";
    echo "- Nomor: {$purchase6['nomor_pembelian']}\n";
    echo "- Tanggal: {$purchase6['tanggal']}\n";
    echo "- Total: Rp " . number_format($purchase6['total_harga']) . "\n";
    echo "- Details: {$purchase6['detail_count']}\n";
    
    if ($purchase6['detail_count'] == 0) {
        echo "❌ Purchase #6 has no details - that's why no journal was created!\n";
    }
} else {
    echo "❌ Purchase #6 does not exist!\n";
}