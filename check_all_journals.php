<?php

$pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');

echo "=== ALL JOURNAL ENTRIES ===\n";

$stmt = $pdo->query('SELECT * FROM journal_entries ORDER BY id DESC LIMIT 10');
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($entries as $entry) {
    echo "ID: {$entry['id']}, Date: {$entry['tanggal']}, Ref: {$entry['ref_type']} #{$entry['ref_id']}, Memo: {$entry['memo']}\n";
}

if (empty($entries)) {
    echo "No journal entries found\n";
    
    // Check if the journal service is using a different table
    echo "\nChecking for other possible journal tables...\n";
    $stmt2 = $pdo->query('SHOW TABLES');
    $tables = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        if (strpos(strtolower($table), 'jurnal') !== false) {
            echo "Found table: {$table}\n";
            
            $stmt3 = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
            $count = $stmt3->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  Records: {$count}\n";
        }
    }
}