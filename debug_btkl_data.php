<?php

echo "=== DEBUG BTKL DATA ISSUE ===\n\n";

echo "Checking BTKL data availability...\n";

// Simulate database connection and check data
try {
    // Check if proses_produksis table exists and has data
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Checking proses_produksis table structure...\n";
    $stmt = $pdo->query("DESCRIBE proses_produksis");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table proses_produksis exists with columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
    
    echo "2. Checking data in proses_produksis table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM proses_produksis");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records in proses_produksis: {$result['total']}\n\n";
    
    echo "3. Checking data for user_id = 1...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM proses_produksis WHERE user_id = ?");
    $stmt->execute([1]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Records for user_id = 1: {$result['total']}\n\n";
    
    echo "4. Sample data from proses_produksis:\n";
    $stmt = $pdo->query("SELECT id, kode_proses, nama_proses, user_id FROM proses_produksis LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($data)) {
        echo "No data found in proses_produksis table!\n";
    } else {
        foreach ($data as $row) {
            echo "  ID: {$row['id']}, Kode: {$row['kode_proses']}, Nama: {$row['nama_proses']}, User: {$row['user_id']}\n";
        }
    }
    echo "\n";
    
    echo "5. Checking if jabatans table exists and has data...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jabatans");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records in jabatans table: {$result['total']}\n\n";
    
    echo "6. Testing the exact query from API route...\n";
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
        echo "No data returned by API query for user_id = 1!\n";
        echo "This explains why BTKL data is not showing.\n";
    } else {
        echo "API query returned data:\n";
        foreach ($apiData as $row) {
            echo "  - {$row['nama_proses']} ({$row['kode_proses']}) - {$row['nama_jabatan']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
