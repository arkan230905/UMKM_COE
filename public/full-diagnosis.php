<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>FULL DIAGNOSIS: Pembayaran Beban</h1>";
echo "<pre>";

// 1. Check expense_payments
echo "=== 1. EXPENSE_PAYMENTS TABLE ===\n";
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.beban_operasional_id, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.id IN (2, 3)
    ORDER BY pb.id
");

while ($row = $result->fetch_assoc()) {
    echo "ID {$row['id']}: {$row['tanggal']} - {$row['nama_beban']}\n";
    echo "  coa_beban_id: {$row['coa_beban_id']}\n";
    echo "  coa_kasbank: {$row['coa_kasbank']}\n";
    echo "  nominal: {$row['nominal_pembayaran']}\n\n";
}

// 2. Check journal_entries
echo "=== 2. JOURNAL_ENTRIES TABLE ===\n";
$result = $conn->query("
    SELECT je.id, je.tanggal, je.ref_type, je.ref_id, je.memo
    FROM journal_entries je
    WHERE je.ref_type = 'expense_payment'
    AND je.ref_id IN (2, 3)
    ORDER BY je.id
");

$je_count = $result->num_rows;
echo "Found: $je_count entries\n";

while ($row = $result->fetch_assoc()) {
    echo "ID {$row['id']}: {$row['tanggal']} - Ref ID {$row['ref_id']} - {$row['memo']}\n";
}

if ($je_count == 0) {
    echo "❌ NO JOURNAL_ENTRIES FOUND!\n";
}

echo "\n";

// 3. Check journal_lines
echo "=== 3. JOURNAL_LINES TABLE ===\n";
$result = $conn->query("
    SELECT jl.id, jl.journal_entry_id, c.kode_akun, c.nama_akun, jl.debit, jl.credit
    FROM journal_lines jl
    LEFT JOIN coas c ON c.id = jl.coa_id
    WHERE jl.journal_entry_id IN (
        SELECT je.id FROM journal_entries je
        WHERE je.ref_type = 'expense_payment'
        AND je.ref_id IN (2, 3)
    )
    ORDER BY jl.journal_entry_id, jl.id
");

$jl_count = $result->num_rows;
echo "Found: $jl_count lines\n";

while ($row = $result->fetch_assoc()) {
    echo "JE {$row['journal_entry_id']}: {$row['kode_akun']} - {$row['nama_akun']} - Debit {$row['debit']} Kredit {$row['credit']}\n";
}

echo "\n";

// 4. Check jurnal_umum
echo "=== 4. JURNAL_UMUM TABLE ===\n";
$result = $conn->query("
    SELECT ju.id, ju.tanggal, ju.keterangan, c.kode_akun, c.nama_akun, ju.debit, ju.kredit, ju.tipe_referensi
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal IN ('2026-04-28', '2026-04-29')
    AND ju.keterangan LIKE '%Pembayaran Beban%'
    ORDER BY ju.tanggal, ju.id
");

$ju_count = $result->num_rows;
echo "Found: $ju_count entries\n";

while ($row = $result->fetch_assoc()) {
    echo "ID {$row['id']}: {$row['tanggal']} - {$row['kode_akun']} - {$row['nama_akun']} - Debit {$row['debit']} Kredit {$row['kredit']} - Type: {$row['tipe_referensi']}\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "ANALYSIS\n";
echo str_repeat("=", 100) . "\n\n";

if ($je_count == 0) {
    echo "❌ PROBLEM: No journal_entries for expense_payment!\n";
    echo "   This means the model boot() method is NOT creating entries.\n";
    echo "   Solution: Need to manually create journal_entries from expense_payments.\n";
} else {
    echo "✓ Journal entries exist in database.\n";
    echo "✓ Problem is likely in the query that displays Jurnal Umum.\n";
}

if ($ju_count > 0) {
    echo "✓ Old data exists in jurnal_umum.\n";
    echo "✓ This old data is being displayed instead of journal_entries.\n";
}

$conn->close();
echo "</pre>";
?>
