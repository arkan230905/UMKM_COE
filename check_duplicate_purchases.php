<?php

$pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');

echo "=== CHECKING DUPLICATE PURCHASES ===\n\n";

// Check for duplicate purchases by nomor_pembelian
echo "1. DUPLICATE PURCHASE NUMBERS:\n";
$stmt = $pdo->query("
    SELECT nomor_pembelian, COUNT(*) as count, GROUP_CONCAT(id) as ids
    FROM pembelians 
    GROUP BY nomor_pembelian 
    HAVING COUNT(*) > 1
    ORDER BY nomor_pembelian
");
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($duplicates as $dup) {
    echo "- {$dup['nomor_pembelian']}: {$dup['count']} entries (IDs: {$dup['ids']})\n";
}

if (empty($duplicates)) {
    echo "✅ No duplicate purchase numbers found\n";
}

// Check for test purchases that should be cleaned up
echo "\n2. TEST PURCHASES TO CLEAN UP:\n";
$stmt2 = $pdo->query("
    SELECT id, nomor_pembelian, tanggal, total_harga
    FROM pembelians 
    WHERE nomor_pembelian LIKE '%TEST%' 
    OR nomor_pembelian LIKE '%BP-%'
    ORDER BY id DESC
");
$testPurchases = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($testPurchases as $test) {
    echo "- ID {$test['id']}: {$test['nomor_pembelian']} ({$test['tanggal']}) - Rp " . number_format($test['total_harga']) . "\n";
}

// Check for purchases on the same date with similar amounts
echo "\n3. POTENTIALLY DUPLICATE PURCHASES (same date, similar amounts):\n";
$stmt3 = $pdo->query("
    SELECT p1.id as id1, p1.nomor_pembelian as nomor1, p1.total_harga as total1,
           p2.id as id2, p2.nomor_pembelian as nomor2, p2.total_harga as total2,
           p1.tanggal
    FROM pembelians p1
    JOIN pembelians p2 ON p1.tanggal = p2.tanggal 
                       AND p1.total_harga = p2.total_harga 
                       AND p1.id < p2.id
    WHERE p1.tanggal = '2026-04-09'
    ORDER BY p1.tanggal, p1.total_harga
");
$similarPurchases = $stmt3->fetchAll(PDO::FETCH_ASSOC);

foreach ($similarPurchases as $similar) {
    echo "- {$similar['nomor1']} (ID {$similar['id1']}) vs {$similar['nomor2']} (ID {$similar['id2']}) - Both Rp " . number_format($similar['total1']) . "\n";
}

// Check journal entries for these purchases
echo "\n4. JOURNAL ENTRIES FOR TODAY'S PURCHASES:\n";
$stmt4 = $pdo->query("
    SELECT p.id, p.nomor_pembelian, p.total_harga,
           COUNT(je.id) as journal_count,
           GROUP_CONCAT(je.id) as journal_ids
    FROM pembelians p
    LEFT JOIN journal_entries je ON je.ref_type = 'purchase' AND je.ref_id = p.id
    WHERE p.tanggal = '2026-04-09'
    GROUP BY p.id
    ORDER BY p.id
");
$purchaseJournals = $stmt4->fetchAll(PDO::FETCH_ASSOC);

foreach ($purchaseJournals as $pj) {
    $journalInfo = $pj['journal_count'] > 0 ? "Journals: {$pj['journal_ids']}" : "No journal";
    echo "- Purchase {$pj['id']} ({$pj['nomor_pembelian']}): {$journalInfo}\n";
}

echo "\n5. RECOMMENDATIONS:\n";
if (!empty($testPurchases)) {
    echo "- Delete test purchases (those with TEST or BP in the name)\n";
}
if (!empty($duplicates)) {
    echo "- Review and merge duplicate purchase numbers\n";
}
if (!empty($similarPurchases)) {
    echo "- Check if similar purchases are actual duplicates\n";
}
echo "- Keep only legitimate business purchases\n";