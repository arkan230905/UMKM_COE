<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>DEBUG: Simulasi Query Controller</h1>";
echo "<pre>";

// Simulate the exact query from AkuntansiController::jurnalUmum()
echo "=== SIMULATING CONTROLLER QUERY ===\n\n";

// Step 1: Query journal_entries
echo "STEP 1: Query journal_entries\n";
$query1 = "
    SELECT je.id, je.tanggal, je.ref_type, je.ref_id, je.memo, jl.id as line_id, jl.debit, jl.credit, 
           jl.memo as line_memo, coas.kode_akun, coas.nama_akun, coas.tipe_akun
    FROM journal_entries je
    LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
    LEFT JOIN coas ON coas.id = jl.coa_id
    WHERE (jl.debit != 0 OR jl.credit != 0)
    AND je.ref_type = 'expense_payment'
    AND je.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY je.tanggal ASC, je.created_at ASC, je.id ASC, jl.id ASC
";

$result1 = $conn->query($query1);
$je_count = $result1->num_rows;
echo "Result: $je_count rows\n";

if ($je_count > 0) {
    printf("%-5s | %-12s | %-10s | %-40s | %-12s | %-12s\n", "JE ID", "Tanggal", "COA", "Nama Akun", "Debit", "Kredit");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $result1->fetch_assoc()) {
        printf("%-5s | %-12s | %-10s | %-40s | %-12s | %-12s\n",
            $row['id'],
            $row['tanggal'],
            $row['kode_akun'],
            substr($row['nama_akun'], 0, 40),
            $row['debit'],
            $row['kredit']
        );
    }
}

echo "\n";

// Step 2: Query jurnal_umum
echo "STEP 2: Query jurnal_umum\n";
$query2 = "
    SELECT ju.id, ju.tanggal, ju.keterangan as memo, ju.referensi, ju.tipe_referensi as ref_type,
           ju.debit, ju.kredit as credit, ju.created_at, coas.kode_akun, coas.nama_akun, coas.tipe_akun
    FROM jurnal_umum ju
    LEFT JOIN coas ON coas.id = ju.coa_id
    WHERE (ju.debit > 0 OR ju.kredit > 0)
    AND ju.tipe_referensi NOT IN ('purchase', 'sale', 'retur_pembelian', 'retur_penjualan',
                                   'production_material', 'production_labor_overhead', 'production_finished',
                                   'produksi', 'expense_payment')
    AND ju.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY ju.tanggal ASC, ju.created_at ASC, ju.id ASC
";

$result2 = $conn->query($query2);
$ju_count = $result2->num_rows;
echo "Result: $ju_count rows (should be 0 because expense_payment is excluded)\n";

echo "\n";

// Step 3: Check what's actually in jurnal_umum
echo "STEP 3: Check actual data in jurnal_umum for 28-29 April\n";
$query3 = "
    SELECT ju.id, ju.tanggal, ju.keterangan, ju.tipe_referensi, c.kode_akun, c.nama_akun, ju.debit, ju.kredit
    FROM jurnal_umum ju
    LEFT JOIN coas c ON c.id = ju.coa_id
    WHERE ju.tanggal IN ('2026-04-28', '2026-04-29')
    AND ju.keterangan LIKE '%Pembayaran Beban%'
    ORDER BY ju.tanggal, ju.id
";

$result3 = $conn->query($query3);
$actual_count = $result3->num_rows;
echo "Result: $actual_count rows\n";

printf("%-5s | %-12s | %-40s | %-20s | %-10s | %-40s | %-12s | %-12s\n", "ID", "Tanggal", "Keterangan", "Tipe", "COA", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 150) . "\n";

while ($row = $result3->fetch_assoc()) {
    printf("%-5s | %-12s | %-40s | %-20s | %-10s | %-40s | %-12s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['tipe_referensi'],
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        $row['debit'],
        $row['kredit']
    );
}

echo "\n" . str_repeat("=", 150) . "\n";
echo "ANALYSIS\n";
echo str_repeat("=", 150) . "\n\n";

if ($je_count == 0) {
    echo "❌ PROBLEM: No journal_entries for expense_payment!\n";
    echo "   The controller query returns 0 rows from journal_entries.\n";
    echo "   This means expense_payment entries are NOT in journal_entries table.\n";
}

if ($actual_count > 0 && $je_count == 0) {
    echo "✓ Data exists in jurnal_umum but NOT in journal_entries.\n";
    echo "✓ The controller query excludes expense_payment from jurnal_umum.\n";
    echo "✓ So the page displays NOTHING or OLD DATA.\n\n";
    
    echo "SOLUTION:\n";
    echo "1. Create journal_entries from expense_payments\n";
    echo "2. OR remove expense_payment from the exclusion list in controller\n";
}

$conn->close();
echo "</pre>";
?>
