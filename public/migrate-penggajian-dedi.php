<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Migrate Penggajian Dedi Gunawan to journal_entries</h1>";
echo "<pre>";

// Get data from jurnal_umum for Dedi Gunawan
$sql = "SELECT * FROM jurnal_umum WHERE keterangan LIKE '%Dedi Gunawan%' ORDER BY id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "No data found for Dedi Gunawan\n";
    exit;
}

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
}

echo "Found " . count($entries) . " entries for Dedi Gunawan\n\n";

// Group by tanggal and keterangan to create journal entry
$grouped = [];
foreach ($entries as $entry) {
    $key = $entry['tanggal'] . '|' . $entry['keterangan'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [];
    }
    $grouped[$key][] = $entry;
}

echo "Creating journal entries...\n\n";

foreach ($grouped as $key => $lines) {
    list($tanggal, $keterangan) = explode('|', $key);
    
    echo "Processing: " . $tanggal . " - " . $keterangan . "\n";
    
    // Create journal entry
    $sql = "INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
            VALUES ('" . $tanggal . "', 'penggajian', 0, '" . $conn->real_escape_string($keterangan) . "', NOW(), NOW())";
    
    if ($conn->query($sql)) {
        $entry_id = $conn->insert_id;
        echo "  ✓ Created journal_entry ID: " . $entry_id . "\n";
        
        // Create journal lines
        foreach ($lines as $line) {
            // Get COA ID
            $sql_coa = "SELECT id FROM coas WHERE kode_akun = '" . $line['kode_akun'] . "'";
            $result_coa = $conn->query($sql_coa);
            
            if ($result_coa && $result_coa->num_rows > 0) {
                $coa = $result_coa->fetch_assoc();
                $coa_id = $coa['id'];
                
                $sql_line = "INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                            VALUES (" . $entry_id . ", " . $coa_id . ", " . $line['debit'] . ", " . $line['kredit'] . ", 
                                    '" . $conn->real_escape_string($line['keterangan']) . "', NOW(), NOW())";
                
                if ($conn->query($sql_line)) {
                    echo "    ✓ Created journal_line for COA " . $line['kode_akun'] . "\n";
                } else {
                    echo "    ✗ Error creating journal_line: " . $conn->error . "\n";
                }
            } else {
                echo "    ✗ COA not found: " . $line['kode_akun'] . "\n";
            }
        }
    } else {
        echo "  ✗ Error creating journal_entry: " . $conn->error . "\n";
    }
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFICATION\n";
echo str_repeat("=", 100) . "\n\n";

// Verify
$sql = "SELECT je.id, je.tanggal, je.memo, COUNT(jl.id) as line_count
        FROM journal_entries je
        LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
        WHERE je.ref_type = 'penggajian'
        GROUP BY je.id
        ORDER BY je.tanggal DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Penggajian entries in journal_entries:\n\n";
    printf("%-5s | %-12s | %-40s | %-12s\n", "ID", "Tanggal", "Memo", "Lines");
    echo str_repeat("-", 80) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-40s | %-12s\n",
            $row['id'],
            $row['tanggal'],
            substr($row['memo'], 0, 40),
            $row['line_count']
        );
    }
} else {
    echo "No penggajian entries found\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "✅ Migration complete!\n";
echo "Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
