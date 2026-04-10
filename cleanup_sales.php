<?php

// Simple cleanup script
echo "Cleanup duplicate sales for 2026-04-08\n";

// Connect to database
$host = 'localhost';
$dbname = 'umkm_coe';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Find duplicates
    $stmt = $pdo->query("
        SELECT nomor_penjualan, COUNT(*) as count 
        FROM penjualans 
        WHERE DATE(tanggal) = '2026-04-08' 
        GROUP BY nomor_penjualan 
        HAVING COUNT(*) > 1
        ORDER BY nomor_penjualan
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "Found " . count($duplicates) . " duplicate sale numbers:\n";
    
    foreach ($duplicates as $dup) {
        echo "- {$dup->nomor_penjualan}: {$dup->count} records\n";
        
        // Get all sales with same number
        $stmt2 = $pdo->prepare("
            SELECT id FROM penjualans 
            WHERE nomor_penjualan = ? 
            ORDER BY id
        ");
        $stmt2->execute([$dup->nomor_penjualan]);
        $sales = $stmt2->fetchAll(PDO::FETCH_OBJ);
        
        // Keep first, delete others
        $keepFirst = array_shift($sales);
        echo "  Keeping sale ID: {$keepFirst->id}\n";
        
        foreach ($sales as $sale) {
            echo "  Deleting sale ID: {$sale->id}\n";
            
            $pdo->beginTransaction();
            try {
                // Delete journal lines first
                $pdo->prepare("
                    DELETE jl FROM journal_lines jl 
                    JOIN journal_entries je ON jl.journal_entry_id = je.id 
                    WHERE je.ref_type = 'sale' AND je.ref_id = ?
                ")->execute([$sale->id]);
                
                // Delete journal entries
                $pdo->prepare("
                    DELETE FROM journal_entries 
                    WHERE ref_type = 'sale' AND ref_id = ?
                ")->execute([$sale->id]);
                
                // Delete penjualan details
                $pdo->prepare("
                    DELETE FROM penjualan_details 
                    WHERE penjualan_id = ?
                ")->execute([$sale->id]);
                
                // Delete stock movements
                $pdo->prepare("
                    DELETE FROM stock_movements 
                    WHERE ref_type = 'sale' AND ref_id = ?
                ")->execute([$sale->id]);
                
                // Delete penjualan
                $pdo->prepare("
                    DELETE FROM penjualans 
                    WHERE id = ?
                ")->execute([$sale->id]);
                
                $pdo->commit();
                
            } catch (Exception $e) {
                $pdo->rollback();
                echo "    Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nCleanup completed!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}