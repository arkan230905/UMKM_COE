<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Check Journal Entry Order</h1>";
echo "<pre>";

// Check all journal entries for 17/04/2026
$sql = "SELECT id, tanggal, ref_type, memo, created_at, updated_at
        FROM journal_entries
        WHERE DATE(tanggal) = '2026-04-17'
        ORDER BY created_at ASC";

$result = $conn->query($sql);

echo "Journal Entries for 17/04/2026:\n";
echo str_repeat("=", 120) . "\n";
printf("%-5s | %-12s | %-30s | %-30s | %-20s\n", "ID", "Tanggal", "Ref Type", "Memo", "Created At");
echo str_repeat("=", 120) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-30s | %-30s | %-20s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['ref_type'], 0, 30),
        substr($row['memo'], 0, 30),
        $row['created_at']
    );
}

echo "\n\nExpected Order:\n";
echo "1. production_material (Konsumsi Material)\n";
echo "2. production_labor_overhead (Alokasi BTKL & BOP)\n";
echo "3. production_finish (Transfer WIP ke Barang Jadi)\n";

$conn->close();
echo "</pre>";
?>
