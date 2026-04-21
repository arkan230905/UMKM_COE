<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>CLEANUP: Hapus Duplikat Data</h1>";
echo "<pre>";

// Step 1: Delete from jurnal_umum for expense_payment
echo "STEP 1: Menghapus expense_payment dari jurnal_umum...\n";
$delete = $conn->query("
    DELETE FROM jurnal_umum
    WHERE tipe_referensi = 'expense_payment'
    AND tanggal IN ('2026-04-28', '2026-04-29')
");
$deleted = $conn->affected_rows;
echo "✓ Dihapus: $deleted baris\n\n";

// Step 2: Check journal_entries
echo "STEP 2: Verifikasi journal_entries...\n";
$result = $conn->query("
    SELECT je.id, je.tanggal, je.ref_type, je.ref_id, je.memo
    FROM journal_entries je
    WHERE je.ref_type = 'expense_payment'
    AND je.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY je.id
");

$je_count = $result->num_rows;
echo "Found: $je_count entries\n";

if ($je_count == 0) {
    echo "\n❌ PROBLEM: No journal_entries found!\n";
    echo "Need to create journal_entries from expense_payments.\n";
} else {
    echo "\n✓ Journal entries exist.\n";
    
    // Show the entries
    echo "\nJournal Entries:\n";
    $result = $conn->query("
        SELECT je.id, je.tanggal, je.memo, jl.debit, jl.credit, c.kode_akun, c.nama_akun
        FROM journal_entries je
        LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
        LEFT JOIN coas c ON c.id = jl.coa_id
        WHERE je.ref_type = 'expense_payment'
        AND je.tanggal IN ('2026-04-28', '2026-04-29')
        ORDER BY je.id, jl.id
    ");
    
    printf("%-5s | %-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n", "JE ID", "Tanggal", "Memo", "COA", "Nama Akun", "Debit", "Kredit");
    echo str_repeat("-", 140) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n",
            $row['id'],
            $row['tanggal'],
            substr($row['memo'], 0, 40),
            $row['kode_akun'],
            substr($row['nama_akun'], 0, 40),
            $row['debit'],
            $row['kredit']
        );
    }
}

// Step 3: Check jurnal_umum
echo "\n\nSTEP 3: Verifikasi jurnal_umum setelah cleanup...\n";
$result = $conn->query("
    SELECT COUNT(*) as count FROM jurnal_umum
    WHERE tipe_referensi = 'expense_payment'
    AND tanggal IN ('2026-04-28', '2026-04-29')
");

$ju_count = $result->fetch_assoc()['count'];
echo "Remaining expense_payment entries: $ju_count (should be 0)\n";

echo "\n" . str_repeat("=", 140) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 140) . "\n";
echo "✓ Deleted from jurnal_umum: $deleted baris\n";
echo "✓ Journal entries in database: $je_count\n";
echo "✓ Remaining duplicates: $ju_count\n";

if ($je_count > 0 && $ju_count == 0) {
    echo "\n✅ CLEANUP SUCCESSFUL!\n";
    echo "Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
} else if ($je_count == 0) {
    echo "\n❌ PROBLEM: No journal_entries found!\n";
    echo "Need to create journal_entries from expense_payments.\n";
}

$conn->close();
echo "</pre>";
?>
