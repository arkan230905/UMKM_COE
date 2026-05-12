<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>FIX: Jurnal Umum - Pembayaran Beban</h1>";
echo "<pre>";

// Step 1: Delete all pembayaran beban entries from jurnal_umum for 28-29 April
echo "=== STEP 1: HAPUS JURNAL UMUM YANG SALAH ===\n\n";

$delete = $conn->query("
    DELETE FROM jurnal_umum
    WHERE tanggal IN ('2026-04-28', '2026-04-29')
    AND keterangan LIKE '%Pembayaran Beban%'
");

echo "Deleted: " . $conn->affected_rows . " rows from jurnal_umum\n\n";

// Step 2: Get correct data from expense_payments
echo "=== STEP 2: BUAT ULANG DENGAN DATA YANG BENAR ===\n\n";

$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY pb.id
");

$created = 0;
$errors = 0;

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $tanggal = $row['tanggal'];
    $beban_coa = $row['coa_beban_id'];
    $kas_coa = $row['coa_kasbank'];
    $nominal = $row['nominal_pembayaran'];
    $nama_beban = $row['nama_beban'];
    
    echo "Processing ID $id ($tanggal - $nama_beban):\n";
    echo "  COA Beban: $beban_coa\n";
    echo "  COA Kas: $kas_coa\n";
    echo "  Nominal: $nominal\n";
    
    // Get COA IDs
    $coa_beban_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$beban_coa'");
    $coa_kas_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$kas_coa'");
    
    if (!$coa_beban_result || $coa_beban_result->num_rows == 0) {
        echo "  ✗ Error: COA Beban $beban_coa not found\n";
        $errors++;
        continue;
    }
    
    if (!$coa_kas_result || $coa_kas_result->num_rows == 0) {
        echo "  ✗ Error: COA Kas $kas_coa not found\n";
        $errors++;
        continue;
    }
    
    $coa_beban_id = $coa_beban_result->fetch_assoc()['id'];
    $coa_kas_id = $coa_kas_result->fetch_assoc()['id'];
    
    // Insert debit entry
    $insert1 = $conn->query("
        INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
        VALUES ('$tanggal', $coa_beban_id, $nominal, 0, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
    ");
    
    if (!$insert1) {
        echo "  ✗ Error inserting debit: " . $conn->error . "\n";
        $errors++;
        continue;
    }
    
    echo "  ✓ Inserted debit entry\n";
    
    // Insert credit entry
    $insert2 = $conn->query("
        INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
        VALUES ('$tanggal', $coa_kas_id, 0, $nominal, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
    ");
    
    if (!$insert2) {
        echo "  ✗ Error inserting credit: " . $conn->error . "\n";
        $errors++;
        continue;
    }
    
    echo "  ✓ Inserted credit entry\n";
    $created++;
    echo "\n";
}

echo str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n";
echo "Created: $created\n";
echo "Errors: $errors\n";

if ($created > 0 && $errors == 0) {
    echo "\n✅ JURNAL UMUM PEMBAYARAN BEBAN BERHASIL DIPERBAIKI!\n";
    echo "Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
}

// Verify
echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFIKASI\n";
echo str_repeat("=", 100) . "\n\n";

$verify = $conn->query("
    SELECT ju.tanggal, ju.keterangan, c.kode_akun, c.nama_akun, ju.debit, ju.kredit
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal IN ('2026-04-28', '2026-04-29')
    AND ju.keterangan LIKE '%Pembayaran Beban%'
    ORDER BY ju.tanggal, ju.id
");

printf("%-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n", "Tanggal", "Keterangan", "COA", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 140) . "\n";

while ($row = $verify->fetch_assoc()) {
    printf("%-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n",
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        $row['debit'],
        $row['kredit']
    );
}

$conn->close();
echo "</pre>";
?>
