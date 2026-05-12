<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Fixing Journal Positions...</h1>";
echo "<pre>";

// Fix BTKL & BOP
$sql = "UPDATE journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        INNER JOIN coas c ON jl.coa_id = c.id
        SET jl.credit = jl.debit, jl.debit = 0
        WHERE je.ref_type = 'production_labor_overhead'
        AND c.kode_akun IN ('52', '53')
        AND jl.debit > 0";

if ($conn->query($sql)) {
    echo "✓ Fixed BTKL & BOP: " . $conn->affected_rows . " rows\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Fix WIP
$sql = "UPDATE journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        INNER JOIN coas c ON jl.coa_id = c.id
        SET jl.debit = jl.credit, jl.credit = 0
        WHERE je.ref_type = 'production_labor_overhead'
        AND c.kode_akun = '117'
        AND jl.credit > 0";

if ($conn->query($sql)) {
    echo "✓ Fixed WIP: " . $conn->affected_rows . " rows\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Clean jurnal_umum
$sql = "DELETE FROM jurnal_umum WHERE tipe_referensi = 'production_labor_overhead'";

if ($conn->query($sql)) {
    echo "✓ Cleaned jurnal_umum: " . $conn->affected_rows . " rows deleted\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Verify
echo "\n" . str_repeat("=", 80) . "\n";
echo "VERIFICATION\n";
echo str_repeat("=", 80) . "\n\n";

$sql = "SELECT je.tanggal, c.kode_akun, c.nama_akun, jl.debit, jl.credit
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        LEFT JOIN coas c ON jl.coa_id = c.id
        WHERE je.ref_type = 'production_labor_overhead'
        ORDER BY je.tanggal DESC, c.kode_akun";

$result = $conn->query($sql);

printf("%-12s | %-6s | %-40s | %-15s | %-15s | Status\n", "Tanggal", "Kode", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 120) . "\n";

$all_correct = true;
while ($row = $result->fetch_assoc()) {
    $status = "✓";
    
    if ($row['kode_akun'] == '52' || $row['kode_akun'] == '53') {
        if (!($row['kredit'] > 0 && $row['debit'] == 0)) {
            $status = "❌ WRONG";
            $all_correct = false;
        }
    } elseif ($row['kode_akun'] == '117') {
        if (!($row['debit'] > 0 && $row['kredit'] == 0)) {
            $status = "❌ WRONG";
            $all_correct = false;
        }
    }
    
    printf("%-12s | %-6s | %-40s | %15s | %15s | %s\n",
        $row['tanggal'],
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        number_format($row['debit'], 0, ',', '.'),
        number_format($row['kredit'], 0, ',', '.'),
        $status
    );
}

echo "\n" . str_repeat("=", 80) . "\n";
if ($all_correct) {
    echo "✅ ALL FIXED!\n";
} else {
    echo "⚠ SOME ENTRIES STILL WRONG\n";
}
echo str_repeat("=", 80) . "\n";

echo "\nRefresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";

$conn->close();
echo "</pre>";
?>
