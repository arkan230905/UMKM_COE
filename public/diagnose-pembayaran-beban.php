<?php
$conn = new mysqli('localhost', 'root', '', 'eadt_umkm');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);

echo "<h1>DIAGNOSA: Pembayaran Beban - Cek Semua Data</h1>";
echo "<pre>";

// Check 1: expense_payments table
echo "=== 1. EXPENSE_PAYMENTS TABLE ===\n";
$result = $conn->query("
    SELECT pb.id, pb.tanggal, pb.beban_operasional_id, pb.coa_beban_id, pb.coa_kasbank, pb.nominal_pembayaran, bo.nama_beban
    FROM expense_payments pb
    LEFT JOIN beban_operasional bo ON bo.id = pb.beban_operasional_id
    WHERE pb.tanggal IN ('2026-04-28', '2026-04-29')
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

// Check 2: journal_entries table
echo "\n=== 2. JOURNAL_ENTRIES TABLE ===\n";
$result = $conn->query("
    SELECT je.id, je.tanggal, je.ref_type, je.ref_id, je.memo
    FROM journal_entries je
    WHERE je.ref_type = 'expense_payment'
    AND je.tanggal IN ('2026-04-28', '2026-04-29')
    ORDER BY je.id
");

printf("%-5s | %-12s | %-20s | %-10s | %-40s\n", "ID", "Tanggal", "Ref Type", "Ref ID", "Memo");
echo str_repeat("-", 100) . "\n";

$je_data = [];
while ($row = $result->fetch_assoc()) {
    $je_data[] = $row;
    printf("%-5s | %-12s | %-20s | %-10s | %-40s\n",
        $row['id'],
        $row['tanggal'],
        $row['ref_type'],
        $row['ref_id'],
        substr($row['memo'], 0, 40)
    );
}

// Check 3: journal_lines table
echo "\n=== 3. JOURNAL_LINES TABLE ===\n";
$result = $conn->query("
    SELECT jl.id, jl.journal_entry_id, c.kode_akun, c.nama_akun, jl.debit, jl.credit
    FROM journal_lines jl
    LEFT JOIN coas c ON c.id = jl.coa_id
    WHERE jl.journal_entry_id IN (
        SELECT je.id FROM journal_entries je
        WHERE je.ref_type = 'expense_payment'
        AND je.tanggal IN ('2026-04-28', '2026-04-29')
    )
    ORDER BY jl.journal_entry_id, jl.id
");

printf("%-5s | %-10s | %-10s | %-40s | %-12s | %-12s\n", "ID", "JE ID", "COA", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 100) . "\n";

$jl_data = [];
while ($row = $result->fetch_assoc()) {
    $jl_data[] = $row;
    printf("%-5s | %-10s | %-10s | %-40s | %-12s | %-12s\n",
        $row['id'],
        $row['journal_entry_id'],
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        $row['debit'],
        $row['kredit']
    );
}

// Check 4: jurnal_umum table
echo "\n=== 4. JURNAL_UMUM TABLE ===\n";
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

$ju_data = [];
while ($row = $result->fetch_assoc()) {
    $ju_data[] = $row;
    printf("%-5s | %-12s | %-40s | %-10s | %-40s | %-12s | %-12s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        $row['debit'],
        $row['kredit']
    );
}

// Analysis
echo "\n" . str_repeat("=", 140) . "\n";
echo "ANALISIS\n";
echo str_repeat("=", 140) . "\n\n";

echo "EXPENSE_PAYMENTS:\n";
foreach ($expense_data as $e) {
    echo "  ID {$e['id']}: {$e['tanggal']} - {$e['nama_beban']} - COA {$e['coa_beban_id']}\n";
}

echo "\nJOURNAL_ENTRIES:\n";
foreach ($je_data as $j) {
    echo "  ID {$j['id']}: {$j['tanggal']} - Ref ID {$j['ref_id']} - {$j['memo']}\n";
}

echo "\nJOURNAL_LINES:\n";
foreach ($jl_data as $l) {
    echo "  JE {$l['journal_entry_id']}: {$l['kode_akun']} - Debit {$l['debit']} Kredit {$l['kredit']}\n";
}

echo "\nJURNAL_UMUM:\n";
foreach ($ju_data as $u) {
    echo "  ID {$u['id']}: {$u['tanggal']} - {$u['kode_akun']} - Debit {$u['debit']} Kredit {$u['kredit']}\n";
}

echo "\n" . str_repeat("=", 140) . "\n";
echo "KESIMPULAN\n";
echo str_repeat("=", 140) . "\n\n";

// Check if data matches
$mismatch = false;
foreach ($expense_data as $e) {
    $id = $e['id'];
    $expected_coa = $e['coa_beban_id'];
    
    // Check in journal_lines
    $jl_match = false;
    foreach ($jl_data as $l) {
        if ($l['journal_entry_id'] == $id && $l['debit'] > 0 && $l['kode_akun'] == $expected_coa) {
            $jl_match = true;
            break;
        }
    }
    
    // Check in jurnal_umum
    $ju_match = false;
    foreach ($ju_data as $u) {
        if ($u['debit'] > 0 && $u['kode_akun'] == $expected_coa) {
            $ju_match = true;
            break;
        }
    }
    
    if (!$jl_match || !$ju_match) {
        echo "❌ ID $id: Expected COA $expected_coa\n";
        if (!$jl_match) echo "   - journal_lines: TIDAK COCOK\n";
        if (!$ju_match) echo "   - jurnal_umum: TIDAK COCOK\n";
        $mismatch = true;
    }
}

if (!$mismatch) {
    echo "✅ Semua data cocok!\n";
}

$conn->close();
echo "</pre>";
?>
