<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'eadt_umkm';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Fix Journal Entry Order</h1>";
echo "<pre>";

// Fix the created_at for production_labor_overhead entries
// They should be created BEFORE production_finish

// For 17/04/2026: production_labor_overhead should be between production_material and production_finish
// production_material: 08:10:41
// production_labor_overhead: should be 08:11:00 (between material and finish)
// production_finish: 08:12:18

$sql = "UPDATE journal_entries
        SET created_at = '2026-04-20 08:11:00'
        WHERE DATE(tanggal) = '2026-04-17'
        AND ref_type = 'production_labor_overhead'";

if ($conn->query($sql)) {
    echo "✓ Fixed created_at for production_labor_overhead on 17/04/2026\n";
    echo "  Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Do the same for 18/04/2026
$sql = "UPDATE journal_entries
        SET created_at = '2026-04-20 08:14:00'
        WHERE DATE(tanggal) = '2026-04-18'
        AND ref_type = 'production_labor_overhead'";

if ($conn->query($sql)) {
    echo "✓ Fixed created_at for production_labor_overhead on 18/04/2026\n";
    echo "  Affected rows: " . $conn->affected_rows . "\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "VERIFICATION\n";
echo str_repeat("=", 100) . "\n\n";

// Verify the order
$sql = "SELECT id, tanggal, ref_type, memo, created_at
        FROM journal_entries
        WHERE DATE(tanggal) IN ('2026-04-17', '2026-04-18')
        ORDER BY tanggal ASC, created_at ASC";

$result = $conn->query($sql);

printf("%-5s | %-12s | %-30s | %-30s | %-20s | Status\n", "ID", "Tanggal", "Ref Type", "Memo", "Created At");
echo str_repeat("-", 130) . "\n";

$prev_tanggal = null;
$prev_ref_type = null;
$expected_order = ['production_material', 'production_labor_overhead', 'production_finish'];
$current_order = [];

while ($row = $result->fetch_assoc()) {
    if ($row['tanggal'] != $prev_tanggal) {
        $current_order = [];
        $prev_tanggal = $row['tanggal'];
    }
    
    $current_order[] = $row['ref_type'];
    
    $status = "✓";
    if (count($current_order) > 1) {
        $prev_idx = array_search($current_order[count($current_order)-2], $expected_order);
        $curr_idx = array_search($row['ref_type'], $expected_order);
        
        if ($prev_idx !== false && $curr_idx !== false && $prev_idx > $curr_idx) {
            $status = "❌ WRONG ORDER";
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
echo "✅ Order should now be correct!\n";
echo "Refresh: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
echo str_repeat("=", 100) . "\n";

$conn->close();
echo "</pre>";
?>
