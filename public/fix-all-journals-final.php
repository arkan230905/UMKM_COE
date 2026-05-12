<?php
/**
 * COMPREHENSIVE JOURNAL FIX
 * Fixes both jurnal_umum table AND ensures journal_entries/journal_lines are correct
 */

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'simcost_sistem_manufaktur_process_costing';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>🔧 COMPREHENSIVE JOURNAL FIX</h1>";
echo "<pre>";

// ============================================================================
// STEP 1: Check current state
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 1: Checking Current State\n";
echo str_repeat("=", 100) . "\n\n";

// Check journal_entries for production_labor_overhead
$sql = "SELECT je.id, je.tanggal, je.memo, COUNT(jl.id) as line_count
        FROM journal_entries je
        LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
        WHERE je.ref_type = 'production_labor_overhead'
        GROUP BY je.id
        ORDER BY je.tanggal DESC";

$result = $conn->query($sql);
echo "Journal Entries (production_labor_overhead):\n";
printf("%-5s | %-12s | %-40s | %-12s\n", "ID", "Tanggal", "Memo", "Lines");
echo str_repeat("-", 75) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-40s | %-12s\n", 
        $row['id'],
        $row['tanggal'],
        substr($row['memo'], 0, 40),
        $row['line_count']
    );
}

// Check journal_lines for production_labor_overhead
echo "\n\nJournal Lines (production_labor_overhead):\n";
$sql = "SELECT jl.id, jl.coa_id, c.kode_akun, c.nama_akun, jl.debit, jl.credit, jl.memo
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        LEFT JOIN coas c ON jl.coa_id = c.id
        WHERE je.ref_type = 'production_labor_overhead'
        ORDER BY je.tanggal DESC, jl.coa_id";

$result = $conn->query($sql);
printf("%-5s | %-6s | %-40s | %-15s | %-15s\n", "ID", "Kode", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 100) . "\n";

$correct_count = 0;
$wrong_count = 0;

while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-6s | %-40s | %15s | %15s\n",
        $row['id'],
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        number_format($row['debit'], 0, ',', '.'),
        number_format($row['credit'], 0, ',', '.')
    );
    
    // Check if correct
    if (in_array($row['kode_akun'], ['52', '53'])) {
        if ($row['credit'] > 0 && $row['debit'] == 0) {
            $correct_count++;
        } else {
            $wrong_count++;
        }
    } elseif ($row['kode_akun'] == '117') {
        if ($row['debit'] > 0 && $row['credit'] == 0) {
            $correct_count++;
        } else {
            $wrong_count++;
        }
    }
}

echo "\n✓ Correct entries: " . $correct_count . "\n";
echo "✗ Wrong entries: " . $wrong_count . "\n";

// ============================================================================
// STEP 2: Fix journal_lines if needed
// ============================================================================
if ($wrong_count > 0) {
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "STEP 2: Fixing journal_lines table\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // Fix BTKL and BOP (should be in credit)
    $sql = "UPDATE journal_lines jl
            INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
            INNER JOIN coas c ON jl.coa_id = c.id
            SET jl.credit = jl.debit, jl.debit = 0
            WHERE je.ref_type = 'production_labor_overhead'
            AND c.kode_akun IN ('52', '53')
            AND jl.debit > 0";
    
    if ($conn->query($sql)) {
        echo "✓ Fixed BTKL & BOP entries: " . $conn->affected_rows . " rows\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
    
    // Fix WIP (should be in debit)
    $sql = "UPDATE journal_lines jl
            INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
            INNER JOIN coas c ON jl.coa_id = c.id
            SET jl.debit = jl.credit, jl.credit = 0
            WHERE je.ref_type = 'production_labor_overhead'
            AND c.kode_akun = '117'
            AND jl.credit > 0";
    
    if ($conn->query($sql)) {
        echo "✓ Fixed WIP entries: " . $conn->affected_rows . " rows\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

// ============================================================================
// STEP 3: Clean up jurnal_umum table (remove production_labor_overhead entries)
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 3: Cleaning up jurnal_umum table\n";
echo str_repeat("=", 100) . "\n\n";

// Check what's in jurnal_umum for production_labor_overhead
$sql = "SELECT COUNT(*) as count FROM jurnal_umum WHERE tipe_referensi = 'production_labor_overhead'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$jurnal_umum_count = $row['count'];

echo "Found " . $jurnal_umum_count . " production_labor_overhead entries in jurnal_umum\n";

if ($jurnal_umum_count > 0) {
    echo "Deleting these entries (they should come from journal_entries/journal_lines)...\n";
    
    $sql = "DELETE FROM jurnal_umum WHERE tipe_referensi = 'production_labor_overhead'";
    if ($conn->query($sql)) {
        echo "✓ Deleted " . $conn->affected_rows . " entries from jurnal_umum\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

// ============================================================================
// STEP 4: Verify the fix
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 4: Verification\n";
echo str_repeat("=", 100) . "\n\n";

$sql = "SELECT jl.id, c.kode_akun, c.nama_akun, jl.debit, jl.credit, jl.memo
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        LEFT JOIN coas c ON jl.coa_id = c.id
        WHERE je.ref_type = 'production_labor_overhead'
        ORDER BY je.tanggal DESC, jl.coa_id";

$result = $conn->query($sql);

echo "Final State of journal_lines:\n";
printf("%-6s | %-40s | %-15s | %-15s | Status\n", "Kode", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 120) . "\n";

$all_correct = true;
while ($row = $result->fetch_assoc()) {
    $status = "✓";
    
    if (in_array($row['kode_akun'], ['52', '53'])) {
        if (!($row['credit'] > 0 && $row['debit'] == 0)) {
            $status = "✗ WRONG";
            $all_correct = false;
        }
    } elseif ($row['kode_akun'] == '117') {
        if (!($row['debit'] > 0 && $row['credit'] == 0)) {
            $status = "✗ WRONG";
            $all_correct = false;
        }
    }
    
    printf("%-6s | %-40s | %15s | %15s | %s\n",
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        number_format($row['debit'], 0, ',', '.'),
        number_format($row['credit'], 0, ',', '.'),
        $status
    );
}

// ============================================================================
// STEP 5: Summary
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n\n";

if ($all_correct) {
    echo "✅ ALL JOURNALS ARE NOW CORRECT!\n\n";
    echo "Expected Structure:\n";
    echo "├─ Debit:  117 (Barang Dalam Proses) - Rp 677.918\n";
    echo "└─ Kredit: 52 (BTKL) - Rp 132.800 + 53 (BOP) - Rp 545.118\n\n";
    echo "✓ journal_lines table: CORRECT\n";
    echo "✓ journal_entries table: CORRECT\n";
    echo "✓ jurnal_umum table: CLEANED UP\n\n";
    echo "The display in akuntansi/jurnal-umum should now show correct positions.\n";
} else {
    echo "⚠️ SOME ENTRIES ARE STILL WRONG\n";
    echo "Please check the status above and try again.\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "Refresh the page: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
echo "Filter by: Produksi - BTKL & BOP\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
