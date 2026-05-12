<?php

// Analyze journal issues for kas & bank report
echo "<h1>Journal Analysis for Kas & Bank Report</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $today = '2026-04-08';
    
    echo "<h2>1. Sales Analysis for {$today}</h2>";
    
    // Check actual sales
    $stmt = $pdo->prepare("
        SELECT id, nomor_penjualan, total, payment_method, created_at
        FROM penjualans 
        WHERE DATE(tanggal) = ?
        ORDER BY id
    ");
    $stmt->execute([$today]);
    $sales = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Actual sales in database: " . count($sales) . "</p>";
    
    if (count($sales) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Nomor</th><th>Total</th><th>Method</th><th>Created</th></tr>";
        
        $totalSalesAmount = 0;
        foreach ($sales as $sale) {
            echo "<tr>";
            echo "<td>{$sale->id}</td>";
            echo "<td>{$sale->nomor_penjualan}</td>";
            echo "<td>Rp " . number_format($sale->total, 0, ',', '.') . "</td>";
            echo "<td>{$sale->payment_method}</td>";
            echo "<td>{$sale->created_at}</td>";
            echo "</tr>";
            $totalSalesAmount += $sale->total;
        }
        echo "</table>";
        echo "<p><strong>Total Sales Amount: Rp " . number_format($totalSalesAmount, 0, ',', '.') . "</strong></p>";
    }
    
    // Check sale journal entries
    $stmt = $pdo->prepare("
        SELECT je.id, je.ref_id, je.memo, je.tanggal,
               (SELECT COUNT(*) FROM journal_lines WHERE journal_entry_id = je.id) as line_count,
               (SELECT SUM(debit) FROM journal_lines WHERE journal_entry_id = je.id) as total_debit
        FROM journal_entries je
        WHERE je.ref_type = 'sale' AND DATE(je.tanggal) = ?
        ORDER BY je.id
    ");
    $stmt->execute([$today]);
    $saleJournals = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Sale journal entries: " . count($saleJournals) . "</p>";
    
    if (count($saleJournals) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>Journal ID</th><th>Ref ID</th><th>Memo</th><th>Lines</th><th>Total Debit</th><th>Sale Exists?</th></tr>";
        
        $totalJournalAmount = 0;
        foreach ($saleJournals as $journal) {
            // Check if sale still exists
            $stmt2 = $pdo->prepare("SELECT id FROM penjualans WHERE id = ?");
            $stmt2->execute([$journal->ref_id]);
            $saleExists = $stmt2->fetch() ? 'Yes' : 'No';
            
            echo "<tr>";
            echo "<td>{$journal->id}</td>";
            echo "<td>{$journal->ref_id}</td>";
            echo "<td>{$journal->memo}</td>";
            echo "<td>{$journal->line_count}</td>";
            echo "<td>Rp " . number_format($journal->total_debit, 0, ',', '.') . "</td>";
            echo "<td style='color:" . ($saleExists == 'Yes' ? 'green' : 'red') . ";'>{$saleExists}</td>";
            echo "</tr>";
            $totalJournalAmount += $journal->total_debit;
        }
        echo "</table>";
        echo "<p><strong>Total Journal Amount: Rp " . number_format($totalJournalAmount, 0, ',', '.') . "</strong></p>";
    }
    
    echo "<h2>2. Purchase Analysis for {$today}</h2>";
    
    // Check actual purchases
    $stmt = $pdo->prepare("
        SELECT id, nomor_pembelian, total_harga, payment_method, bank_id, created_at
        FROM pembelians 
        WHERE DATE(tanggal) = ?
        ORDER BY id
    ");
    $stmt->execute([$today]);
    $purchases = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Actual purchases in database: " . count($purchases) . "</p>";
    
    if (count($purchases) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Nomor</th><th>Total</th><th>Method</th><th>Bank ID</th><th>Created</th></tr>";
        
        $totalPurchaseAmount = 0;
        foreach ($purchases as $purchase) {
            echo "<tr>";
            echo "<td>{$purchase->id}</td>";
            echo "<td>{$purchase->nomor_pembelian}</td>";
            echo "<td>Rp " . number_format($purchase->total_harga, 0, ',', '.') . "</td>";
            echo "<td>{$purchase->payment_method}</td>";
            echo "<td>{$purchase->bank_id}</td>";
            echo "<td>{$purchase->created_at}</td>";
            echo "</tr>";
            $totalPurchaseAmount += $purchase->total_harga;
        }
        echo "</table>";
        echo "<p><strong>Total Purchase Amount: Rp " . number_format($totalPurchaseAmount, 0, ',', '.') . "</strong></p>";
    }
    
    // Check purchase journal entries
    $stmt = $pdo->prepare("
        SELECT je.id, je.ref_id, je.memo, je.tanggal,
               (SELECT COUNT(*) FROM journal_lines WHERE journal_entry_id = je.id) as line_count,
               (SELECT SUM(credit) FROM journal_lines WHERE journal_entry_id = je.id) as total_credit
        FROM journal_entries je
        WHERE je.ref_type = 'purchase' AND DATE(je.tanggal) = ?
        ORDER BY je.id
    ");
    $stmt->execute([$today]);
    $purchaseJournals = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Purchase journal entries: " . count($purchaseJournals) . "</p>";
    
    if (count($purchaseJournals) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>Journal ID</th><th>Ref ID</th><th>Memo</th><th>Lines</th><th>Total Credit</th><th>Purchase Exists?</th></tr>";
        
        $totalPurchaseJournalAmount = 0;
        foreach ($purchaseJournals as $journal) {
            // Check if purchase still exists
            $stmt2 = $pdo->prepare("SELECT id FROM pembelians WHERE id = ?");
            $stmt2->execute([$journal->ref_id]);
            $purchaseExists = $stmt2->fetch() ? 'Yes' : 'No';
            
            echo "<tr>";
            echo "<td>{$journal->id}</td>";
            echo "<td>{$journal->ref_id}</td>";
            echo "<td>{$journal->memo}</td>";
            echo "<td>{$journal->line_count}</td>";
            echo "<td>Rp " . number_format($journal->total_credit, 0, ',', '.') . "</td>";
            echo "<td style='color:" . ($purchaseExists == 'Yes' ? 'green' : 'red') . ";'>{$purchaseExists}</td>";
            echo "</tr>";
            $totalPurchaseJournalAmount += $journal->total_credit;
        }
        echo "</table>";
        echo "<p><strong>Total Purchase Journal Amount: Rp " . number_format($totalPurchaseJournalAmount, 0, ',', '.') . "</strong></p>";
    }
    
    echo "<h2>3. Kas Account (112) Analysis</h2>";
    
    // Check journal lines for Kas account (112)
    $stmt = $pdo->prepare("
        SELECT jl.debit, jl.credit, jl.memo, je.tanggal, je.ref_type, je.ref_id
        FROM journal_lines jl
        JOIN journal_entries je ON jl.journal_entry_id = je.id
        JOIN coas c ON jl.coa_id = c.id
        WHERE c.kode_akun = '112' AND DATE(je.tanggal) = ?
        ORDER BY je.tanggal, je.id
    ");
    $stmt->execute([$today]);
    $kasLines = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Kas (112) journal lines for {$today}: " . count($kasLines) . "</p>";
    
    if (count($kasLines) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>Date</th><th>Ref Type</th><th>Ref ID</th><th>Debit</th><th>Credit</th><th>Memo</th></tr>";
        
        $totalKasDebit = 0;
        $totalKasCredit = 0;
        
        foreach ($kasLines as $line) {
            echo "<tr>";
            echo "<td>{$line->tanggal}</td>";
            echo "<td>{$line->ref_type}</td>";
            echo "<td>{$line->ref_id}</td>";
            echo "<td>Rp " . number_format($line->debit, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($line->credit, 0, ',', '.') . "</td>";
            echo "<td>{$line->memo}</td>";
            echo "</tr>";
            
            $totalKasDebit += $line->debit;
            $totalKasCredit += $line->credit;
        }
        echo "</table>";
        echo "<p><strong>Total Kas Debit (Masuk): Rp " . number_format($totalKasDebit, 0, ',', '.') . "</strong></p>";
        echo "<p><strong>Total Kas Credit (Keluar): Rp " . number_format($totalKasCredit, 0, ',', '.') . "</strong></p>";
    }
    
    echo "<h2>4. Cleanup Actions</h2>";
    
    // Cleanup orphan sale journals
    if (count($saleJournals) > count($sales)) {
        echo "<p style='color:orange;'>⚠️ Found more sale journals than actual sales - cleanup needed</p>";
        echo "<p><a href='/comprehensive-cleanup' style='background:red;color:white;padding:10px;text-decoration:none;'>🧹 Run Comprehensive Cleanup</a></p>";
    } else {
        echo "<p style='color:green;'>✅ Sale journals match actual sales</p>";
    }
    
    // Check if purchase journals are missing
    if (count($purchases) > count($purchaseJournals)) {
        echo "<p style='color:orange;'>⚠️ Some purchases don't have journal entries - may need to recreate</p>";
    } else {
        echo "<p style='color:green;'>✅ Purchase journals exist for all purchases</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Next Steps:</strong></p>";
echo "<p>1. <a href='/comprehensive-cleanup'>Run Comprehensive Cleanup</a> if needed</p>";
echo "<p>2. <a href='/laporan/kas-bank'>Check Kas & Bank Report Again</a></p>";
?>