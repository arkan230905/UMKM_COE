<?php

// Fix pembelian journal creation issues
echo "<h1>Fix Pembelian Journal Issues</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Step 1: Analyze All Pembelian Records</h2>";
    
    // Get all pembelian records
    $stmt = $pdo->query("
        SELECT p.id, p.nomor_pembelian, p.tanggal, p.total_harga, p.payment_method, p.bank_id,
               v.nama_vendor,
               (SELECT COUNT(*) FROM journal_entries WHERE ref_type = 'purchase' AND ref_id = p.id) as journal_count,
               (SELECT COUNT(*) FROM pembelian_details WHERE pembelian_id = p.id) as detail_count
        FROM pembelians p
        LEFT JOIN vendors v ON p.vendor_id = v.id
        ORDER BY p.tanggal DESC, p.id DESC
        LIMIT 20
    ");
    $pembelians = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    echo "<p>Found " . count($pembelians) . " recent pembelian records:</p>";
    
    echo "<table border='1' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'>";
    echo "<th>ID</th><th>Nomor</th><th>Tanggal</th><th>Vendor</th><th>Total</th>";
    echo "<th>Method</th><th>Bank ID</th><th>Details</th><th>Journals</th><th>Status</th>";
    echo "</tr>";
    
    $needsJournal = [];
    
    foreach ($pembelians as $p) {
        $status = 'OK';
        $statusColor = 'green';
        
        if ($p->detail_count == 0) {
            $status = 'NO DETAILS';
            $statusColor = 'orange';
        } elseif ($p->journal_count == 0) {
            $status = 'NO JOURNAL';
            $statusColor = 'red';
            $needsJournal[] = $p;
        } elseif ($p->journal_count > 1) {
            $status = 'DUPLICATE JOURNALS';
            $statusColor = 'purple';
        }
        
        echo "<tr>";
        echo "<td>{$p->id}</td>";
        echo "<td>{$p->nomor_pembelian}</td>";
        echo "<td>{$p->tanggal}</td>";
        echo "<td>{$p->nama_vendor}</td>";
        echo "<td>Rp " . number_format($p->total_harga, 0, ',', '.') . "</td>";
        echo "<td>{$p->payment_method}</td>";
        echo "<td>{$p->bank_id}</td>";
        echo "<td>{$p->detail_count}</td>";
        echo "<td>{$p->journal_count}</td>";
        echo "<td style='color:{$statusColor};'><strong>{$status}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Step 2: Create Missing Journals</h2>";
    
    if (count($needsJournal) > 0) {
        echo "<p>Found " . count($needsJournal) . " pembelian records without journals. Creating them now...</p>";
        
        foreach ($needsJournal as $pembelian) {
            echo "<h3>Creating Journal for Pembelian #{$pembelian->id}</h3>";
            
            // Get pembelian details
            $stmt = $pdo->prepare("
                SELECT pd.*, 
                       bb.nama_bahan as bahan_baku_nama,
                       bp.nama_bahan as bahan_pendukung_nama,
                       CASE 
                           WHEN pd.bahan_baku_id IS NOT NULL THEN 'bahan_baku'
                           WHEN pd.bahan_pendukung_id IS NOT NULL THEN 'bahan_pendukung'
                           ELSE 'unknown'
                       END as tipe_item
                FROM pembelian_details pd
                LEFT JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
                LEFT JOIN bahan_pendukungs bp ON pd.bahan_pendukung_id = bp.id
                WHERE pd.pembelian_id = ?
            ");
            $stmt->execute([$pembelian->id]);
            $details = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            if (count($details) == 0) {
                echo "<p style='color:red;'>❌ No details found for pembelian #{$pembelian->id} - skipping</p>";
                continue;
            }
            
            echo "<p>Found " . count($details) . " detail items:</p>";
            foreach ($details as $detail) {
                $itemName = $detail->bahan_baku_nama ?? $detail->bahan_pendukung_nama ?? 'Unknown';
                $subtotal = $detail->jumlah * $detail->harga_satuan;
                echo "<p>- {$detail->tipe_item}: {$itemName} - {$detail->jumlah} x Rp " . number_format($detail->harga_satuan, 0, ',', '.') . " = Rp " . number_format($subtotal, 0, ',', '.') . "</p>";
            }
            
            $pdo->beginTransaction();
            
            try {
                // Create journal entry
                $stmt = $pdo->prepare("
                    INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
                    VALUES (?, 'purchase', ?, ?, NOW(), NOW())
                ");
                $memo = "Pembelian {$pembelian->nomor_pembelian} - {$pembelian->nama_vendor}";
                $stmt->execute([$pembelian->tanggal, $pembelian->id, $memo]);
                $journalEntryId = $pdo->lastInsertId();
                
                echo "<p>✅ Created journal entry ID: {$journalEntryId}</p>";
                
                // Create journal lines for each detail (Debit Persediaan)
                $totalInventory = 0;
                foreach ($details as $detail) {
                    $amount = $detail->jumlah * $detail->harga_satuan;
                    $totalInventory += $amount;
                    
                    // Determine COA for inventory
                    $coaCode = '1104'; // Default: Persediaan Bahan Baku
                    if ($detail->tipe_item === 'bahan_pendukung') {
                        $coaCode = '1105'; // Persediaan Bahan Pendukung
                    }
                    
                    // Get COA ID
                    $stmt2 = $pdo->prepare("SELECT id, nama_akun FROM coas WHERE kode_akun = ?");
                    $stmt2->execute([$coaCode]);
                    $coa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($coa) {
                        // Create debit line for inventory
                        $stmt3 = $pdo->prepare("
                            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                            VALUES (?, ?, ?, 0, ?, NOW(), NOW())
                        ");
                        $itemName = $detail->bahan_baku_nama ?? $detail->bahan_pendukung_nama ?? 'Item';
                        $lineMemo = "Pembelian {$itemName}";
                        $stmt3->execute([$journalEntryId, $coa->id, $amount, $lineMemo]);
                        
                        echo "<p>  - Debit {$coaCode} ({$coa->nama_akun}): Rp " . number_format($amount, 0, ',', '.') . "</p>";
                    } else {
                        throw new Exception("COA {$coaCode} not found");
                    }
                }
                
                // Add PPN if exists
                if (($pembelian->ppn_nominal ?? 0) > 0) {
                    $stmt2 = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '1130'"); // PPN Masukan
                    $stmt2->execute();
                    $ppnCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($ppnCoa) {
                        $stmt3 = $pdo->prepare("
                            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                            VALUES (?, ?, ?, 0, 'PPN Masukan', NOW(), NOW())
                        ");
                        $stmt3->execute([$journalEntryId, $ppnCoa->id, $pembelian->ppn_nominal]);
                        
                        echo "<p>  - Debit PPN Masukan: Rp " . number_format($pembelian->ppn_nominal, 0, ',', '.') . "</p>";
                        $totalInventory += $pembelian->ppn_nominal;
                    }
                }
                
                // Add Biaya Kirim if exists
                if (($pembelian->biaya_kirim ?? 0) > 0) {
                    $stmt2 = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '511'"); // Biaya Kirim
                    $stmt2->execute();
                    $biayaCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($biayaCoa) {
                        $stmt3 = $pdo->prepare("
                            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                            VALUES (?, ?, ?, 0, 'Biaya kirim pembelian', NOW(), NOW())
                        ");
                        $stmt3->execute([$journalEntryId, $biayaCoa->id, $pembelian->biaya_kirim]);
                        
                        echo "<p>  - Debit Biaya Kirim: Rp " . number_format($pembelian->biaya_kirim, 0, ',', '.') . "</p>";
                        $totalInventory += $pembelian->biaya_kirim;
                    }
                }
                
                // Create credit line for payment (Kas/Bank/Hutang)
                $creditCoaId = null;
                $creditMemo = '';
                
                if ($pembelian->payment_method === 'credit') {
                    // Credit Hutang Usaha
                    $stmt2 = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '2101'");
                    $stmt2->execute();
                    $hutangCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                    
                    if ($hutangCoa) {
                        $creditCoaId = $hutangCoa->id;
                        $creditMemo = 'Hutang pembelian kredit';
                    }
                } else {
                    // Cash or Transfer - use bank_id if available
                    if ($pembelian->bank_id && $pembelian->bank_id !== 'credit') {
                        // bank_id is COA ID
                        $stmt2 = $pdo->prepare("SELECT id, nama_akun FROM coas WHERE id = ?");
                        $stmt2->execute([$pembelian->bank_id]);
                        $bankCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                        
                        if ($bankCoa) {
                            $creditCoaId = $bankCoa->id;
                            $creditMemo = "Pembayaran {$pembelian->payment_method} via {$bankCoa->nama_akun}";
                        }
                    } else {
                        // Default to Kas (112)
                        $stmt2 = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '112'");
                        $stmt2->execute();
                        $kasCoa = $stmt2->fetch(PDO::FETCH_OBJ);
                        
                        if ($kasCoa) {
                            $creditCoaId = $kasCoa->id;
                            $creditMemo = "Pembayaran {$pembelian->payment_method} pembelian";
                        }
                    }
                }
                
                if ($creditCoaId) {
                    // Create credit line
                    $stmt3 = $pdo->prepare("
                        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
                        VALUES (?, ?, 0, ?, ?, NOW(), NOW())
                    ");
                    $stmt3->execute([$journalEntryId, $creditCoaId, $pembelian->total_harga, $creditMemo]);
                    
                    echo "<p>  - Credit: Rp " . number_format($pembelian->total_harga, 0, ',', '.') . " ({$creditMemo})</p>";
                } else {
                    throw new Exception("Could not determine credit account");
                }
                
                $pdo->commit();
                echo "<p style='color:green;'>✅ Journal created successfully for pembelian #{$pembelian->id}</p>";
                
            } catch (Exception $e) {
                $pdo->rollback();
                echo "<p style='color:red;'>❌ Error creating journal for pembelian #{$pembelian->id}: " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<p style='color:green;'>✅ All pembelian records already have journals!</p>";
    }
    
    echo "<h2>Step 3: Disable Conflicting Journal Systems</h2>";
    echo "<p>⚠️ <strong>Important:</strong> There are multiple systems trying to create pembelian journals:</p>";
    echo "<ul>";
    echo "<li>PembelianController calls JournalService::createJournalFromPembelian()</li>";
    echo "<li>Pembelian Model boot() method also calls the same</li>";
    echo "<li>PembelianObserver creates journals independently</li>";
    echo "</ul>";
    echo "<p>This can cause conflicts or prevent journal creation. Consider keeping only one system active.</p>";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>✅ Fix Complete!</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<p>1. <a href='/transaksi/pembelian' style='background:blue;color:white;padding:10px;text-decoration:none;'>📋 Check Pembelian List</a></p>";
echo "<p>2. <a href='/laporan/kas-bank' style='background:green;color:white;padding:10px;text-decoration:none;'>📊 Check Kas & Bank Report</a></p>";
echo "<p>3. Click on any pembelian to verify journal is now created</p>";
?>