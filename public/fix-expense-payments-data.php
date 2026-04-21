<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>FIX: Expense Payments Data</h1>";
echo "<pre>";

echo "STEP 1: Check current data in expense_payments\n";
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.coa_beban_id, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.id IN (2, 3)
    ORDER BY pb.id
");

printf("%-3s | %-12s | %-40s | %-10s\n", "ID", "Tanggal", "Beban", "COA");
echo str_repeat("-", 70) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-3s | %-12s | %-40s | %-10s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['nama_beban'], 0, 40),
        $row['coa_beban_id']
    );
}

echo "\n\nSTEP 2: Fix the data\n";

// ID 2: Pembayaran Beban Sewa - should be 551
echo "Updating ID 2 (Sewa) to COA 551...\n";
$update1 = $conn->query("UPDATE expense_payments SET coa_beban_id = '551' WHERE id = 2");
if ($update1) {
    echo "✓ Updated\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// ID 3: Pembayaran Beban Listrik - should be 550
echo "Updating ID 3 (Listrik) to COA 550...\n";
$update2 = $conn->query("UPDATE expense_payments SET coa_beban_id = '550' WHERE id = 3");
if ($update2) {
    echo "✓ Updated\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

echo "\n\nSTEP 3: Delete old journal_entries\n";
$delete = $conn->query("
    DELETE FROM journal_entries
    WHERE ref_type = 'expense_payment'
    AND ref_id IN (2, 3)
");
echo "✓ Deleted: " . $conn->affected_rows . " entries\n";

echo "\n\nSTEP 4: Create new journal_entries from updated expense_payments\n";

$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.id IN (2, 3)
    ORDER BY pb.id
");

$created = 0;
while ($p = $result->fetch_assoc()) {
    $id = $p['id'];
    $tanggal = $p['tanggal'];
    $beban_coa = $p['coa_beban_id'];
    $kas_coa = $p['coa_kasbank'];
    $nominal = $p['nominal_pembayaran'];
    $nama_beban = $p['nama_beban'];
    
    echo "Processing ID $id ($nama_beban): COA $beban_coa\n";
    
    // Get COA IDs
    $coa_beban_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$beban_coa'");
    $coa_kas_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$kas_coa'");
    
    if (!$coa_beban_result || $coa_beban_result->num_rows == 0) {
        echo "  ✗ COA $beban_coa not found\n";
        continue;
    }
    
    if (!$coa_kas_result || $coa_kas_result->num_rows == 0) {
        echo "  ✗ COA $kas_coa not found\n";
        continue;
    }
    
    $coa_beban_id = $coa_beban_result->fetch_assoc()['id'];
    $coa_kas_id = $coa_kas_result->fetch_assoc()['id'];
    
    // Create journal_entry
    $insert_je = $conn->query("
        INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
        VALUES ('$tanggal', 'expense_payment', $id, 'Pembayaran Beban: $nama_beban', NOW(), NOW())
    ");
    
    if (!$insert_je) {
        echo "  ✗ Error: " . $conn->error . "\n";
        continue;
    }
    
    $je_id = $conn->insert_id;
    
    // Create lines
    $conn->query("INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                 VALUES ($je_id, $coa_beban_id, $nominal, 0, 'Pembayaran beban', NOW(), NOW())");
    
    $conn->query("INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                 VALUES ($je_id, $coa_kas_id, 0, $nominal, 'Pembayaran beban operasional', NOW(), NOW())");
    
    echo "  ✓ Created journal_entry\n";
    $created++;
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFIKASI\n";
echo str_repeat("=", 100) . "\n\n";

$verify = $conn->query("
    SELECT je.id, je.tanggal, je.memo, jl.debit, jl.credit, c.kode_akun, c.nama_akun
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
    LEFT JOIN coas c ON c.id = jl.coa_id
    WHERE je.ref_type = 'expense_payment'
    AND je.ref_id IN (2, 3)
    ORDER BY je.id, jl.id
");

printf("%-5s | %-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n", "JE ID", "Tanggal", "Memo", "COA", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 140) . "\n";

while ($row = $verify->fetch_assoc()) {
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

echo "\n" . str_repeat("=", 100) . "\n";
echo "✅ SELESAI! Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
