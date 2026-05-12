<?php

// Simple script to update bahan pendukung stock
// Read database configuration from .env file

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env file not found');
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);
    }
    
    return $env;
}

try {
    echo "Loading environment configuration...\n";
    $env = loadEnv('.env');
    
    $host = $env['DB_HOST'] ?? 'localhost';
    $port = $env['DB_PORT'] ?? '3306';
    $database = $env['DB_DATABASE'] ?? '';
    $username = $env['DB_USERNAME'] ?? '';
    $password = $env['DB_PASSWORD'] ?? '';
    
    echo "Connecting to database: {$database}@{$host}:{$port}\n";
    
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully!\n";
    
    // Update bahan pendukung stock from 50 to 200
    $stmt = $pdo->prepare("UPDATE bahan_pendukungs SET stok = 200 WHERE stok = 50");
    $stmt->execute();
    $updated = $stmt->rowCount();
    
    echo "Updated {$updated} bahan pendukung records from 50 to 200 stock.\n";
    
    // Show current stock levels
    $stmt = $pdo->query("SELECT id, nama_bahan, stok FROM bahan_pendukungs ORDER BY id");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent bahan pendukung stock levels:\n";
    echo "ID\tNama Bahan\t\t\tStok\n";
    echo "------------------------------------------------\n";
    
    foreach ($results as $row) {
        echo "{$row['id']}\t{$row['nama_bahan']}\t\t\t{$row['stok']}\n";
    }
    
    echo "\nStock update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}