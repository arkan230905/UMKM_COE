<?php

// Quick fix for kas & bank report issues
echo "<h1>Quick Fix: Kas & Bank Report</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $today = '2026-04-08';
    
    echo "<h2>Problem Analysis</h2>";
    
    // Check current sales
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(total) as total FROM penjualans WHERE DATE(tanggal) = ?");
    $stmt->execute([$today]);
    $sales = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<p>Current sales in database: {$sales->count} records, Total: Rp " . number_format($sales->total ?? 0, 0, ',', '.') . "</p>";
    
    // Check sale journals
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, SUM(jl.debit) as total
        FROM journal_entries je
        JOIN journal_lines jl ON je.id = jl.journal_entry_id
        JOIN coas c ON jl.coa_id = c.id
        WHERE je.ref_type = 'sale' AND DATE(je.tanggal) = ? AND c.kode_akun = '112'
    ");
    $stmt->execute([$today]);
    $saleJournals = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<p>Sale journals in Kas (112): {$saleJournals->count} entries, Total: Rp " . number_format($saleJournals->total ?? 0, 0, ',', '.') . "</p>";
    
    // Check purchases
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(total_harga) as total FROM pembelians WHERE DATE(tanggal) = ?");
    $stmt->execute([$today]);
    $purchases = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<p>Current purchases in database: {$purchases->count} records, Total: Rp " . number_format($purchases->total ?? 0, 0, ',', '.') . "</p>";
    
    // Check purchase journals
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count, SUM(jl.credit) as total
        FROM journal_entries je
        JOIN journal_lines jl ON je.id = jl.journal_entry_id
        JOIN coas c ON jl.coa_id = c.id
        WHERE je.ref_type = 'purchase' AND DATE(je.tanggal) = ? AND c.kode_akun = '112'
    ");
    $stmt->execute([$today]);
    $purchaseJournals = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<p>Purchase journals in Kas (112): {$purchaseJournals->count} entries, Total: Rp " . number_format($purchaseJournals->total ?? 0, 0, ',', '.') . "</p>";
    
    echo "<h2>Issues Found</h2>";
    
    $issuesFound = false;
    
    // Issue 1: Excess sale journals
    if (($saleJournals->total ?? 0) > ($sales->total ?? 0)) {
        $excess = ($saleJournals->total ?? 0) - ($sales->total ?? 0);
        echo "<p style='color:red;'>❌ Issue 1: Excess sale journals = Rp " . number_format($excess, 0, ',', '.') . "</p>";
        $issuesFound = true;
    }
    
    // Issue 2: Missing purchase journals
    if (($purchases->total ?? 0) > ($purchaseJournals->total ?? 0)) {
        $missing = ($purchases->total ?? 0) - ($purchaseJournals->total ?? 0);
        echo "<p style='color:red;'>❌ Issue 2: Missing purchase journals = Rp " . number_format($missing, 0, ',', '.') . "</p>";
        $issuesFound = true;
    }
    
    if (!$issuesFound) {
        echo "<p style='color:green;'>✅ No issues found!</p>";
        echo "<p><a href='/laporan/kas-bank'>Check Kas & Bank Report</a></p>";
        exit;
    }
    
    echo "<h2>Fix 1: Remove Orphan Sale Journals</h2>";
    
    // Find and remove orphan sale journals
    $stmt = $pdo->query("
        SELECT je.id, je.ref_id, je.memo
        FROM journal_entries je
        LEFT JOIN penjualans p ON je.ref_id = p.id
        WHERE je.ref_type = 'sale' AND DATE(je.tanggal) = '$today' AND p.id IS NULL
    ");
    $orphanSaleJournals = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (count($orphanSaleJournals) > 0) {
        echo "<p>Found " . count($orphanSaleJournals) . " orphan sale journals to remove:</p>";
        
        $pdo->beginTransaction();
        
        foreach ($orphanSaleJournals as $journal) {
            // Delete journal lines
            $stmt = $pdo->prepare("DELETE FROM journal_lines WHERE journal_entry_id = ?");
            $stmt->execute([$journal->id]);
            $deletedLines = $stmt->rowCount();
            
            // Delete journal entry
            $stmt = $pdo->prepare("DELETE FROM journal_entries WHERE id = ?");
            $stmt->execute([$journal->id]);
            
            echo "<p>✅ Deleted orphan journal {$journal->id} (ref_id: {$journal->ref_id}) - {$deletedLines} lines</p>";
        }
        
        $pdo->commit();
        echo "<p style='color:green;'>✅ Orphan sale journals cleaned up!</p>";
    } else {
        echo "<p>No orphan sale journals found.</p>";
    }
    
    echo "<h2>Fix 2: Create Missing Purchase Journals</h2>";
    
    // Find purchases without journals
    $stmt = $pdo->query("
        SELECT p.id, p.nomor_pembelian, p.total_harga, p.payment_method, p.bank_id
        FROM pembelians p
        LEFT JOIN journal_entries je ON je.ref_type = 'purchase' AND je.ref_id = p.id
        WHERE DATE(p.tanggal) = '$today' AND je.id IS NULL
    ");
    $purchasesWithoutJournals = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    if (count($purchasesWithoutJournals) > 0) {
        echo "<p>Found " . count($purchasesWithoutJournals) . " purchases without journals:</p>";
        
        foreach ($purchasesWithoutJournals as $purchase) {
            echo "<p>Creating journal for Purchase #{$purchase->id} - Rp " . number_format($purchase->total_harga, 0, ',', '.') . "</p>";
            
            $pdo->beginTransaction();
            
            try {
                // Create journal entry
                $stmt = $pdo->prepare("
                    INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
                    VALUES (?, 'purchase', ?, ?, NOW(), NOW())
                ");
                $memo = "Pembelian {$purchase->nomor_pembelian}";
                $stmt->execute([$today, $purchase->id, $memo]);
                $journalEntryId = $pdo->lastInsertId();
                
                // Get COA IDs
                $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '1104'"); // Persediaan
                $stmt->execute();
                $persediaanCoaId = $stmt->fetch(PDO::FETCH_OBJ)->id ?? null;
                
                $kasCoaId = null;
                if ($purchase->bank_id && $purchase->bank_id !== 'credit') {
                    $kasCoaId = $purchase->bank_id; // bank_id is COA ID
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '112'"); // Kas
                    $stmt->execute();
                    $kasCoaId = $stmt->fetch(PDO::FETCH_OBJ)->id ?? null;
                }
                
                if ($persediaanCoaId && $kasCoaId) {
                    // Debit Persediaan
                    $stmt = $pdo->prepare("
                        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                        VALUES (?, ?, ?, 0, 'Pembelian bahan', NOW(), NOW())
                    ");
                    $stmt->execute([$journalEntryId, $persediaanCoaId, $purchase->total_harga]);
                    
                    // Credit Kas/Bank
                    $stmt = $pdo->prepare("
                        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                        VALUES (?, ?, 0, ?, 'Pembayaran pembelian', NOW(), NOW())
                    ");
                    $stmt->execute([$journalEntryId, $kasCoaId, $purchase->total_harga]);
                    
                    $pdo->commit();
                    echo "<p style='color:green;'>✅ Journal created for purchase #{$purchase->id}</p>";
                } else {
                    $pdo->rollback();
                    echo "<p style='color:red;'>❌ Could not find required COA accounts</p>";
                }
                
            } catch (Exception $e) {
                $pdo->rollback();
                echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p>No purchases without journals found.</p>";
    }
    
    echo "<h2>✅ Fix Complete!</h2>";
    echo "<p>The Kas & Bank report should now show correct amounts.</p>";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong><a href='/laporan/kas-bank' style='background:green;color:white;padding:10px;text-decoration:none;'>📊 Check Kas & Bank Report Now</a></strong></p>";
?>