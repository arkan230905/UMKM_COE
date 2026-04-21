<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>DETAIL: Pembayaran Beban ID 2 & 3</h1>";
echo "<pre>";

// Check pembayaran beban ID 2 & 3
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.beban_operasional_id, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, pb.keterangan,
           bo.nama_beban, c1.kode_akun as beban_kode, c1.nama_akun as beban_nama, c2.kode_akun as kas_kode, c2.nama_akun as kas_nama
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    LEFT JOIN coas c1 ON c1.kode_akun = pb.coa_beban_id
    LEFT JOIN coas c2 ON c2.kode_akun = pb.coa_kasbank
    WHERE pb.id IN (2, 3)
    ORDER BY pb.id
");

echo "=== EXPENSE_PAYMENTS TABLE ===\n";
printf("%-3s | %-12s | %-40s | %-10s | %-40s | %-10s | %-40s\n", "ID", "Tanggal", "Beban", "Beban COA", "Beban Nama", "Kas COA", "Kas Nama");
echo str_repeat("-", 160) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-3s | %-12s | %-40s | %-10s | %-40s | %-10s | %-40s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['nama_beban'], 0, 40),
        $row['beban_kode'],
        substr($row['beban_nama'], 0, 40),
        $row['kas_kode'],
        substr($row['kas_nama'], 0, 40)
    );
}

echo "\n" . str_repeat("=", 160) . "\n";
echo "=== JURNAL UMUM UNTUK ID 2 & 3 ===\n";

$result = $conn->query("
    SELECT ju.id, ju.tanggal, ju.keterangan, ju.debit, ju.kredit, c.kode_akun, c.nama_akun
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal IN ('2026-04-28', '2026-04-29')
    AND ju.keterangan LIKE '%Pembayaran Beban%'
    ORDER BY ju.tanggal, ju.id
");

printf("%-5s | %-12s | %-40s | %-12s | %-12s | %-10s | %-40s\n", "ID", "Tanggal", "Keterangan", "Debit", "Kredit", "COA", "Nama Akun");
echo str_repeat("-", 140) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-40s | %-12s | %-12s | %-10s | %-40s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['debit'],
        $row['kredit'],
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40)
    );
}

echo "\n" . str_repeat("=", 160) . "\n";
echo "ANALISIS\n";
echo str_repeat("=", 160) . "\n\n";

echo "MASALAH:\n";
echo "1. Pembayaran Beban ID 2 (28/04/2026 - Sewa):\n";
echo "   - Di database: coa_beban_id = 551 (BOP Sewa Tempat) ✓\n";
echo "   - Di Jurnal Umum: 550 (BOP Listrik) ✗\n";
echo "   - KESIMPULAN: Ada 2 journal entries yang dibuat dengan akun berbeda!\n\n";

echo "PENYEBAB:\n";
echo "1. Di ExpensePaymentController::store():\n";
echo "   - Membuat journal entry dengan akun dari DROPDOWN\n";
echo "   - Jika user memilih akun yang salah, journal entry akan salah\n\n";

echo "2. Di ExpensePayment model boot():\n";
echo "   - Membuat journal entry LAGI dengan akun dari DATABASE\n";
echo "   - Ini menyebabkan DOUBLE ENTRY atau ENTRY YANG BERBEDA\n\n";

echo "SOLUSI:\n";
echo "1. Hapus journal entry creation di ExpensePaymentController::store()\n";
echo "2. Biarkan hanya JournalService::createJournalFromExpensePayment() yang membuat entry\n";
echo "3. Pastikan akun di database sudah benar sebelum menyimpan\n";

$conn->close();
echo "</pre>";
?>
