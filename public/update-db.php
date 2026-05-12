<?php
// Direct database connection and update

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'simcost_sistem_manufaktur_process_costing';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Update Jurnal BTKL & BOP</h1>";
echo "<pre>";

// SQL to fix BTKL and BOP
$sql = "UPDATE journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
SET jl.credit = jl.debit, jl.debit = 0
WHERE je.ref_type = 'production_labor_overhead'
AND jl.coa_code IN ('52', '53')
AND jl.debit > 0";

if ($conn->query($sql) === TRUE) {
    echo "✓ Update berhasil!\n";
    echo "Baris yang diupdate: " . $conn->affected_rows . "\n\n";
    
    // Show results
    $result = $conn->query("
        SELECT 
            je.tanggal,
            je.memo,
            jl.coa_code,
            jl.debit,
            jl.credit
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        WHERE je.ref_type = 'production_labor_overhead'
        ORDER BY je.tanggal DESC, jl.coa_code
    ");
    
    echo "Hasil Perbaikan:\n";
    echo str_repeat("=", 100) . "\n";
    printf("%-12s | %-40s | %-10s | %-15s | %-15s\n", "Tanggal", "Memo", "Kode", "Debit", "Kredit");
    echo str_repeat("=", 100) . "\n";
    
    while($row = $result->fetch_assoc()) {
        printf("%-12s | %-40s | %-10s | %15s | %15s\n", 
            $row["tanggal"],
            substr($row["memo"], 0, 40),
            $row["coa_code"],
            number_format($row["debit"], 0, ',', '.'),
            number_format($row["credit"], 0, ',', '.')
        );
    }
    
    echo str_repeat("=", 100) . "\n";
    echo "\n✓ Selesai! Refresh halaman jurnal-umum untuk melihat perubahan.\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

$conn->close();
echo "</pre>";
?>
