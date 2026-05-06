<?php

echo "=== DEBUG SQLITE BTKL DATA ===\n\n";

echo "Checking BTKL data in SQLite database...\n";

try {
    // Connect to SQLite database
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Checking all tables in database...\n";
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tables) . "\n\n";
    
    echo "2. Checking proses_produksis table...\n";
    if (in_array('proses_produksis', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM proses_produksis");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total records in proses_produksis: {$result['total']}\n";
        
        if ($result['total'] > 0) {
            echo "\nSample data from proses_produksis:\n";
            $stmt = $pdo->query("SELECT id, kode_proses, nama_proses, user_id FROM proses_produksis LIMIT 5");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $row) {
                echo "  ID: {$row['id']}, Kode: {$row['kode_proses']}, Nama: {$row['nama_proses']}, User: {$row['user_id']}\n";
            }
            
            echo "\nChecking data for user_id = 1:\n";
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM proses_produksis WHERE user_id = ?");
            $stmt->execute([1]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Records for user_id = 1: {$result['total']}\n";
            
            if ($result['total'] > 0) {
                echo "\nTesting API query for user_id = 1:\n";
                $stmt = $pdo->prepare("
                    SELECT 
                        pp.id,
                        pp.kode_proses,
                        pp.nama_proses,
                        j.nama_jabatan,
                        pp.tarif_btkl,
                        pp.kapasitas_per_jam,
                        pp.btkl_per_produk,
                        pp.bop_per_produk
                    FROM proses_produksis as pp
                    LEFT JOIN jabatans as j ON pp.jabatan_id = j.id
                    WHERE pp.user_id = ?
                    ORDER BY pp.nama_proses
                    LIMIT 5
                ");
                $stmt->execute([1]);
                $apiData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($apiData)) {
                    echo "No data returned by API query!\n";
                } else {
                    foreach ($apiData as $row) {
                        echo "  - {$row['nama_proses']} ({$row['kode_proses']}) - {$row['nama_jabatan']}\n";
                    }
                }
            }
        }
    } else {
        echo "Table proses_produksis not found!\n";
    }
    
    echo "\n3. Checking other possible BTKL tables...\n";
    $possibleTables = ['proses_btkl', 'btkl', 'proses', 'produksi'];
    foreach ($possibleTables as $table) {
        if (in_array($table, $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM {$table}");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Table {$table}: {$result['total']} records\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
