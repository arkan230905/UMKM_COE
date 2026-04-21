<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>CHECK: Expense Payments Data</h1>";
echo "<pre>";

// Check expense_payments
echo "=== EXPENSE_PAYMENTS TABLE ===\n";
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.beban_operasional_id, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.id IN (2, 3)
    ORDER BY pb.id
");

printf("%-3s | %-12s | %-40s | %-10s | %-10s | %-12s\n", "ID", "Tanggal", "Beban", "COA Beban", "COA Kas", "Nominal");
echo str_repeat("-", 100) . "\n";

$expense_data = [];
while ($row = $result->fetch_assoc()) {
    $expense_data[] = $row;
    printf("%-3s | %-12s | %-40s | %-10s | %-10s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['nama_beban'], 0, 40),
        $row['coa_beban_id'],
        $row['coa_kasbank'],
        $row['nominal_pembayaran']
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "ANALYSIS\n";
echo str_repeat("=", 100) . "\n\n";

foreach ($expense_data as $e) {
    echo "ID {$e['id']}: {$e['tanggal']} - {$e['nama_beban']}\n";
    echo "  coa_beban_id: {$e['coa_beban_id']}\n";
    
    // Get COA name
    $coa_result = $conn->query("SELECT nama_akun FROM coas WHERE kode_akun = '{$e['coa_beban_id']}'");
    if ($coa_result && $coa_result->num_rows > 0) {
        $coa = $coa_result->fetch_assoc();
        echo "  COA Name: {$coa['nama_akun']}\n";
    }
    echo "\n";
}

// Check journal_entries
echo "=== JOURNAL_ENTRIES TABLE ===\n";
$result = $conn->query("
    SELECT je.id, je.tanggal, je.ref_id, je.memo, jl.debit, jl.credit, c.kode_akun, c.nama_akun
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
    LEFT JOIN coas c ON c.id = jl.coa_id
    WHERE je.ref_type = 'expense_payment'
    AND je.ref_id IN (2, 3)
    ORDER BY je.id, jl.id
");

printf("%-5s | %-12s | %-10s | %-40s | %-12s | %-12s\n", "JE ID", "Tanggal", "COA", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 100) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-10s | %-40s | %-12s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        $row['debit'],
        $row['kredit']
    );
}

$conn->close();
echo "</pre>";
?>
