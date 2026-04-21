<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Find Dedi Gunawan & Check Tables</h1>";
echo "<pre>";

// List all tables
echo "=== ALL TABLES IN DATABASE ===\n";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
    echo "- " . $row[0] . "\n";
}

echo "\n=== SEARCH FOR DEDI GUNAWAN IN ALL TABLES ===\n";

// Search in all tables
foreach ($tables as $table) {
    $sql = "SELECT * FROM " . $table . " WHERE CAST(CONCAT_WS(' ', *) AS CHAR) LIKE '%Dedi Gunawan%' LIMIT 5";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "\nFound in table: " . $table . " (" . $result->num_rows . " rows)\n";
        echo str_repeat("-", 100) . "\n";
        
        // Get column names
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo $field->name . " | ";
        }
        echo "\n" . str_repeat("-", 100) . "\n";
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Show data
        while ($row = $result->fetch_assoc()) {
            echo json_encode($row) . "\n";
        }
    }
}

// Check for penggajian table
echo "\n\n=== CHECK PENGGAJIAN TABLE ===\n";
$sql = "SELECT * FROM penggajians WHERE nama_pegawai LIKE '%Dedi%' OR keterangan LIKE '%Dedi%'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " penggajian entries for Dedi:\n\n";
    printf("%-5s | %-12s | %-20s | %-15s | %-15s | %-30s\n", "ID", "Tanggal", "Nama", "Gaji", "Potongan", "Keterangan");
    echo str_repeat("-", 120) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-20s | %15s | %15s | %-30s\n",
            $row['id'] ?? '',
            $row['tanggal'] ?? '',
            substr($row['nama_pegawai'] ?? $row['nama'] ?? '', 0, 20),
            number_format($row['gaji'] ?? $row['jumlah'] ?? 0, 0, ',', '.'),
            number_format($row['potongan'] ?? 0, 0, ',', '.'),
            substr($row['keterangan'] ?? '', 0, 30)
        );
    }
} else {
    echo "No penggajian entries found for Dedi\n";
}

// Check journal_entries for penggajian
echo "\n\n=== CHECK JOURNAL_ENTRIES FOR PENGGAJIAN ===\n";
$sql = "SELECT * FROM journal_entries WHERE ref_type = 'penggajian' ORDER BY tanggal DESC LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " penggajian journal entries:\n\n";
    printf("%-5s | %-12s | %-10s | %-40s | %-20s\n", "ID", "Tanggal", "Ref ID", "Memo", "Created At");
    echo str_repeat("-", 120) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-12s | %-10s | %-40s | %-20s\n",
            $row['id'],
            $row['tanggal'],
            $row['ref_id'],
            substr($row['memo'], 0, 40),
            $row['created_at']
        );
    }
} else {
    echo "No penggajian journal entries found\n";
}

// Check if there's a general ledger or buku besar table
echo "\n\n=== SEARCH FOR GENERAL LEDGER / BUKU BESAR TABLE ===\n";
$ledger_tables = ['buku_besars', 'buku_besar', 'general_ledger', 'ledger', 'gl_entries'];
foreach ($ledger_tables as $table) {
    $sql = "SHOW TABLES LIKE '" . $table . "'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "✓ Found table: " . $table . "\n";
    }
}

$conn->close();
echo "</pre>";
?>
