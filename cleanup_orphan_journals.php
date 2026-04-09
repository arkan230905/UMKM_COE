<?php

// Cleanup orphan journal entries for deleted sales
echo "<h1>Cleanup Orphan Journal Entries</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Finding Orphan Sale Journals</h2>";
    
    // Find journal entries that reference deleted sales
    $stmt = $pdo->query("
        SELECT je.id, je.ref_type, je.ref_id, je.memo, je.tanggal
        FROM journal_entries je
        LEFT JOIN penjualans p ON je.ref_id = p.id
        WHERE je.ref_type = 'sale' AND p.id IS NULL
        ORDER BY je.tanggal DESC
    ");
    
    $orphanJournals = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Found " . count($orphanJournals) . " orphan sale journals:</p>";
    
    if (count($orphanJournals) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>Journal ID</th><th>Date</th><th>Ref ID</th><th>Memo</th><th>Action</th></tr>";
        
        foreach ($orphanJournals as $journal) {
            echo "<tr>";
            echo "<td>{$journal->id}</td>";
            echo "<td>{$journal->tanggal}</td>";
            echo "<td>{$journal->ref_id}</td>";
            echo "<td>{$journal->memo}</td>";
            echo "<td>Will Delete</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>🗑️ Cleaning Up Orphan Journals</h2>";
        
        $pdo->beginTransaction();
        
        $deletedJournalLines = 0;
        $deletedJournalEntries = 0;
        
        foreach ($orphanJournals as $journal) {
            // Delete journal lines first
            $stmt = $pdo->prepare("DELETE FROM journal_lines WHERE journal_entry_id = ?");
            $stmt->execute([$journal->id]);
            $deletedJournalLines += $stmt->rowCount();
            
            // Delete journal entry
            $stmt = $pdo->prepare("DELETE FROM journal_entries WHERE id = ?");
            $stmt->execute([$journal->id]);
            $deletedJournalEntries += $stmt->rowCount();
            
            echo "<p>✅ Deleted journal entry ID {$journal->id} (ref_id: {$journal->ref_id})</p>";
        }
        
        $pdo->commit();
        
        echo "<h2>✅ Cleanup Summary</h2>";
        echo "<p>Deleted {$deletedJournalLines} journal lines</p>";
        echo "<p>Deleted {$deletedJournalEntries} journal entries</p>";
        
    } else {
        echo "<p>✅ No orphan sale journals found!</p>";
    }
    
    // Also check for other orphan types
    echo "<h2>Checking Other Orphan Journals</h2>";
    
    // Check purchase journals
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM journal_entries je
        LEFT JOIN pembelians p ON je.ref_id = p.id
        WHERE je.ref_type = 'purchase' AND p.id IS NULL
    ");
    $orphanPurchases = $stmt->fetch(PDO::FETCH_OBJ)->count;
    echo "<p>Orphan purchase journals: {$orphanPurchases}</p>";
    
    // Check production journals
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM journal_entries je
        LEFT JOIN produksis p ON je.ref_id = p.id
        WHERE je.ref_type = 'production' AND p.id IS NULL
    ");
    $orphanProductions = $stmt->fetch(PDO::FETCH_OBJ)->count;
    echo "<p>Orphan production journals: {$orphanProductions}</p>";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='/laporan/kas-bank'>Check Kas & Bank Report Now</a></p>";
?>