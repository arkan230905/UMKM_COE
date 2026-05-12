<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Check Table Structure</h1>";
echo "<pre>";

// Check journal_lines structure
echo "=== journal_lines table structure ===\n";
$result = $conn->query("DESCRIBE journal_lines");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . "\n";
}

echo "\n=== Sample data from journal_lines ===\n";
$result = $conn->query("SELECT * FROM journal_lines LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== Check production_labor_overhead entries ===\n";
$result = $conn->query("
    SELECT jl.*, je.ref_type, c.kode_akun
    FROM journal_lines jl
    INNER JOIN journal_entries je ON jl.journal_entry_id = je.id
    LEFT JOIN coas c ON jl.coa_id = c.id
    WHERE je.ref_type = 'production_labor_overhead'
    LIMIT 10
");

while ($row = $result->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

$conn->close();
echo "</pre>";
?>
