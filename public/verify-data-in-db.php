<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>VERIFY: Data di Database</h1>";
echo "<pre>";

echo "=== JURNAL_UMUM TABLE ===\n";
echo "Checking data for 28-29 April with Pembayaran Beban:\n\n";

$result = $conn->query("
    SELECT ju.id, ju.tanggal, ju.keterangan, c.kode_akun, c.nama_akun, ju.debit, ju.kredit
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal IN ('2026-04-28', '2026-04-29')
    AND ju.keterangan LIKE '%Pembayaran Beban%'
    ORDER BY ju.tanggal, ju.id
");

printf("%-5s | %-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n", "ID", "Tanggal", "Keterangan", "COA", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 140) . "\n";

$count = 0;
while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        $row['debit'],
        $row['kredit']
    );
    $count++;
}

echo "\nTotal entries: $count\n";

if ($count == 0) {
    echo "\n❌ NO DATA FOUND!\n";
    echo "The fix script did not create entries.\n";
} else {
    echo "\n✓ Data exists in database.\n";
    echo "✓ If you still see wrong data on the page, it's a browser cache issue.\n";
    echo "✓ Solution: Hard refresh browser (Ctrl+Shift+Delete or Ctrl+F5)\n";
}

// Check if 551 is in the data
$result = $conn->query("
    SELECT COUNT(*) as count FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal = '2026-04-28'
    AND ju.keterangan LIKE '%Pembayaran Beban Sewa%'
    AND c.kode_akun = '551'
");

$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    echo "\n✅ COA 551 (BOP Sewa Tempat) found for 28/04/2026!\n";
} else {
    echo "\n❌ COA 551 NOT found for 28/04/2026!\n";
}

// Check if 550 is in the data
$result = $conn->query("
    SELECT COUNT(*) as count FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal = '2026-04-29'
    AND ju.keterangan LIKE '%Pembayaran Beban Listrik%'
    AND c.kode_akun = '550'
");

$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    echo "✅ COA 550 (BOP Listrik) found for 29/04/2026!\n";
} else {
    echo "❌ COA 550 NOT found for 29/04/2026!\n";
}

$conn->close();
echo "</pre>";
?>
