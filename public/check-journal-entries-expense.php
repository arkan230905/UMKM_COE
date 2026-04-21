<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>CHECK: Journal Entries untuk Expense Payment</h1>";
echo "<pre>";

// Check journal_entries
echo "=== JOURNAL_ENTRIES ===\n";
$result = $conn->query("
    SELECT je.id, je.tanggal, je.ref_type, je.ref_id, je.memo, je.created_at
    FROM journal_entries je
    WHERE je.ref_type = 'expense_payment'
    AND je.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY je.id
");

printf("%-5s | %-12s | %-20s | %-10s | %-40s | %-20s\n", "ID", "Tanggal", "Ref Type", "Ref ID", "Memo", "Created At");
echo str_repeat("-", 120) . "\n";

$je_ids = [];
while ($row = $result->fetch_assoc()) {
    $je_ids[] = $row['id'];
    printf("%-5s | %-12s | %-20s | %-10s | %-40s | %-20s\n",
        $row['id'],
        $row['tanggal'],
        $row['ref_type'],
        $row['ref_id'],
        substr($row['memo'], 0, 40),
        $row['created_at']
    );
}

if (empty($je_ids)) {
    echo "❌ TIDAK ADA JOURNAL ENTRIES UNTUK EXPENSE_PAYMENT!\n";
} else {
    echo "\n=== JOURNAL_LINES ===\n";
    $je_ids_str = implode(',', $je_ids);
    $result = $conn->query("
        SELECT jl.id, jl.journal_entry_id, c.kode_akun, c.nama_akun, jl.debit, jl.credit
        FROM journal_lines jl
        LEFT JOIN coas c ON c.id = jl.coa_id
        WHERE jl.journal_entry_id IN ($je_ids_str)
        ORDER BY jl.journal_entry_id, jl.id
    ");
    
    printf("%-5s | %-10s | %-10s | %-40s | %-12s | %-12s\n", "ID", "JE ID", "COA", "Nama Akun", "Debit", "Kredit");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-10s | %-10s | %-40s | %-12s | %-12s\n",
            $row['id'],
            $row['journal_entry_id'],
            $row['kode_akun'],
            substr($row['nama_akun'], 0, 40),
            $row['debit'],
            $row['kredit']
        );
    }
}

// Check expense_payments
echo "\n=== EXPENSE_PAYMENTS ===\n";
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY pb.id
");

printf("%-3s | %-12s | %-40s | %-10s | %-10s | %-12s\n", "ID", "Tanggal", "Beban", "COA Beban", "COA Kas", "Nominal");
echo str_repeat("-", 100) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-3s | %-12s | %-40s | %-10s | %-10s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['nama_beban'], 0, 40),
        $row['coa_beban_id'],
        $row['coa_kasbank'],
        $row['nominal_pembayaran']
    );
}

echo "\n" . str_repeat("=", 120) . "\n";
echo "KESIMPULAN\n";
echo str_repeat("=", 120) . "\n\n";

if (empty($je_ids)) {
    echo "❌ MASALAH: Tidak ada journal_entries untuk expense_payment!\n";
    echo "   Ini berarti model boot() method tidak dipanggil atau tidak membuat entry.\n";
    echo "   Solusi: Jalankan script untuk membuat journal_entries dari expense_payments.\n";
} else {
    echo "✓ Journal entries ada di database.\n";
    echo "✓ Masalahnya mungkin di query halaman Jurnal Umum.\n";
}

$conn->close();
echo "</pre>";
?>
