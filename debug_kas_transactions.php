<?php

// Debug kas transactions in detail
echo "<h1>Debug: Kas Account Transactions</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $today = '2026-04-08';
    
    echo "<h2>All Kas (112) Transactions for {$today}</h2>";
    
    // Get all journal lines for Kas account (112) on the date
    $stmt = $pdo->prepare("
        SELECT jl.id, jl.debit, jl.credit, jl.memo, 
               je.id as journal_id, je.ref_type, je.ref_id, je.memo as journal_memo, je.tanggal,
               CASE 
                   WHEN je.ref_type = 'sale' THEN (SELECT nomor_penjualan FROM penjualans WHERE id = je.ref_id)
                   WHEN je.ref_type = 'purchase' THEN (SELECT nomor_pembelian FROM pembelians WHERE id = je.ref_id)
                   ELSE NULL
               END as nomor_transaksi,
               CASE 
                   WHEN je.ref_type = 'sale' THEN (SELECT COUNT(*) FROM penjualans WHERE id = je.ref_id)
                   WHEN je.ref_type = 'purchase' THEN (SELECT COUNT(*) FROM pembelians WHERE id = je.ref_id)
                   ELSE 1
               END as ref_exists
        FROM journal_lines jl
        JOIN journal_entries je ON jl.journal_entry_id = je.id
        JOIN coas c ON jl.coa_id = c.id
        WHERE c.kode_akun = '112' AND DATE(je.tanggal) = ?
        ORDER BY je.tanggal, je.id, jl.id
    ");
    $stmt->execute([$today]);
    $kasTransactions = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Total Kas (112) journal lines: " . count($kasTransactions) . "</p>";
    
    if (count($kasTransactions) > 0) {
        echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
        echo "<tr style='background:#f0f0f0;'>";
        echo "<th>Line ID</th><th>Journal ID</th><th>Ref Type</th><th>Ref ID</th><th>Nomor</th>";
        echo "<th>Debit (Masuk)</th><th>Credit (Keluar)</th><th>Memo</th><th>Ref Exists?</th><th>Action</th>";
        echo "</tr>";
        
        $totalDebit = 0;
        $totalCredit = 0;
        $orphanCount = 0;
        
        foreach ($kasTransactions as $tx) {
            $isOrphan = $tx->ref_exists == 0;
            $rowColor = $isOrphan ? 'background:#ffcccc;' : '';
            
            echo "<tr style='{$rowColor}'>";
            echo "<td>{$tx->id}</td>";
            echo "<td>{$tx->journal_id}</td>";
            echo "<td>{$tx->ref_type}</td>";
            echo "<td>{$tx->ref_id}</td>";
            echo "<td>" . ($tx->nomor_transaksi ?? 'N/A') . "</td>";
            echo "<td>Rp " . number_format($tx->debit, 0, ',', '.') . "</td>";
            echo "<td>Rp " . number_format($tx->credit, 0, ',', '.') . "</td>";
            echo "<td>{$tx->memo}</td>";
            echo "<td style='color:" . ($isOrphan ? 'red' : 'green') . ";'>" . ($isOrphan ? 'NO' : 'YES') . "</td>";
            echo "<td>" . ($isOrphan ? '🗑️ DELETE' : '✅ KEEP') . "</td>";
            echo "</tr>";
            
            $totalDebit += $tx->debit;
            $totalCredit += $tx->credit;
            
            if ($isOrphan) {
                $orphanCount++;
            }
        }
        
        echo "<tr style='background:#e0e0e0; font-weight:bold;'>";
        echo "<td colspan='5'>TOTAL</td>";
        echo "<td>Rp " . number_format($totalDebit, 0, ',', '.') . "</td>";
        echo "<td>Rp " . number_format($totalCredit, 0, ',', '.') . "</td>";
        echo "<td colspan='3'>Net: Rp " . number_format($totalDebit - $totalCredit, 0, ',', '.') . "</td>";
        echo "</tr>";
        echo "</table>";
        
        echo "<h3>Summary</h3>";
        echo "<p>Total Debit (Uang Masuk): <strong>Rp " . number_format($totalDebit, 0, ',', '.') . "</strong></p>";
        echo "<p>Total Credit (Uang Keluar): <strong>Rp " . number_format($totalCredit, 0, ',', '.') . "</strong></p>";
        echo "<p>Orphan transactions: <strong style='color:red;'>{$orphanCount}</strong></p>";
        
        if ($orphanCount > 0) {
            echo "<h3>🧹 Cleanup Orphan Transactions</h3>";
            echo "<p><a href='#' onclick='cleanupOrphans()' style='background:red;color:white;padding:10px;text-decoration:none;'>Delete {$orphanCount} Orphan Transactions</a></p>";
            
            echo "<script>
            function cleanupOrphans() {
                if (confirm('Are you sure you want to delete {$orphanCount} orphan transactions?')) {
                    window.location.href = '/quick-fix-kas-bank';
                }
            }
            </script>";
        }
        
    } else {
        echo "<p>No Kas transactions found for {$today}</p>";
    }
    
    echo "<h2>Expected vs Actual</h2>";
    
    // Get expected amounts
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total FROM penjualans WHERE DATE(tanggal) = ?");
    $stmt->execute([$today]);
    $expectedSales = $stmt->fetch(PDO::FETCH_OBJ);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total_harga), 0) as total FROM pembelians WHERE DATE(tanggal) = ? AND payment_method IN ('cash', 'transfer') AND (bank_id IS NULL OR bank_id IN (SELECT id FROM coas WHERE kode_akun = '112'))");
    $stmt->execute([$today]);
    $expectedPurchases = $stmt->fetch(PDO::FETCH_OBJ);
    
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'><th>Type</th><th>Expected</th><th>Actual in Journal</th><th>Difference</th></tr>";
    
    $salesDiff = $totalDebit - $expectedSales->total;
    $purchasesDiff = $totalCredit - $expectedPurchases->total;
    
    echo "<tr>";
    echo "<td>Sales (Debit)</td>";
    echo "<td>Rp " . number_format($expectedSales->total, 0, ',', '.') . "</td>";
    echo "<td>Rp " . number_format($totalDebit, 0, ',', '.') . "</td>";
    echo "<td style='color:" . ($salesDiff > 0 ? 'red' : 'green') . ";'>Rp " . number_format($salesDiff, 0, ',', '.') . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td>Purchases (Credit)</td>";
    echo "<td>Rp " . number_format($expectedPurchases->total, 0, ',', '.') . "</td>";
    echo "<td>Rp " . number_format($totalCredit, 0, ',', '.') . "</td>";
    echo "<td style='color:" . ($purchasesDiff < 0 ? 'red' : 'green') . ";'>Rp " . number_format($purchasesDiff, 0, ',', '.') . "</td>";
    echo "</tr>";
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Actions:</strong></p>";
echo "<p><a href='/quick-fix-kas-bank' style='background:blue;color:white;padding:10px;text-decoration:none;'>🔧 Run Quick Fix</a></p>";
echo "<p><a href='/laporan/kas-bank' style='background:green;color:white;padding:10px;text-decoration:none;'>📊 Check Report</a></p>";
?>