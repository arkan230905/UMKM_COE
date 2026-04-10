<?php

$pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');

echo "=== TESTING CONTROLLER QUERY ===\n\n";

// Simulate the exact query from the controller
$from = '2026-04-09';
$to = '2026-04-09';
$refType = 'purchase';

echo "Parameters: from={$from}, to={$to}, ref_type={$refType}\n\n";

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
    ORDER BY je.tanggal ASC, je.id ASC, jl.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$from, $to, $refType]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Query returned " . count($results) . " rows\n\n";

if (empty($results)) {
    echo "❌ No results returned!\n";
    
    // Test without the debit/credit filter
    echo "\nTesting without debit/credit filter:\n";
    $sql2 = "
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
        WHERE DATE(je.tanggal) >= ?
        AND DATE(je.tanggal) <= ?
        AND je.ref_type = ?
        ORDER BY je.tanggal ASC, je.id ASC, jl.id ASC
    ";
    
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([$from, $to, $refType]);
    $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Without filter: " . count($results2) . " rows\n";
    
    foreach (array_slice($results2, 0, 5) as $row) {
        echo "- Journal {$row['id']}: {$row['nama_akun']} | Debit: {$row['debit']} | Credit: {$row['credit']}\n";
    }
    
} else {
    echo "✅ Results found!\n";
    
    // Group by journal entry like the controller does
    $grouped = [];
    foreach ($results as $row) {
        $grouped[$row['id']][] = $row;
    }
    
    echo "Grouped into " . count($grouped) . " journal entries:\n\n";
    
    foreach ($grouped as $entryId => $lines) {
        $firstLine = $lines[0];
        echo "Journal {$entryId} ({$firstLine['tanggal']}):\n";
        echo "  Memo: {$firstLine['memo']}\n";
        echo "  Lines: " . count($lines) . "\n";
        
        foreach ($lines as $line) {
            $debit = number_format($line['debit']);
            $credit = number_format($line['credit']);
            echo "    - {$line['nama_akun']} | Debit: {$debit} | Credit: {$credit}\n";
        }
        echo "\n";
    }
}

// Check if there are any null values in journal_lines
echo "\n=== CHECKING FOR NULL VALUES ===\n";
$stmt3 = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN debit IS NULL THEN 1 END) as null_debit,
        COUNT(CASE WHEN credit IS NULL THEN 1 END) as null_credit,
        COUNT(CASE WHEN coa_id IS NULL THEN 1 END) as null_coa_id
    FROM journal_lines jl
    JOIN journal_entries je ON jl.journal_entry_id = je.id
    WHERE je.ref_type = 'purchase'
");
$nullCheck = $stmt3->fetch(PDO::FETCH_ASSOC);

echo "Total purchase journal lines: {$nullCheck['total']}\n";
echo "NULL debit values: {$nullCheck['null_debit']}\n";
echo "NULL credit values: {$nullCheck['null_credit']}\n";
echo "NULL coa_id values: {$nullCheck['null_coa_id']}\n";