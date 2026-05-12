<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Check and Fix 18/04/2026 Order</h1>";
echo "<pre>";

// First, check current state
echo "CURRENT STATE:\n";
echo str_repeat("=", 100) . "\n";

$sql = "SELECT id, tanggal, ref_type, memo, created_at
        FROM journal_entries
        WHERE DATE(tanggal) = '2026-04-18'
        ORDER BY created_at ASC";

$result = $conn->query($sql);

printf("%-5s | %-12s | %-30s | %-30s | %-20s\n", "ID", "Tanggal", "Ref Type", "Memo", "Created At");
echo str_repeat("-", 100) . "\n";

$entries = [];
while ($row = $result->fetch_assoc()) {
    printf("%-5s | %-12s | %-30s | %-30s | %-20s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['ref_type'], 0, 30),
        substr($row['memo'], 0, 30),
        $row['created_at']
    );
    $entries[] = $row;
}

echo "\n\nFIXING ORDER:\n";
echo str_repeat("=", 100) . "\n";

// Find the IDs
$material_id = null;
$labor_id = null;
$finish_id = null;

foreach ($entries as $entry) {
    if ($entry['ref_type'] == 'production_material') $material_id = $entry['id'];
    if ($entry['ref_type'] == 'production_labor_overhead') $labor_id = $entry['id'];
    if ($entry['ref_type'] == 'production_finish') $finish_id = $entry['id'];
}

echo "Found:\n";
echo "- production_material ID: " . $material_id . "\n";
echo "- production_labor_overhead ID: " . $labor_id . "\n";
echo "- production_finish ID: " . $finish_id . "\n\n";

// Get the created_at times
$material_time = null;
$finish_time = null;

foreach ($entries as $entry) {
    if ($entry['ref_type'] == 'production_material') $material_time = $entry['created_at'];
    if ($entry['ref_type'] == 'production_finish') $finish_time = $entry['created_at'];
}

echo "Times:\n";
echo "- production_material: " . $material_time . "\n";
echo "- production_finish: " . $finish_time . "\n\n";

// Calculate middle time for labor_overhead
// If material is 08:13:20 and finish is 08:13:53, labor should be 08:13:36
$material_dt = new DateTime($material_time);
$finish_dt = new DateTime($finish_time);
$diff = $finish_dt->getTimestamp() - $material_dt->getTimestamp();
$middle_time = $material_dt->modify('+' . intval($diff/2) . ' seconds')->format('Y-m-d H:i:s');

echo "Calculated middle time: " . $middle_time . "\n\n";

// Update the created_at
$sql = "UPDATE journal_entries
        SET created_at = '" . $middle_time . "'
        WHERE id = " . $labor_id;

if ($conn->query($sql)) {
    echo "✓ Updated production_labor_overhead created_at to: " . $middle_time . "\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFICATION AFTER FIX:\n";
echo str_repeat("=", 100) . "\n\n";

$sql = "SELECT id, tanggal, ref_type, memo, created_at
        FROM journal_entries
        WHERE DATE(tanggal) = '2026-04-18'
        ORDER BY created_at ASC";

$result = $conn->query($sql);

printf("%-5s | %-12s | %-30s | %-30s | %-20s | Status\n", "ID", "Tanggal", "Ref Type", "Memo", "Created At");
echo str_repeat("-", 110) . "\n";

$expected_order = ['production_material', 'production_labor_overhead', 'production_finish'];
$actual_order = [];

while ($row = $result->fetch_assoc()) {
    $actual_order[] = $row['ref_type'];
    
    $status = "✓";
    if (count($actual_order) > 1) {
        $prev_idx = array_search($actual_order[count($actual_order)-2], $expected_order);
        $curr_idx = array_search($row['ref_type'], $expected_order);
        
        if ($prev_idx !== false && $curr_idx !== false && $prev_idx > $curr_idx) {
            $status = "❌ WRONG";
        }
    }
    
    printf("%-5s | %-12s | %-30s | %-30s | %-20s | %s\n",
        $row['id'],
        $row['tanggal'],
        substr($row['ref_type'], 0, 30),
        substr($row['memo'], 0, 30),
        $row['created_at'],
        $status
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "✅ Done! Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
