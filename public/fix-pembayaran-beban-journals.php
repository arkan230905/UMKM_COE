<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>FIX: Pembayaran Beban - Journal Entries</h1>";
echo "<pre>";

// Get all expense payments
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    ORDER BY pb.tanggal, pb.id
");

echo "=== PROCESSING EXPENSE PAYMENTS ===\n\n";

$fixed = 0;
$errors = 0;

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $tanggal = $row['tanggal'];
    $beban_coa = $row['coa_beban_id'];
    $kas_coa = $row['coa_kasbank'];
    $nominal = $row['nominal_pembayaran'];
    $nama_beban = $row['nama_beban'];
    
    echo "Processing ID $id ($tanggal - $nama_beban):\n";
    
    // Get COA IDs
    $coa_beban_id = $conn->query("SELECT id FROM coas WHERE kode_akun = '$beban_coa'")->fetch_assoc()['id'];
    $coa_kas_id = $conn->query("SELECT id FROM coas WHERE kode_akun = '$kas_coa'")->fetch_assoc()['id'];
    
    // Check existing journal entries in jurnal_umum
    $existing = $conn->query("
        SELECT ju.id, ju.coa_id, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
        FROM jurnal_umum ju
        LEFT JOIN coas c ON c.id = ju.coa_id
        WHERE ju.tanggal = '$tanggal'
        AND ju.keterangan LIKE '%Pembayaran Beban%'
        AND ju.keterangan LIKE '%$nama_beban%'
        ORDER BY ju.id
    ");
    
    $entries = [];
    while ($entry = $existing->fetch_assoc()) {
        $entries[] = $entry;
    }
    
    echo "  Found " . count($entries) . " existing entries\n";
    
    // Check if entries are correct
    $correct = true;
    foreach ($entries as $entry) {
        if ($entry['kode_akun'] != $beban_coa && $entry['kode_akun'] != $kas_coa) {
            echo "  ✗ Entry ID {$entry['id']}: Wrong COA {$entry['kode_akun']} (should be $beban_coa or $kas_coa)\n";
            $correct = false;
        }
    }
    
    if (!$correct) {
        echo "  Deleting incorrect entries...\n";
        
        // Delete incorrect entries
        $delete_result = $conn->query("
            DELETE FROM jurnal_umum
            WHERE tanggal = '$tanggal'
            AND keterangan LIKE '%Pembayaran Beban%'
            AND keterangan LIKE '%$nama_beban%'
        ");
        
        if ($delete_result) {
            echo "  ✓ Deleted " . $conn->affected_rows . " incorrect entries\n";
        } else {
            echo "  ✗ Error deleting entries: " . $conn->error . "\n";
            $errors++;
            continue;
        }
        
        // Create correct entries
        echo "  Creating correct entries...\n";
        
        // Debit entry
        $insert1 = $conn->query("
            INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
            VALUES ('$tanggal', $coa_beban_id, $nominal, 0, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
        ");
        
        // Credit entry
        $insert2 = $conn->query("
            INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
            VALUES ('$tanggal', $coa_kas_id, 0, $nominal, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
        ");
        
        if ($insert1 && $insert2) {
            echo "  ✓ Created correct entries\n";
            $fixed++;
        } else {
            echo "  ✗ Error creating entries: " . $conn->error . "\n";
            $errors++;
        }
    } else {
        echo "  ✓ Entries are correct\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n";
echo "Fixed: $fixed\n";
echo "Errors: $errors\n";

if ($fixed > 0) {
    echo "\n✅ Pembayaran Beban journals telah diperbaiki!\n";
    echo "Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
}

$conn->close();
echo "</pre>";
?>
