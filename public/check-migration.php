<?php
// Check if payment proof columns exist in penjualans table

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'simcost_sistem_manufaktur_process_costing';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Migration Status Check</h1>";
echo "<pre>";

// Check if columns exist
$result = $conn->query("DESCRIBE penjualans");

$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[$row['Field']] = $row['Type'];
}

echo "Checking for payment proof columns...\n";
echo str_repeat("=", 60) . "\n";

if (isset($columns['bukti_pembayaran'])) {
    echo "✓ bukti_pembayaran column EXISTS\n";
    echo "  Type: " . $columns['bukti_pembayaran'] . "\n";
} else {
    echo "✗ bukti_pembayaran column MISSING\n";
}

if (isset($columns['catatan_pembayaran'])) {
    echo "✓ catatan_pembayaran column EXISTS\n";
    echo "  Type: " . $columns['catatan_pembayaran'] . "\n";
} else {
    echo "✗ catatan_pembayaran column MISSING\n";
}

echo str_repeat("=", 60) . "\n\n";

// If columns don't exist, run the migration
if (!isset($columns['bukti_pembayaran']) || !isset($columns['catatan_pembayaran'])) {
    echo "Running migration to add payment proof columns...\n\n";
    
    $sql = "ALTER TABLE penjualans ADD COLUMN bukti_pembayaran VARCHAR(255) NULL AFTER total";
    if ($conn->query($sql)) {
        echo "✓ Added bukti_pembayaran column\n";
    } else {
        echo "✗ Error adding bukti_pembayaran: " . $conn->error . "\n";
    }
    
    $sql = "ALTER TABLE penjualans ADD COLUMN catatan_pembayaran LONGTEXT NULL AFTER bukti_pembayaran";
    if ($conn->query($sql)) {
        echo "✓ Added catatan_pembayaran column\n";
    } else {
        echo "✗ Error adding catatan_pembayaran: " . $conn->error . "\n";
    }
    
    echo "\n✓ Migration completed!\n";
} else {
    echo "✓ All payment proof columns already exist!\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Current penjualans table structure:\n";
echo str_repeat("=", 60) . "\n";

$result = $conn->query("DESCRIBE penjualans");
printf("%-25s | %-20s | %-10s | %-10s | %-20s\n", "Field", "Type", "Null", "Key", "Default");
echo str_repeat("-", 100) . "\n";

while ($row = $result->fetch_assoc()) {
    printf("%-25s | %-20s | %-10s | %-10s | %-20s\n", 
        $row['Field'],
        $row['Type'],
        $row['Null'],
        $row['Key'] ?? '',
        $row['Default'] ?? ''
    );
}

$conn->close();
echo "</pre>";
?>
