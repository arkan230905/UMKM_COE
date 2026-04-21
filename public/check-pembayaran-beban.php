<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>INVESTIGASI: Pembayaran Beban - Akun Salah</h1>";
echo "<pre>";

// Check pembayaran beban di database
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.keterangan, pb.jumlah, c.kode_akun, c.nama_akun
    FROM pembayaran_bebans pb
    LEFT JOIN coas c ON c.id = pb.coa_id
    WHERE pb.tanggal >= '2026-04-28'
    ORDER BY pb.tanggal, pb.id
");

echo "=== PEMBAYARAN BEBAN DI DATABASE ===\n";
printf("%-5s | %-12s | %-40s | %-10s | %-40s\n", "ID", "Tanggal", "Keterangan", "Jumlah", "COA");
echo str_repeat("-", 120) . "\n";

$pembayaranData = [];
while ($row = $result->fetch_assoc()) {
    $pembayaranData[] = $row;
    printf("%-5s | %-12s | %-40s | %-10s | %-40s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['jumlah'],
        $row['kode_akun'] . ' - ' . substr($row['nama_akun'], 0, 30)
    );
}

echo "\n" . str_repeat("=", 120) . "\n";
echo "=== JURNAL UMUM UNTUK PEMBAYARAN BEBAN ===\n";

$result = $conn->query("
    SELECT ju.id, ju.tanggal, ju.keterangan, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal >= '2026-04-28' AND ju.tanggal <= '2026-04-29'
    AND ju.keterangan LIKE '%Pembayaran Beban%'
    ORDER BY ju.tanggal, ju.id
");

printf("%-5s | %-12s | %-40s | %-12s | %-12s | %-40s\n", "ID", "Tanggal", "Keterangan", "Debit", "Kredit", "COA");
echo str_repeat("-", 140) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-40s | %-12s | %-12s | %-40s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['debit'],
        $row['kredit'],
        $row['kode_akun'] . ' - ' . substr($row['nama_akun'], 0, 30)
    );
}

echo "\n" . str_repeat("=", 120) . "\n";
echo "ANALISIS\n";
echo str_repeat("=", 120) . "\n\n";

echo "MASALAH:\n";
echo "1. Di halaman transaksi pembayaran beban ID 2:\n";
echo "   - Akun yang benar: 551 - BOP Sewa Tempat\n";
echo "   - Tapi di Jurnal Umum: 550 - BOP Listrik (SALAH!)\n\n";

echo "2. Di halaman transaksi pembayaran beban ID 3:\n";
echo "   - Akun yang benar: 550 - BOP Listrik\n";
echo "   - Tapi di Jurnal Umum: 550 - BOP Listrik (BENAR)\n\n";

echo "KEMUNGKINAN PENYEBAB:\n";
echo "- Data di pembayaran_bebans table benar\n";
echo "- Tapi saat membuat jurnal_umum, akun yang digunakan salah\n";
echo "- Atau ada bug di controller saat membuat journal entry\n\n";

// Check the controller code
echo "PERLU CEK:\n";
echo "1. app/Http/Controllers/PembayaranBebanController.php\n";
echo "2. Bagian yang membuat journal entry untuk pembayaran beban\n";
echo "3. Apakah menggunakan coa_id yang benar dari pembayaran_bebans?\n";

$conn->close();
echo "</pre>";
?>
