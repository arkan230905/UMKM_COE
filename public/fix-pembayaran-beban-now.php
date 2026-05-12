<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>🔧 FIX PEMBAYARAN BEBAN - JALANKAN SEKARANG</h1>";
echo "<pre>";

// STEP 1: Delete old entries
echo "STEP 1: Menghapus data lama dari jurnal_umum...\n";
$delete = $conn->query("
    DELETE FROM jurnal_umum
    WHERE tanggal IN ('2026-04-28', '2026-04-29')
    AND keterangan LIKE '%Pembayaran Beban%'
");
$deleted = $conn->affected_rows;
echo "✓ Dihapus: $deleted baris\n\n";

// STEP 2: Get correct data from expense_payments
echo "STEP 2: Membaca data yang benar dari expense_payments...\n";
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

// STEP 3: Create new entries
echo "STEP 3: Membuat ulang entries dengan data yang benar...\n\n";

$created = 0;
foreach ($payments as $p) {
    $id = $p['id'];
    $tanggal = $p['tanggal'];
    $beban_coa = $p['coa_beban_id'];
    $kas_coa = $p['coa_kasbank'];
    $nominal = $p['nominal_pembayaran'];
    $nama_beban = $p['nama_beban'];
    
    // Get COA IDs
    $coa_beban_id = $conn->query("SELECT id FROM coas WHERE kode_akun = '$beban_coa'")->fetch_assoc()['id'];
    $coa_kas_id = $conn->query("SELECT id FROM coas WHERE kode_akun = '$kas_coa'")->fetch_assoc()['id'];
    
    // Insert debit
    $conn->query("
        INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
        VALUES ('$tanggal', $coa_beban_id, $nominal, 0, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
    ");
    
    // Insert credit
    $conn->query("
        INSERT INTO jurnal_umum (tanggal, coa_id, debit, kredit, keterangan, referensi, tipe_referensi, created_at, updated_at)
        VALUES ('$tanggal', $coa_kas_id, 0, $nominal, 'Pembayaran Beban: $nama_beban', 'PB-$id', 'expense_payment', NOW(), NOW())
    ");
    
    echo "✓ ID $id ($tanggal): $beban_coa (Rp $nominal) → $kas_coa\n";
    $created++;
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "HASIL\n";
echo str_repeat("=", 100) . "\n";
echo "✓ Dihapus: $deleted baris lama\n";
echo "✓ Dibuat: " . ($created * 2) . " baris baru (" . $created . " transaksi)\n";

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
echo "✅ SELESAI! Refresh halaman Jurnal Umum untuk melihat perubahan.\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
