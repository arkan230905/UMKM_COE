<?php

echo "=== SIMPLE TABLE CHECK ===\n\n";

// Try to access old table directly
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm', 'root', '');
    
    // Check if bom_job_costings exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'eadt_umkm' AND table_name = 'bom_job_costings'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        echo "❌ Table bom_job_costings still EXISTS\n";
    } else {
        echo "✅ Table bom_job_costings does NOT exist\n";
    }
    
    // Check other old tables
    $oldTables = ['bom_job_bbb', 'bom_job_bahan_pendukung', 'bom_job_btkl', 'bom_job_bop'];
    
    foreach ($oldTables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'eadt_umkm' AND table_name = '{$table}'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo "❌ Table {$table} still EXISTS\n";
        } else {
            echo "✅ Table {$table} does NOT exist\n";
        }
    }
    
    echo "\n=== NEW TABLES CHECK ===\n";
    
    // Check new tables
    $newTables = ['harga_pokok_produksi_biaya_bahan_baku', 'harga_pokok_produksi_btkl', 'harga_pokok_produksi_bop'];
    
    foreach ($newTables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'eadt_umkm' AND table_name = '{$table}'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            echo "✅ Table {$table} EXISTS\n";
        } else {
            echo "❌ Table {$table} does NOT exist\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
