<?php

// Comprehensive cleanup for duplicate sales and orphan data
echo "<h1>Comprehensive Sales Cleanup</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Step 1: Find Remaining Duplicate Sales</h2>";
    
    // Check for any remaining duplicate sales
    $stmt = $pdo->query("
        SELECT nomor_penjualan, COUNT(*) as count, GROUP_CONCAT(id) as ids
        FROM penjualans 
        WHERE DATE(tanggal) = '2026-04-08' 
        GROUP BY nomor_penjualan 
        HAVING COUNT(*) > 1
        ORDER BY nomor_penjualan
    ");
    
    $duplicates = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (count($duplicates) > 0) {
        echo "<p>Found " . count($duplicates) . " duplicate sale numbers still in database:</p>";
        
        foreach ($duplicates as $dup) {
            echo "<p>- {$dup->nomor_penjualan}: {$dup->count} records (IDs: {$dup->ids})</p>";
        }
    } else {
        echo "<p>✅ No duplicate sales found in penjualans table</p>";
    }
    
    echo "<h2>Step 2: Find Orphan Journal Entries</h2>";
    
    // Find journal entries that reference non-existent sales
    $stmt = $pdo->query("
        SELECT je.id, je.ref_id, je.memo, je.tanggal,
               (SELECT COUNT(*) FROM journal_lines WHERE journal_entry_id = je.id) as line_count
        FROM journal_entries je
        LEFT JOIN penjualans p ON je.ref_id = p.id
        WHERE je.ref_type = 'sale' AND p.id IS NULL
        ORDER BY je.tanggal DESC
    ");
    
    $orphanJournals = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Found " . count($orphanJournals) . " orphan sale journal entries:</p>";
    
    if (count($orphanJournals) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>Journal ID</th><th>Date</th><th>Missing Sale ID</th><th>Memo</th><th>Lines</th></tr>";
        
        foreach ($orphanJournals as $journal) {
            echo "<tr>";
            echo "<td>{$journal->id}</td>";
            echo "<td>{$journal->tanggal}</td>";
            echo "<td>{$journal->ref_id}</td>";
            echo "<td>{$journal->memo}</td>";
            echo "<td>{$journal->line_count}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>Step 3: Cleanup Orphan Journals</h2>";
        
        $pdo->beginTransaction();
        
        $totalDeletedLines = 0;
        $totalDeletedEntries = 0;
        
        foreach ($orphanJournals as $journal) {
            // Delete journal lines
            $stmt = $pdo->prepare("DELETE FROM journal_lines WHERE journal_entry_id = ?");
            $stmt->execute([$journal->id]);
            $deletedLines = $stmt->rowCount();
            $totalDeletedLines += $deletedLines;
            
            // Delete journal entry
            $stmt = $pdo->prepare("DELETE FROM journal_entries WHERE id = ?");
            $stmt->execute([$journal->id]);
            $deletedEntries = $stmt->rowCount();
            $totalDeletedEntries += $deletedEntries;
            
            echo "<p>✅ Deleted journal {$journal->id} (ref_id: {$journal->ref_id}) - {$deletedLines} lines</p>";
        }
        
        $pdo->commit();
        
        echo "<h2>✅ Cleanup Complete!</h2>";
        echo "<p>Total deleted journal lines: {$totalDeletedLines}</p>";
        echo "<p>Total deleted journal entries: {$totalDeletedEntries}</p>";
        
    } else {
        echo "<p>✅ No orphan journal entries found!</p>";
    }
    
    echo "<h2>Step 4: Verify Current State</h2>";
    
    // Check current sales for today
    $stmt = $pdo->query("
        SELECT COUNT(*) as count, SUM(total) as total_amount
        FROM penjualans 
        WHERE DATE(tanggal) = '2026-04-08'
    ");
    $currentSales = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<p>Current sales for 2026-04-08:</p>";
    echo "<p>- Count: {$currentSales->count}</p>";
    echo "<p>- Total Amount: Rp " . number_format($currentSales->total_amount, 0, ',', '.') . "</p>";
    
    // Check current journal entries for sales
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM journal_entries 
        WHERE ref_type = 'sale' AND DATE(tanggal) = '2026-04-08'
    ");
    $currentJournals = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<p>Current sale journal entries for 2026-04-08: {$currentJournals->count}</p>";
    
    if ($currentSales->count == $currentJournals->count) {
        echo "<p style='color:green;'>✅ Sales and journals are now in sync!</p>";
    } else {
        echo "<p style='color:orange;'>⚠️ Sales ({$currentSales->count}) and journals ({$currentJournals->count}) count mismatch</p>";
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Next Steps:</strong></p>";
echo "<p>1. <a href='/laporan/kas-bank'>Check Kas & Bank Report</a></p>";
echo "<p>2. <a href='/transaksi/penjualan'>Check Sales List</a></p>";
echo "<p>3. Try creating a new sale to test the fix</p>";
?>