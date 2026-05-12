<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>🔧 FINAL FIX: Pembayaran Beban - Lengkap</h1>";
echo "<pre>";

// STEP 1: Delete old data from jurnal_umum
echo "STEP 1: Menghapus data lama dari jurnal_umum...\n";
$delete_ju = $conn->query("
    DELETE FROM jurnal_umum
    WHERE tanggal IN ('2026-04-28', '2026-04-29')
    AND keterangan LIKE '%Pembayaran Beban%'
");
$deleted_ju = $conn->affected_rows;
echo "✓ Dihapus dari jurnal_umum: $deleted_ju baris\n\n";

// STEP 2: Delete old journal_entries
echo "STEP 2: Menghapus journal_entries lama...\n";
$delete_je = $conn->query("
    DELETE FROM journal_entries
    WHERE ref_type = 'expense_payment'
    AND tanggal IN ('2026-04-28', '2026-04-29')
");
$deleted_je = $conn->affected_rows;
echo "✓ Dihapus dari journal_entries: $deleted_je baris\n\n";

// STEP 3: Get expense_payments data
echo "STEP 3: Membaca data dari expense_payments...\n";
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

// STEP 4: Create new journal_entries and jurnal_umum
echo "STEP 4: Membuat journal_entries dan jurnal_umum baru...\n\n";

$created_je = 0;
$created_ju = 0;

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
        echo "  ✓ Created journal_lines\n";
        $created_je++;
    }
    
    // Create jurnal_umum entries
    $insert_ju1 = $conn->query("
        INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
        VALUES ('$tanggal', $coa_beban_id, $nominal, 0, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
    ");
    
    $insert_ju2 = $conn->query("
        INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
        VALUES ('$tanggal', $coa_kas_id, 0, $nominal, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
    ");
    
    if ($insert_ju1 && $insert_ju2) {
        echo "  ✓ Created jurnal_umum entries\n";
        $created_ju += 2;
    }
    
    echo "\n";
}

echo str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n";
echo "Dihapus dari jurnal_umum: $deleted_ju baris\n";
echo "Dihapus dari journal_entries: $deleted_je baris\n";
echo "Dibuat journal_entries: $created_je transaksi\n";
echo "Dibuat jurnal_umum: $created_ju baris\n";

echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFIKASI\n";
echo str_repeat("=", 100) . "\n\n";

// Verify journal_entries
echo "JOURNAL_ENTRIES:\n";
$verify_je = $conn->query("
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

while ($row = $verify_je->fetch_assoc()) {
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

// Verify jurnal_umum
echo "\n\nJURNAL_UMUM:\n";
$verify_ju = $conn->query("
    SELECT ju.tanggal, ju.keterangan, c.kode_akun, c.nama_akun, ju.debit, ju.kredit
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal IN ('2026-04-28', '2026-04-29')
    AND ju.keterangan LIKE '%Pembayaran Beban%'
    ORDER BY ju.tanggal, ju.id
");

printf("%-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n", "Tanggal", "Keterangan", "COA", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 140) . "\n";

while ($row = $verify_ju->fetch_assoc()) {
    printf("%-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n",
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        $row['debit'],
        $row['kredit']
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "✅ SELESAI! Refresh halaman Jurnal Umum untuk melihat perubahan.\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
