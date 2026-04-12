<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

echo "=== JOURNAL_LINES TABLE STRUCTURE ===\n";

$stmt = $pdo->query('DESCRIBE journal_lines');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $column) {
    echo "Column: {$column['Field']} ({$column['Type']})\n";
}

echo "\n=== SAMPLE DATA ===\n";
$stmt2 = $pdo->query('SELECT * FROM journal_lines LIMIT 3');
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    echo "Row: " . json_encode($row) . "\n";
}