<?php

// Fix purchase journal creation conflicts
echo "<h1>Fix Purchase Journal Issues</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $today = '2026-04-08';
    
    echo "<h2>Step 1: Analyze Purchase vs Journal Mismatch</h2>";
    
    // Get purchases without journals
    $stmt = $pdo->prepare("
        SELECT p.id, p.nomor_pembelian, p.total_harga, p.payment_method, p.bank_id
        FROM pembelians p
        LEFT JOIN journal_entries je ON je.ref_type = 'purchase' AND je.ref_id = p.id
        WHERE DATE(p.tanggal) = ? AND je.id IS NULL
        ORDER BY p.id
    ");
    $stmt->execute([$today]);
    $purchasesWithoutJournals = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Purchases without journal entries: " . count($purchasesWithoutJournals) . "</p>";
    
    if (count($purchasesWithoutJournals) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Nomor</th><th>Total</th><th>Method</th><th>Bank ID</th><th>Action</th></tr>";
        
        foreach ($purchasesWithoutJournals as $purchase) {
            echo "<tr>";
            echo "<td>{$purchase->id}</td>";
            echo "<td>{$purchase->nomor_pembelian}</td>";
            echo "<td>Rp " . number_format($purchase->total_harga, 0, ',', '.') . "</td>";
            echo "<td>{$purchase->payment_method}</td>";
            echo "<td>{$purchase->bank_id}</td>";
            echo "<td>Need Journal</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>Step 2: Create Missing Journal Entries</h2>";
        
        foreach ($purchasesWithoutJournals as $purchase) {
            echo "<h3>Creating journal for Purchase #{$purchase->id}</h3>";
            
            // Get purchase details
            $stmt = $pdo->prepare("
                SELECT pd.*, 
                       bb.nama_bahan as bahan_baku_nama,
                       bp.nama_bahan as bahan_pendukung_nama
                FROM pembelian_details pd
                LEFT JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
                LEFT JOIN bahan_pendukungs bp ON pd.bahan_pendukung_id = bp.id
                WHERE pd.pembelian_id = ?
            ");
            $stmt->execute([$purchase->id]);
            $details = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if (count($details) == 0) {
                echo "<p style='color:red;'>❌ No details found for purchase #{$purchase->id}</p>";
                continue;
            }
            
            echo "<p>Found " . count($details) . " detail items</p>";
            
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
                
                echo "<p>✅ Created journal entry ID: {$journalEntryId}</p>";
                
                // Create journal lines for inventory (debit)
                $totalInventory = 0;
                foreach ($details as $detail) {
                    $amount = $detail->jumlah * $detail->harga_satuan;
                    $totalInventory += $amount;
                    
                    // Determine COA for inventory
                    $coaCode = '1104'; // Default: Persediaan Bahan Baku
                    $itemName = $detail->bahan_baku_nama ?? $detail->bahan_pendukung_nama ?? 'Item';
                    
                    if ($detail->bahan_pendukung_id) {
                        $coaCode = '1105'; // Persediaan Bahan Pendukung
                    }
                    
                    // Get COA ID
                    $stmt2 = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = ?");
                    $stmt2->execute([$coaCode]);
                    $coa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($coa) {
                        // Create debit line for inventory
                        $stmt3 = $pdo->prepare("
                            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                            VALUES (?, ?, ?, 0, ?, NOW(), NOW())
                        ");
                        $stmt3->execute([$journalEntryId, $coa->id, $amount, "Pembelian {$itemName}"]);
                        
                        echo "<p>  - Debit {$coaCode} ({$itemName}): Rp " . number_format($amount, 0, ',', '.') . "</p>";
                    }
                }
                
                // Create journal line for cash/bank (credit)
                $bankCoaId = null;
                $bankName = 'Unknown';
                
                if ($purchase->bank_id && $purchase->bank_id !== 'credit') {
                    // Get bank COA
                    $stmt2 = $pdo->prepare("SELECT id, nama_akun FROM coas WHERE id = ?");
                    $stmt2->execute([$purchase->bank_id]);
                    $bankCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($bankCoa) {
                        $bankCoaId = $bankCoa->id;
                        $bankName = $bankCoa->nama_akun;
                    }
                } else if ($purchase->payment_method === 'credit') {
                    // Use Hutang Usaha for credit purchases
                    $stmt2 = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '2101'");
                    $stmt2->execute();
                    $hutangCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($hutangCoa) {
                        $bankCoaId = $hutangCoa->id;
                        $bankName = 'Hutang Usaha';
                    }
                } else {
                    // Default to Kas (112)
                    $stmt2 = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '112'");
                    $stmt2->execute();
                    $kasCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($kasCoa) {
                        $bankCoaId = $kasCoa->id;
                        $bankName = 'Kas';
                    }
                }
                
                if ($bankCoaId) {
                    // Create credit line for payment
                    $stmt3 = $pdo->prepare("
                        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                        VALUES (?, ?, 0, ?, ?, NOW(), NOW())
                    ");
                    $paymentMemo = "Pembayaran {$purchase->payment_method} pembelian";
                    $stmt3->execute([$journalEntryId, $bankCoaId, $purchase->total_harga, $paymentMemo]);
                    
                    echo "<p>  - Credit {$bankName}: Rp " . number_format($purchase->total_harga, 0, ',', '.') . "</p>";
                } else {
                    echo "<p style='color:red;'>❌ Could not find COA for payment method</p>";
                }
                
                $pdo->commit();
                echo "<p style='color:green;'>✅ Journal created successfully for purchase #{$purchase->id}</p>";
                
            } catch (Exception $e) {
                $pdo->rollback();
                echo "<p style='color:red;'>❌ Error creating journal for purchase #{$purchase->id}: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color:green;'>✅ All purchases have journal entries</p>";
    }
    
    echo "<h2>Step 3: Disable Conflicting Journal Creation</h2>";
    echo "<p>⚠️ Multiple systems are creating purchase journals:</p>";
    echo "<ul>";
    echo "<li>PembelianController calls JournalService::createJournalFromPembelian()</li>";
    echo "<li>Pembelian Model boot() also calls the same method</li>";
    echo "<li>PembelianObserver creates journals too</li>";
    echo "</ul>";
    echo "<p>This can cause conflicts. Consider disabling one of them.</p>";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Next Steps:</strong></p>";
echo "<p>1. <a href='/laporan/kas-bank'>Check Kas & Bank Report</a></p>";
echo "<p>2. <a href='/comprehensive-cleanup'>Run Comprehensive Cleanup</a> for sales</p>";
?>