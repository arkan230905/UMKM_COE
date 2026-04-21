<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Check Penggajian Dedi Gunawan</h1>";
echo "<pre>";

// Check in journal_entries
echo "=== JOURNAL ENTRIES ===\n";
$sql = "SELECT * FROM journal_entries WHERE memo LIKE '%Dedi Gunawan%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " entries:\n\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Tanggal: " . $row['tanggal'] . "\n";
        echo "Ref Type: " . $row['ref_type'] . "\n";
        echo "Ref ID: " . $row['ref_id'] . "\n";
        echo "Memo: " . $row['memo'] . "\n";
        echo "Created At: " . $row['created_at'] . "\n\n";
    }
} else {
    echo "No entries found for Dedi Gunawan\n\n";
}

// Check in journal_lines
echo "=== JOURNAL LINES ===\n";
$sql = "SELECT jl.*, je.memo, c.kode_akun, c.nama_akun
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        LEFT JOIN coas c ON jl.coa_id = c.id
        WHERE je.memo LIKE '%Dedi Gunawan%'
        ORDER BY jl.id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " lines:\n\n";
    printf("%-5s | %-6s | %-40s | %-15s | %-15s | Memo\n", "ID", "Kode", "Nama Akun", "Debit", "Kredit");
    echo str_repeat("-", 120) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-6s | %-40s | %15s | %15s | %s\n",
            $row['id'],
            $row['kode_akun'],
            substr($row['nama_akun'], 0, 40),
            number_format($row['debit'], 0, ',', '.'),
            number_format($row['credit'], 0, ',', '.'),
            substr($row['memo'], 0, 30)
        );
    }
} else {
    echo "No lines found for Dedi Gunawan\n\n";
}

// Check in buku_besars
echo "\n=== BUKU BESAR ===\n";
$sql = "SELECT bb.*, c.kode_akun, c.nama_akun
        FROM buku_besars bb
        LEFT JOIN coas c ON bb.coa_id = c.id
        WHERE bb.keterangan LIKE '%Dedi Gunawan%'
        ORDER BY bb.id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " entries:\n\n";
    printf("%-5s | %-6s | %-40s | %-15s | %-15s | Keterangan\n", "ID", "Kode", "Nama Akun", "Debit", "Kredit");
    echo str_repeat("-", 120) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-5s | %-6s | %-40s | %15s | %15s | %s\n",
            $row['id'],
            $row['kode_akun'],
            substr($row['nama_akun'], 0, 40),
            number_format($row['debit'], 0, ',', '.'),
            number_format($row['credit'], 0, ',', '.'),
            substr($row['keterangan'], 0, 30)
        );
    }
} else {
    echo "No entries found in buku_besars for Dedi Gunawan\n\n";
}

// Check KAS account (112)
echo "\n=== KAS ACCOUNT (112) - ALL ENTRIES ===\n";
$sql = "SELECT bb.*, c.kode_akun, c.nama_akun
        FROM buku_besars bb
        LEFT JOIN coas c ON bb.coa_id = c.id
        WHERE c.kode_akun = '112'
        ORDER BY bb.tanggal DESC, bb.id DESC
        LIMIT 20";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " entries in KAS (112):\n\n";
    printf("%-12s | %-15s | %-15s | %-40s\n", "Tanggal", "Debit", "Kredit", "Keterangan");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-12s | %15s | %15s | %-40s\n",
            $row['tanggal'],
            number_format($row['debit'], 0, ',', '.'),
            number_format($row['credit'], 0, ',', '.'),
            substr($row['keterangan'], 0, 40)
        );
    }
} else {
    echo "No entries found in KAS (112)\n\n";
}

// Check journal_lines for KAS account
echo "\n=== JOURNAL LINES FOR KAS (112) ===\n";
$sql = "SELECT jl.*, je.tanggal, je.memo, c.kode_akun, c.nama_akun
        FROM journal_lines jl
        INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
        LEFT JOIN coas c ON jl.coa_id = c.id
        WHERE c.kode_akun = '112'
        ORDER BY je.tanggal DESC, jl.id DESC
        LIMIT 20";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " entries in journal_lines for KAS (112):\n\n";
    printf("%-12s | %-15s | %-15s | %-40s\n", "Tanggal", "Debit", "Kredit", "Memo");
    echo str_repeat("-", 100) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-12s | %15s | %15s | %-40s\n",
            $row['tanggal'],
            number_format($row['debit'], 0, ',', '.'),
            number_format($row['credit'], 0, ',', '.'),
            substr($row['memo'], 0, 40)
        );
    }
} else {
    echo "No entries found in journal_lines for KAS (112)\n\n";
}

$conn->close();
echo "</pre>";
?>
