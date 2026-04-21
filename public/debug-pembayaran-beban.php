<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>DEBUG: Pembayaran Beban - Data di Database</h1>";
echo "<pre>";

// Check expense_payments table
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.beban_operasional_id, pb.coa_beban_id, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY pb.id
");

echo "=== EXPENSE_PAYMENTS TABLE ===\n";
printf("%-3s | %-12s | %-40s | %-10s | %-12s\n", "ID", "Tanggal", "Beban", "COA", "Nominal");
echo str_repeat("-", 80) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-3s | %-12s | %-40s | %-10s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['nama_beban'], 0, 40),
        $row['coa_beban_id'],
        $row['nominal_pembayaran']
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "=== JURNAL_UMUM TABLE ===\n";

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

echo "\n" . str_repeat("=", 100) . "\n";
echo "=== JOURNAL_ENTRIES TABLE ===\n";

$result = $conn->query("
    SELECT je.id, je.tanggal, je.ref_type, je.ref_id, je.memo, COUNT(jl.id) as line_count
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
    WHERE je.ref_type = 'expense_payment'
    AND je.tanggal IN ('2026-04-28', '2026-04-29')
    GROUP BY je.id
    ORDER BY je.tanggal, je.id
");

printf("%-5s | %-12s | %-20s | %-10s | %-40s | %-12s\n", "ID", "Tanggal", "Ref Type", "Ref ID", "Memo", "Lines");
echo str_repeat("-", 100) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-20s | %-10s | %-40s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        $row['ref_type'],
        $row['ref_id'],
        substr($row['memo'], 0, 40),
        $row['line_count']
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "ANALISIS\n";
echo str_repeat("=", 100) . "\n\n";

echo "MASALAH:\n";
echo "1. Expense Payment ID 2 (28/04/2026):\n";
echo "   - coa_beban_id di database: 551 (BOP Sewa Tempat)\n";
echo "   - Tapi di Jurnal Umum: 550 (BOP Listrik)\n\n";

echo "KEMUNGKINAN PENYEBAB:\n";
echo "1. JournalService::createJournalFromExpensePayment() tidak dipanggil\n";
echo "2. Atau ada journal entry lama yang belum dihapus\n";
echo "3. Atau ada bug di JournalService saat membuat entry\n\n";

echo "SOLUSI:\n";
echo "1. Hapus semua journal entries untuk expense_payment di tanggal 28-29 April\n";
echo "2. Hapus semua jurnal_umum entries untuk pembayaran beban di tanggal 28-29 April\n";
echo "3. Buat ulang expense_payments dengan trigger yang benar\n";

$conn->close();
echo "</pre>";
?>
