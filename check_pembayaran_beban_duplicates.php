<?php
/**
 * Script sederhana untuk cek duplikasi pembayaran beban
 * Bisa dijalankan langsung: php check_pembayaran_beban_duplicates.php
 * Atau akses via browser: http://localhost/check_pembayaran_beban_duplicates.php
 */

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

echo "<pre style='font-family: monospace; background: #f5f5f5; padding: 20px;'>\n";
echo "========================================\n";
echo "ANALISIS DUPLIKASI JOURNAL ENTRIES\n";
echo "Pembayaran Beban: 28/04/2026 - 29/04/2026\n";
echo "========================================\n\n";

// 1. Struktur tabel
echo "1. STRUKTUR TABEL JOURNAL_ENTRIES:\n";
echo "-----------------------------------\n";
$stmt = $pdo->query("DESCRIBE journal_entries");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'NO' ? '[NOT NULL]' : '[NULL]') . "\n";
}

echo "\n2. STRUKTUR TABEL JOURNAL_LINES:\n";
echo "-----------------------------------\n";
$stmt = $pdo->query("DESCRIBE journal_lines");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'NO' ? '[NOT NULL]' : '[NULL]') . "\n";
}

// 2. Query entries pembayaran beban
echo "\n3. JOURNAL ENTRIES PEMBAYARAN BEBAN (28-29 April 2026):\n";
echo "-----------------------------------\n";

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

// 3. Cek duplikasi berdasarkan tanggal, deskripsi, dan nominal
echo "4. ANALISIS DUPLIKASI (Tanggal + Deskripsi + Nominal):\n";
echo "-----------------------------------\n";

$dupSql = "
SELECT 
    je1.id as entry1_id,
    je2.id as entry2_id,
    je1.entry_date,
    je1.description,
    je1.created_at as created1,
    je2.created_at as created2,
    SUM(jl1.debit) as total_debit,
    SUM(jl1.credit) as total_credit
FROM journal_entries je1
JOIN journal_entries je2 ON 
    DATE(je1.entry_date) = DATE(je2.entry_date) AND
    je1.description = je2.description AND
    je1.id < je2.id
LEFT JOIN journal_lines jl1 ON je1.id = jl1.journal_entry_id
LEFT JOIN journal_lines jl2 ON je2.id = jl2.journal_entry_id
WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je1.id, je2.id
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
        echo "    Created: {$dup['created1']} vs {$dup['created2']}\n";
        echo "    Nominal: Debit=" . number_format($dup['total_debit'], 2) . 
             ", Credit=" . number_format($dup['total_credit'], 2) . "\n\n";
    }
}

// 4. Cek entries dengan nominal sama per akun
echo "5. ENTRIES DENGAN NOMINAL SAMA PER AKUN:\n";
echo "-----------------------------------\n";

$sameSql = "
SELECT 
    je1.id as entry1_id,
    je2.id as entry2_id,
    je1.entry_date,
    jl1.account_id,
    jl1.debit,
    jl1.credit
FROM journal_entries je1
JOIN journal_lines jl1 ON je1.id = jl1.journal_entry_id
JOIN journal_entries je2 ON DATE(je1.entry_date) = DATE(je2.entry_date) AND je1.id < je2.id
JOIN journal_lines jl2 ON je2.id = jl2.journal_entry_id
WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
  AND jl1.account_id = jl2.account_id
  AND jl1.debit = jl2.debit
  AND jl1.credit = jl2.credit
GROUP BY je1.id, je2.id, jl1.account_id
";

$stmt = $pdo->query($sameSql);
$sameAmount = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($sameAmount)) {
    echo "Tidak ada entries dengan nominal dan akun yang sama.\n";
} else {
    echo "Entries dengan nominal dan akun yang sama: " . count($sameAmount) . "\n\n";
    foreach ($sameAmount as $item) {
        echo "  Entry {$item['entry1_id']} dan {$item['entry2_id']}\n";
        echo "    Tanggal: {$item['entry_date']}\n";
        echo "    Account: {$item['account_id']}\n";
        echo "    Debit: " . number_format($item['debit'], 2) . ", Credit: " . number_format($item['credit'], 2) . "\n\n";
    }
}

// 5. Summary
echo "6. SUMMARY:\n";
echo "-----------------------------------\n";

$countSql = "SELECT COUNT(*) as total FROM journal_entries WHERE DATE(entry_date) BETWEEN '2026-04-28' AND '2026-04-29'";
$stmt = $pdo->query($countSql);
$totalEntries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$lineSql = "
SELECT COUNT(*) as total FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
";
$stmt = $pdo->query($lineSql);
$totalLines = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

echo "Total Journal Entries (28-29 April): {$totalEntries}\n";
echo "Total Journal Lines: {$totalLines}\n";
echo "Duplikasi terdeteksi: " . count($duplicates) . "\n";
echo "Entries dengan nominal sama: " . count($sameAmount) . "\n";

echo "\n========================================\n";
echo "ANALISIS SELESAI\n";
echo "========================================\n";
echo "</pre>\n";
