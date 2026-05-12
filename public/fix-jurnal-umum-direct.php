<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>DIRECT FIX: Update jurnal_umum dengan COA yang benar</h1>";
echo "<pre>";

// Get the correct COA IDs
echo "STEP 1: Mendapatkan COA IDs yang benar...\n";

// For Pembayaran Beban Sewa (ID 2): COA 551 (BOP Sewa Tempat)
$coa_551 = $conn->query("SELECT id FROM coas WHERE kode_akun = '551'")->fetch_assoc()['id'];
echo "COA 551 (BOP Sewa Tempat): ID = $coa_551\n";

// For Pembayaran Beban Listrik (ID 3): COA 550 (BOP Listrik)
$coa_550 = $conn->query("SELECT id FROM coas WHERE kode_akun = '550'")->fetch_assoc()['id'];
echo "COA 550 (BOP Listrik): ID = $coa_550\n";

// For Kas Bank: COA 111
$coa_111 = $conn->query("SELECT id FROM coas WHERE kode_akun = '111'")->fetch_assoc()['id'];
echo "COA 111 (Kas Bank): ID = $coa_111\n\n";

// STEP 2: Delete old entries for 28-29 April
echo "STEP 2: Menghapus entries lama untuk 28-29 April...\n";
$delete = $conn->query("
    DELETE FROM jurnal_umum
    WHERE tanggal IN ('2026-04-28', '2026-04-29')
    AND keterangan LIKE '%Pembayaran Beban%'
");
echo "✓ Dihapus: " . $conn->affected_rows . " baris\n\n";

// STEP 3: Create new entries with correct COAs
echo "STEP 3: Membuat entries baru dengan COA yang benar...\n\n";

// Pembayaran Beban Sewa (28/04/2026) - COA 551
echo "Creating entries for Pembayaran Beban Sewa (28/04/2026):\n";

$insert1 = $conn->query("
    INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
    VALUES ('2026-04-28', $coa_551, 1500000, 0, 'Pembayaran Beban: Pembayaran Beban Sewa', 'PB-2', 'expense_payment', NOW(), NOW())
");

$insert2 = $conn->query("
    INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
    VALUES ('2026-04-28', $coa_111, 0, 1500000, 'Pembayaran Beban: Pembayaran Beban Sewa', 'PB-2', 'expense_payment', NOW(), NOW())
");

if ($insert1 && $insert2) {
    echo "✓ Created entries for Sewa\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Pembayaran Beban Listrik (29/04/2026) - COA 550
echo "\nCreating entries for Pembayaran Beban Listrik (29/04/2026):\n";

$insert3 = $conn->query("
    INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
    VALUES ('2026-04-29', $coa_550, 2030000, 0, 'Pembayaran Beban: Pembayaran Beban Listrik', 'PB-3', 'expense_payment', NOW(), NOW())
");

$insert4 = $conn->query("
    INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
    VALUES ('2026-04-29', $coa_111, 0, 2030000, 'Pembayaran Beban: Pembayaran Beban Listrik', 'PB-3', 'expense_payment', NOW(), NOW())
");

if ($insert3 && $insert4) {
    echo "✓ Created entries for Listrik\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

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

echo "\n" . str_repeat("=", 100) . "\n";
echo "✅ SELESAI! Refresh halaman Jurnal Umum.\n";
echo "Harusnya sekarang menampilkan:\n";
echo "- 28/04/2026: 551 - BOP Sewa Tempat\n";
echo "- 29/04/2026: 550 - BOP Listrik\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
