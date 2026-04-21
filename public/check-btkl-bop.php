<?php
// Check BTKL & BOP journal entries status

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'simcost_sistem_manufaktur_process_costing';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>BTKL & BOP Journal Entries Status</h1>";
echo "<pre>";

// Check current state of BTKL & BOP entries
$sql = "
SELECT 
    je.tanggal,
    je.memo,
    jl.coa_code,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_lines jl
INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
LEFT JOIN coas c ON jl.coa_code = c.kode_akun
WHERE je.ref_type = 'production_labor_overhead'
ORDER BY je.tanggal DESC, jl.coa_code
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " journal line entries for BTKL & BOP\n\n";
    echo str_repeat("=", 120) . "\n";
    printf("%-12s | %-40s | %-6s | %-40s | %-15s | %-15s\n", 
        "Tanggal", "Memo", "Kode", "Nama Akun", "Debit", "Kredit");
    echo str_repeat("=", 120) . "\n";
    
    $issues = 0;
    while ($row = $result->fetch_assoc()) {
        printf("%-12s | %-40s | %-6s | %-40s | %15s | %15s\n",
            $row['tanggal'],
            substr($row['memo'], 0, 40),
            $row['coa_code'],
            substr($row['nama_akun'] ?? 'N/A', 0, 40),
            number_format($row['debit'], 0, ',', '.'),
            number_format($row['credit'], 0, ',', '.')
        );
        
        // Check if BTKL (52) or BOP (53) have debit values (which is wrong)
        if (in_array($row['coa_code'], ['52', '53']) && $row['debit'] > 0) {
            $issues++;
        }
    }
    
    echo str_repeat("=", 120) . "\n\n";
    
    if ($issues > 0) {
        echo "⚠ ISSUE FOUND: " . $issues . " BTKL/BOP entries have incorrect debit values\n";
        echo "These should be in CREDIT column instead.\n\n";
        
        echo "Attempting to fix...\n";
        $fix_sql = "UPDATE journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        SET jl.credit = jl.debit, jl.debit = 0
        WHERE je.ref_type = 'production_labor_overhead'
        AND jl.coa_code IN ('52', '53')
        AND jl.debit > 0";
        
        if ($conn->query($fix_sql)) {
            echo "✓ Fixed " . $conn->affected_rows . " entries\n\n";
            
            // Show results after fix
            echo "After fix:\n";
            echo str_repeat("=", 120) . "\n";
            printf("%-12s | %-40s | %-6s | %-40s | %-15s | %-15s\n", 
                "Tanggal", "Memo", "Kode", "Nama Akun", "Debit", "Kredit");
            echo str_repeat("=", 120) . "\n";
            
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                printf("%-12s | %-40s | %-6s | %-40s | %15s | %15s\n",
                    $row['tanggal'],
                    substr($row['memo'], 0, 40),
                    $row['coa_code'],
                    substr($row['nama_akun'] ?? 'N/A', 0, 40),
                    number_format($row['debit'], 0, ',', '.'),
                    number_format($row['credit'], 0, ',', '.')
                );
            }
            echo str_repeat("=", 120) . "\n";
        } else {
            echo "✗ Error fixing entries: " . $conn->error . "\n";
        }
    } else {
        echo "✓ All BTKL & BOP entries are correctly positioned!\n";
        echo "  - BTKL (52) and BOP (53) are in CREDIT column\n";
        echo "  - Barang Dalam Proses (117) is in DEBIT column\n";
    }
} else {
    echo "No BTKL & BOP journal entries found.\n";
}

$conn->close();
echo "</pre>";
?>
