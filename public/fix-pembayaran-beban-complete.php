<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>FIX LENGKAP: Pembayaran Beban - Hapus & Buat Ulang</h1>";
echo "<pre>";

// Step 1: Get all expense payments for 28-29 April
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.beban_operasional_id, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY pb.id
");

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo "=== STEP 1: HAPUS JOURNAL ENTRIES YANG SALAH ===\n\n";

// Delete from journal_entries
$delete1 = $conn->query("
    DELETE FROM journal_entries
    WHERE ref_type = 'expense_payment'
    AND tanggal IN ('2026-04-28', '2026-04-29')
");

echo "Deleted from journal_entries: " . $conn->affected_rows . " rows\n";

// Delete from journal_lines (cascade)
$delete2 = $conn->query("
    DELETE FROM journal_lines
    WHERE journal_entry_id NOT IN (SELECT id FROM journal_entries)
");

echo "Deleted from journal_lines: " . $conn->affected_rows . " rows\n";

// Delete from jurnal_umum
$delete3 = $conn->query("
    DELETE FROM jurnal_umum
    WHERE tanggal IN ('2026-04-28', '2026-04-29')
    AND keterangan LIKE '%Pembayaran Beban%'
");

echo "Deleted from jurnal_umum: " . $conn->affected_rows . " rows\n";

echo "\n" . str_repeat("=", 100) . "\n";
echo "=== STEP 2: BUAT ULANG JOURNAL ENTRIES YANG BENAR ===\n\n";

$created = 0;
$errors = 0;

foreach ($payments as $payment) {
    $id = $payment['id'];
    $tanggal = $payment['tanggal'];
    $beban_coa = $payment['coa_beban_id'];
    $kas_coa = $payment['coa_kasbank'];
    $nominal = $payment['nominal_pembayaran'];
    $nama_beban = $payment['nama_beban'];
    
    echo "Processing Payment ID $id ($tanggal - $nama_beban):\n";
    
    // Get COA IDs
    $coa_beban_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$beban_coa'");
    $coa_kas_result = $conn->query("SELECT id FROM coas WHERE kode_akun = '$kas_coa'");
    
    if (!$coa_beban_result || !$coa_kas_result) {
        echo "  ✗ Error: COA not found\n";
        $errors++;
        continue;
    }
    
    $coa_beban_id = $coa_beban_result->fetch_assoc()['id'];
    $coa_kas_id = $coa_kas_result->fetch_assoc()['id'];
    
    // Create journal entry
    $insert_je = $conn->query("
        INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
        VALUES ('$tanggal', 'expense_payment', $id, 'Pembayaran Beban: $nama_beban', NOW(), NOW())
    ");
    
    if (!$insert_je) {
        echo "  ✗ Error creating journal_entry: " . $conn->error . "\n";
        $errors++;
        continue;
    }
    
    $je_id = $conn->insert_id;
    echo "  ✓ Created journal_entry ID: $je_id\n";
    
    // Create debit line
    $insert_jl1 = $conn->query("
        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
        VALUES ($je_id, $coa_beban_id, $nominal, 0, 'Pembayaran beban', NOW(), NOW())
    ");
    
    if (!$insert_jl1) {
        echo "  ✗ Error creating debit line: " . $conn->error . "\n";
        $errors++;
        continue;
    }
    
    echo "  ✓ Created debit line: $beban_coa (Rp $nominal)\n";
    
    // Create credit line
    $insert_jl2 = $conn->query("
        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
        VALUES ($je_id, $coa_kas_id, 0, $nominal, 'Pembayaran beban operasional', NOW(), NOW())
    ");
    
    if (!$insert_jl2) {
        echo "  ✗ Error creating credit line: " . $conn->error . "\n";
        $errors++;
        continue;
    }
    
    echo "  ✓ Created credit line: $kas_coa (Rp $nominal)\n";
    
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
        $created++;
    } else {
        echo "  ✗ Error creating jurnal_umum entries: " . $conn->error . "\n";
        $errors++;
    }
    
    echo "\n";
}

echo str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n";
echo "Created: $created\n";
echo "Errors: $errors\n";

if ($created > 0 && $errors == 0) {
    echo "\n✅ PEMBAYARAN BEBAN BERHASIL DIPERBAIKI!\n";
    echo "Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
}

$conn->close();
echo "</pre>";
?>
