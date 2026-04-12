<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

echo "=== CHECKING JOURNAL TABLES ===\n";

$stmt = $pdo->query('SHOW TABLES LIKE "%journal%"');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "Table: {$table}\n";
    
    // Show table structure
    $stmt2 = $pdo->query("DESCRIBE {$table}");
    $columns = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    echo "\n";
}