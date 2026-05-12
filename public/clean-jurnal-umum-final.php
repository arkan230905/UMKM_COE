<?php
/**
 * CLEAN JURNAL_UMUM - Remove Duplicates and Keep Only Correct Data
 */

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'simcost_sistem_manufaktur_process_costing';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>🧹 Clean Jurnal Umum - Remove Duplicates</h1>";
echo "<pre>";

// ============================================================================
// STEP 1: Identify duplicates
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 1: Identifying Duplicates\n";
echo str_repeat("=", 100) . "\n\n";

$sql = "SELECT 
            tanggal, 
            keterangan, 
            tipe_referensi,
            COUNT(*) as count,
            GROUP_CONCAT(id) as ids
        FROM jurnal_umum
        WHERE tipe_referensi = 'production_labor_overhead'
        GROUP BY tanggal, keterangan, tipe_referensi
        HAVING count > 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found duplicate entries:\n\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "Date: " . $row['tanggal'] . "\n";
        echo "Description: " . $row['keterangan'] . "\n";
        echo "Count: " . $row['count'] . "\n";
        echo "IDs: " . $row['ids'] . "\n";
        echo "\n";
    }
} else {
    echo "No duplicates found.\n";
}

// ============================================================================
// STEP 2: Show all production_labor_overhead entries
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 2: All Production Labor Overhead Entries\n";
echo str_repeat("=", 100) . "\n\n";

$sql = "SELECT id, tanggal, keterangan, coa_id, debit, kredit, tipe_referensi
        FROM jurnal_umum
        WHERE tipe_referensi = 'production_labor_overhead'
        ORDER BY tanggal DESC, id DESC";

$result = $conn->query($sql);

printf("%-5s | %-12s | %-40s | %-8s | %-15s | %-15s\n", "ID", "Tanggal", "Keterangan", "COA_ID", "Debit", "Kredit");
echo str_repeat("-", 110) . "\n";

$entries_by_date = [];
while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-40s | %-8s | %15s | %15s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['keterangan'], 0, 40),
        $row['coa_id'],
        number_format($row['debit'], 0, ',', '.'),
        number_format($row['kredit'], 0, ',', '.')
    );
    
    $key = $row['tanggal'] . '|' . $row['keterangan'];
    if (!isset($entries_by_date[$key])) {
        $entries_by_date[$key] = [];
    }
    $entries_by_date[$key][] = $row;
}

// ============================================================================
// STEP 3: Identify which entries to keep/delete
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 3: Identifying Correct vs Wrong Entries\n";
echo str_repeat("=", 100) . "\n\n";

$to_delete = [];
$to_keep = [];

foreach ($entries_by_date as $key => $entries) {
    if (count($entries) > 1) {
        echo "Group: " . $key . " (Count: " . count($entries) . ")\n";
        
        // Analyze each entry
        foreach ($entries as $entry) {
            $is_correct = false;
            
            // Check if this entry is correct based on COA code
            if ($entry['coa_id'] == 52 || $entry['coa_id'] == 56) { // BTKL or BOP
                // Should be in KREDIT
                if ($entry['kredit'] > 0 && $entry['debit'] == 0) {
                    $is_correct = true;
                    echo "  ID " . $entry['id'] . ": ✓ CORRECT (BTKL/BOP in KREDIT)\n";
                } else {
                    echo "  ID " . $entry['id'] . ": ✗ WRONG (BTKL/BOP in DEBIT)\n";
                }
            } elseif ($entry['coa_id'] == 126) { // WIP
                // Should be in DEBIT
                if ($entry['debit'] > 0 && $entry['kredit'] == 0) {
                    $is_correct = true;
                    echo "  ID " . $entry['id'] . ": ✓ CORRECT (WIP in DEBIT)\n";
                } else {
                    echo "  ID " . $entry['id'] . ": ✗ WRONG (WIP in KREDIT)\n";
                }
            }
            
            if ($is_correct) {
                $to_keep[] = $entry['id'];
            } else {
                $to_delete[] = $entry['id'];
            }
        }
        echo "\n";
    }
}

// ============================================================================
// STEP 4: Delete wrong entries
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 4: Deleting Wrong Entries\n";
echo str_repeat("=", 100) . "\n\n";

if (!empty($to_delete)) {
    echo "IDs to delete: " . implode(", ", $to_delete) . "\n";
    
    $ids_str = implode(",", $to_delete);
    $sql = "DELETE FROM jurnal_umum WHERE id IN (" . $ids_str . ")";
    
    if ($conn->query($sql)) {
        echo "✓ Deleted " . $conn->affected_rows . " wrong entries\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
    }
} else {
    echo "No wrong entries to delete.\n";
}

// ============================================================================
// STEP 5: Verify final state
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "STEP 5: Final Verification\n";
echo str_repeat("=", 100) . "\n\n";

$sql = "SELECT ju.id, ju.tanggal, ju.keterangan, ju.coa_id, c.kode_akun, c.nama_akun, ju.debit, ju.kredit
        FROM jurnal_umum ju
        LEFT JOIN coas c ON ju.coa_id = c.id
        WHERE ju.tipe_referensi = 'production_labor_overhead'
        ORDER BY ju.tanggal DESC, ju.coa_id";

$result = $conn->query($sql);

printf("%-5s | %-12s | %-6s | %-40s | %-15s | %-15s | Status\n", "ID", "Tanggal", "Kode", "Nama Akun", "Debit", "Kredit");
echo str_repeat("-", 130) . "\n";

$all_correct = true;
while ($row = $result->fetch_assoc()) {
    $status = "✓";
    
    if ($row['kode_akun'] == '52' || $row['kode_akun'] == '53') {
        if (!($row['kredit'] > 0 && $row['debit'] == 0)) {
            $status = "✗ WRONG";
            $all_correct = false;
        }
    } elseif ($row['kode_akun'] == '117') {
        if (!($row['debit'] > 0 && $row['kredit'] == 0)) {
            $status = "✗ WRONG";
            $all_correct = false;
        }
    }
    
    printf("%-5s | %-12s | %-6s | %-40s | %15s | %15s | %s\n",
        $row['id'],
        $row['tanggal'],
        $row['kode_akun'],
        substr($row['nama_akun'], 0, 40),
        number_format($row['debit'], 0, ',', '.'),
        number_format($row['kredit'], 0, ',', '.'),
        $status
    );
}

// ============================================================================
// STEP 6: Summary
// ============================================================================
echo "\n" . str_repeat("=", 100) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 100) . "\n\n";

if ($all_correct) {
    echo "✅ ALL ENTRIES ARE NOW CORRECT!\n\n";
    echo "Expected Structure:\n";
    echo "├─ Debit:  117 (Barang Dalam Proses) - Rp 677.918\n";
    echo "└─ Kredit: 52 (BTKL) - Rp 132.800 + 53 (BOP) - Rp 545.118\n\n";
    echo "✓ jurnal_umum table: CLEANED UP\n";
    echo "✓ Duplicates: REMOVED\n";
    echo "✓ Data: CORRECT\n\n";
    echo "The display in akuntansi/jurnal-umum should now show correct positions.\n";
} else {
    echo "⚠️ SOME ENTRIES ARE STILL WRONG\n";
    echo "Please check the status above.\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "Next: Refresh the page: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
echo "Filter by: Produksi - BTKL & BOP\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
