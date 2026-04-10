<?php

$pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');

echo "=== DEBUGGING JOURNAL DISPLAY ISSUE ===\n\n";

// Check all journal entries
echo "1. ALL JOURNAL ENTRIES:\n";
$stmt = $pdo->query('SELECT * FROM journal_entries ORDER BY id DESC LIMIT 10');
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($entries as $entry) {
    echo "ID: {$entry['id']}, Date: {$entry['tanggal']}, Ref: {$entry['ref_type']} #{$entry['ref_id']}, Memo: {$entry['memo']}\n";
}

if (empty($entries)) {
    echo "❌ No journal entries found!\n";
} else {
    echo "✅ Found " . count($entries) . " journal entries\n";
}

echo "\n2. JOURNAL LINES FOR LATEST ENTRIES:\n";
foreach (array_slice($entries, 0, 3) as $entry) {
    echo "\nJournal ID {$entry['id']} lines:\n";
    
    $stmt2 = $pdo->prepare('
        SELECT jl.*, c.kode_akun, c.nama_akun
        FROM journal_lines jl
        LEFT JOIN coas c ON jl.coa_id = c.id
        WHERE jl.journal_entry_id = ?
        ORDER BY jl.id
    ');
    $stmt2->execute([$entry['id']]);
    $lines = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($lines as $line) {
        $debit = number_format($line['debit']);
        $credit = number_format($line['credit']);
        echo "  - {$line['nama_akun']} ({$line['kode_akun']}) | Debit: {$debit} | Credit: {$credit}\n";
    }
}

// Check the date range from the URL
echo "\n3. CHECKING DATE FILTER:\n";
echo "URL shows: from=2026-04-09&to=2026-04-09\n";

$stmt3 = $pdo->prepare('
    SELECT COUNT(*) as count 
    FROM journal_entries 
    WHERE tanggal BETWEEN ? AND ?
');
$stmt3->execute(['2026-04-09', '2026-04-09']);
$dateCount = $stmt3->fetch(PDO::FETCH_ASSOC)['count'];

echo "Journals in date range 2026-04-09: {$dateCount}\n";

// Check if there are journals for today's date
$today = date('Y-m-d');
$stmt4 = $pdo->prepare('
    SELECT COUNT(*) as count 
    FROM journal_entries 
    WHERE tanggal = ?
');
$stmt4->execute([$today]);
$todayCount = $stmt4->fetch(PDO::FETCH_ASSOC)['count'];

echo "Journals for today ({$today}): {$todayCount}\n";

// Check the ref_type filter
echo "\n4. CHECKING REF_TYPE FILTER:\n";
echo "URL shows: ref_type=purchase\n";

$stmt5 = $pdo->prepare('
    SELECT COUNT(*) as count 
    FROM journal_entries 
    WHERE ref_type = ? AND tanggal BETWEEN ? AND ?
');
$stmt5->execute(['purchase', '2026-04-09', '2026-04-09']);
$refTypeCount = $stmt5->fetch(PDO::FETCH_ASSOC)['count'];

echo "Purchase journals on 2026-04-09: {$refTypeCount}\n";

// Show all ref_types available
$stmt6 = $pdo->query('SELECT DISTINCT ref_type, COUNT(*) as count FROM journal_entries GROUP BY ref_type');
$refTypes = $stmt6->fetchAll(PDO::FETCH_ASSOC);

echo "\nAll ref_types in database:\n";
foreach ($refTypes as $refType) {
    echo "- {$refType['ref_type']}: {$refType['count']} entries\n";
}

echo "\n5. SAMPLE QUERY THAT SHOULD MATCH THE REPORT:\n";
$stmt7 = $pdo->prepare('
    SELECT je.*, jl.coa_id, jl.debit, jl.credit, jl.memo as line_memo, c.kode_akun, c.nama_akun
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
    LEFT JOIN coas c ON jl.coa_id = c.id
    WHERE je.ref_type = ? AND je.tanggal BETWEEN ? AND ?
    ORDER BY je.tanggal DESC, je.id DESC
    LIMIT 10
');
$stmt7->execute(['purchase', '2026-04-09', '2026-04-09']);
$reportData = $stmt7->fetchAll(PDO::FETCH_ASSOC);

echo "Query results: " . count($reportData) . " rows\n";
foreach ($reportData as $row) {
    echo "- Journal {$row['id']}: {$row['nama_akun']} | Debit: {$row['debit']} | Credit: {$row['credit']}\n";
}