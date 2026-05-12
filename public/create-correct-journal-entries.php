<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>CREATE: Journal Entries dari Expense Payments</h1>";
echo "<pre>";

// Step 1: Delete old journal_entries for expense_payment
echo "STEP 1: Menghapus journal_entries lama...\n";
$delete = $conn->query("
    DELETE FROM journal_entries
    WHERE ref_type = 'expense_payment'
    AND tanggal IN ('2026-04-28', '2026-04-29')
");
$deleted = $conn->affected_rows;
echo "✓ Dihapus: $deleted entries\n\n";

// Step 2: Get expense_payments data
echo "STEP 2: Membaca expense_payments...\n";
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY pb.id
");

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}
echo "✓ Ditemukan: " . count($payments) . " pembayaran\n\n";

// Step 3: Create journal_entries
echo "STEP 3: Membuat journal_entries baru...\n\n";

$created = 0;
foreach ($payments as $p) {
    $id = $p['id'];
    $tanggal = $p['tanggal'];
    $beban_coa = $p['coa_beban_id'];
    $kas_coa = $p['coa_kasbank'];
    $nominal = $p['nominal_pembayaran'];
    $nama_beban = $p['nama_beban'];
    
    echo "Processing ID $id ($tanggal - $nama_beban):\n";
    
    // Get COA IDs
    $coa_beban_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$beban_coa'");
    $coa_kas_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$kas_coa'");
    
    if (!$coa_beban_result || $coa_beban_result->num_rows == 0) {
        echo "  ✗ Error: COA Beban $beban_coa not found\n";
        continue;
    }
    
    if (!$coa_kas_result || $coa_kas_result->num_rows == 0) {
        echo "  ✗ Error: COA Kas $kas_coa not found\n";
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
        echo "  ✗ Error creating journal_entry: " . $conn->error . "\n";
        continue;
    }
    
    $je_id = $conn->insert_id;
    echo "  ✓ Created journal_entry ID: $je_id\n";
    
    // Create debit line
    $insert_jl1 = $conn->query("
        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
        VALUES ($je_id, $coa_beban_id, $nominal, 0, 'Pembayaran beban', NOW(), NOW())
    ");
    
    // Create credit line
    $insert_jl2 = $conn->query("
        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
        VALUES ($je_id, $coa_kas_id, 0, $nominal, 'Pembayaran beban operasional', NOW(), NOW())
    ");
    
    if ($insert_jl1 && $insert_jl2) {
        echo "  ✓ Created journal_lines: $beban_coa (Rp $nominal) → $kas_coa\n";
        $created++;
    } else {
        echo "  ✗ Error creating journal_lines: " . $conn->error . "\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n";
echo "Created: $created journal_entries\n";

if ($created > 0) {
    echo "\n✅ JOURNAL ENTRIES CREATED!\n";
    echo "Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
}

// Verify
echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFIKASI\n";
echo str_repeat("=", 100) . "\n\n";

$verify = $conn->query("
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

$conn->close();
echo "</pre>";
?>
