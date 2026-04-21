<?php
// Koneksi database
$host = '127.0.0.1';
$db = 'eadt_umkm';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

echo "========================================\n";
echo "ANALISIS DUPLIKASI JOURNAL ENTRIES\n";
echo "Pembayaran Beban: 28/04/2026 - 29/04/2026\n";
echo "========================================\n\n";

// Query entries pembayaran beban
$sql = "
SELECT 
    je.id,
    je.entry_date,
    je.ref_type,
    je.description,
    je.created_at,
    COUNT(jl.id) as line_count,
    SUM(jl.debit) as total_debit,
    SUM(jl.credit) as total_credit
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je.id
ORDER BY je.entry_date, je.id
";

$stmt = $pdo->query($sql);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total entries ditemukan: " . count($entries) . "\n\n";

foreach ($entries as $entry) {
    echo "Entry ID: {$entry['id']}\n";
    echo "  Tanggal: {$entry['entry_date']}\n";
    echo "  Ref Type: {$entry['ref_type']}\n";
    echo "  Deskripsi: {$entry['description']}\n";
    echo "  Created: {$entry['created_at']}\n";
    echo "  Lines: {$entry['line_count']}\n";
    echo "  Total Debit: " . number_format($entry['total_debit'], 2) . "\n";
    echo "  Total Credit: " . number_format($entry['total_credit'], 2) . "\n";
    
    // Detail lines
    $lineSql = "
    SELECT 
        jl.id,
        jl.account_id,
        jl.debit,
        jl.credit
    FROM journal_lines jl
    WHERE jl.journal_entry_id = ?
    ORDER BY jl.id
    ";
    
    $lineStmt = $pdo->prepare($lineSql);
    $lineStmt->execute([$entry['id']]);
    $lines = $lineStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($lines)) {
        echo "  Detail Lines:\n";
        foreach ($lines as $line) {
            echo "    - Account {$line['account_id']}: Debit=" . number_format($line['debit'], 2) . 
                 ", Credit=" . number_format($line['credit'], 2) . "\n";
        }
    }
    echo "\n";
}

// Cek duplikasi
echo "\nANALISIS DUPLIKASI:\n";
echo "-----------------------------------\n";

$dupSql = "
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

$stmt = $pdo->query($dupSql);
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicates)) {
    echo "Tidak ada duplikasi berdasarkan tanggal dan deskripsi yang sama.\n";
} else {
    echo "Duplikasi ditemukan: " . count($duplicates) . "\n\n";
    foreach ($duplicates as $dup) {
        echo "  Entry {$dup['entry1_id']} dan {$dup['entry2_id']}\n";
        echo "    Tanggal: {$dup['entry_date']}\n";
        echo "    Deskripsi: {$dup['description']}\n";
        echo "    Created: {$dup['created1']} vs {$dup['created2']}\n\n";
    }
}

echo "\nANALISIS SELESAI\n";
echo "========================================\n";
