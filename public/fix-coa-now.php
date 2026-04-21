<?php
// Direct database fix for expense_payments COA

$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "<h1>FIXING EXPENSE PAYMENTS COA DATA</h1>";
echo "<pre>";

// Step 1: Show current data
echo "STEP 1: Current data in expense_payments\n";
echo str_repeat("=", 80) . "\n";

$result = $conn->query("
    SELECT id, tanggal, coa_beban_id, nominal_pembayaran 
    FROM expense_payments 
    WHERE id IN (2, 3)
    ORDER BY id
");

while ($row = $result->fetch_assoc()) {
    echo "ID {$row['id']}: {$row['tanggal']} | COA: {$row['coa_beban_id']} | Amount: {$row['nominal_pembayaran']}\n";
}

// Step 2: Update COA data
echo "\nSTEP 2: Updating COA data\n";
echo str_repeat("=", 80) . "\n";

$update1 = $conn->query("UPDATE expense_payments SET coa_beban_id = '551' WHERE id = 2");
echo "✓ ID 2: Updated to COA 551 (BOP Sewa Tempat)\n";

$update2 = $conn->query("UPDATE expense_payments SET coa_beban_id = '550' WHERE id = 3");
echo "✓ ID 3: Updated to COA 550 (BOP Listrik)\n";

// Step 3: Delete old journal entries
echo "\nSTEP 3: Deleting old journal entries\n";
echo str_repeat("=", 80) . "\n";

$delete_lines = $conn->query("
    DELETE jl FROM journal_lines jl
    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
    WHERE je.ref_type = 'expense_payment' AND je.ref_id IN (2, 3)
");
echo "✓ Deleted journal_lines\n";

$delete_entries = $conn->query("
    DELETE FROM journal_entries 
    WHERE ref_type = 'expense_payment' AND ref_id IN (2, 3)
");
echo "✓ Deleted journal_entries\n";

// Step 4: Create new journal entries
echo "\nSTEP 4: Creating new journal entries\n";
echo str_repeat("=", 80) . "\n";

// Get COA IDs
$coa_551 = $conn->query("SELECT id FROM coas WHERE kode_akun = '551'")->fetch_assoc()['id'];
$coa_550 = $conn->query("SELECT id FROM coas WHERE kode_akun = '550'")->fetch_assoc()['id'];
$coa_111 = $conn->query("SELECT id FROM coas WHERE kode_akun = '111'")->fetch_assoc()['id'];

// Create journal for ID 2 (Sewa - 551)
$je2 = $conn->query("
    INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
    VALUES ('2026-04-28', 'expense_payment', 2, 'Pembayaran Beban: Pembayaran Beban Sewa', NOW(), NOW())
");
$je2_id = $conn->insert_id;

$conn->query("
    INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
    VALUES ($je2_id, $coa_551, 1500000, 0, 'Pembayaran beban', NOW(), NOW())
");

$conn->query("
    INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
    VALUES ($je2_id, $coa_111, 0, 1500000, 'Pembayaran beban operasional', NOW(), NOW())
");

echo "✓ Created journal entry for ID 2 (Sewa - COA 551)\n";

// Create journal for ID 3 (Listrik - 550)
$je3 = $conn->query("
    INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
    VALUES ('2026-04-29', 'expense_payment', 3, 'Pembayaran Beban: Pembayaran Beban Listrik', NOW(), NOW())
");
$je3_id = $conn->insert_id;

$conn->query("
    INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
    VALUES ($je3_id, $coa_550, 2030000, 0, 'Pembayaran beban', NOW(), NOW())
");

$conn->query("
    INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
    VALUES ($je3_id, $coa_111, 0, 2030000, 'Pembayaran beban operasional', NOW(), NOW())
");

echo "✓ Created journal entry for ID 3 (Listrik - COA 550)\n";

// Step 5: Verify
echo "\nSTEP 5: Verification\n";
echo str_repeat("=", 80) . "\n";

$verify = $conn->query("
    SELECT je.tanggal, je.memo, c.kode_akun, c.nama_akun, jl.debit, jl.credit
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
    LEFT JOIN coas c ON c.id = jl.coa_id
    WHERE je.ref_type = 'expense_payment' AND je.ref_id IN (2, 3)
    ORDER BY je.id, jl.id
");

while ($row = $verify->fetch_assoc()) {
    echo "{$row['tanggal']} | {$row['kode_akun']} {$row['nama_akun']} | D: {$row['debit']} K: {$row['credit']}\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ SELESAI! Refresh halaman: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
echo str_repeat("=", 80) . "\n";

$conn->close();
echo "</pre>";
?>
