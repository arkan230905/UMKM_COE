<?php
// FINAL CLEANUP SCRIPT - Remove all old depreciation entries and insert correct ones

try {
    $pdo = new PDO('mysql:host=localhost;dbname=umkm_coe', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔥 FINAL TOTAL CLEANUP - REMOVING ALL OLD DEPRECIATION DATA\n\n";
    
    // Step 1: Check what exists
    echo "=== CHECKING EXISTING DATA ===\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries WHERE tanggal = '2026-04-30' AND memo LIKE '%Penyusutan%'");
    $journalEntries = $stmt->fetchColumn();
    echo "journal_entries with depreciation: $journalEntries\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM jurnal_umum WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Penyusutan%'");
    $jurnalUmum = $stmt->fetchColumn();
    echo "jurnal_umum with depreciation: $jurnalUmum\n";
    
    // Step 2: DELETE ALL depreciation entries from ALL tables
    echo "\n=== DELETING ALL OLD ENTRIES ===\n";
    
    $pdo->beginTransaction();
    
    // Delete from journal_lines first (foreign key constraint)
    $stmt = $pdo->prepare("
        DELETE jl FROM journal_lines jl 
        JOIN journal_entries je ON jl.journal_entry_id = je.id 
        WHERE je.tanggal = '2026-04-30' AND je.memo LIKE '%Penyusutan%'
    ");
    $stmt->execute();
    $deletedLines = $stmt->rowCount();
    echo "Deleted journal_lines: $deletedLines\n";
    
    // Delete from journal_entries
    $stmt = $pdo->prepare("DELETE FROM journal_entries WHERE tanggal = '2026-04-30' AND memo LIKE '%Penyusutan%'");
    $stmt->execute();
    $deletedEntries = $stmt->rowCount();
    echo "Deleted journal_entries: $deletedEntries\n";
    
    // Delete from jurnal_umum - ALL patterns
    $stmt = $pdo->prepare("
        DELETE FROM jurnal_umum 
        WHERE tanggal = '2026-04-30' 
        AND (
            keterangan LIKE '%Penyusutan%' 
            OR keterangan LIKE '%GL) 2026-04%'
            OR keterangan LIKE '%SM) 2026-04%' 
            OR keterangan LIKE '%SYD) 2026-04%'
        )
    ");
    $stmt->execute();
    $deletedJurnal = $stmt->rowCount();
    echo "Deleted jurnal_umum: $deletedJurnal\n";
    
    // Step 3: Verify cleanup
    echo "\n=== VERIFYING CLEANUP ===\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries WHERE tanggal = '2026-04-30' AND memo LIKE '%Penyusutan%'");
    $remaining1 = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM jurnal_umum WHERE tanggal = '2026-04-30' AND keterangan LIKE '%Penyusutan%'");
    $remaining2 = $stmt->fetchColumn();
    
    echo "Remaining journal_entries: $remaining1\n";
    echo "Remaining jurnal_umum: $remaining2\n";
    
    if ($remaining1 == 0 && $remaining2 == 0) {
        echo "✅ ALL CLEAN!\n";
        
        // Step 4: Insert CORRECT values
        echo "\n=== INSERTING CORRECT VALUES ===\n";
        echo "Mesin: Rp 1.333.333\n";
        echo "Peralatan: Rp 659.474 (CORRECTED from 2.833.333)\n";
        echo "Kendaraan: Rp 888.889\n\n";
        
        $correctEntries = [
            // Mesin Produksi - Rp 1.333.333
            [
                'coa_id' => 555,
                'tanggal' => '2026-04-30',
                'keterangan' => 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04',
                'debit' => 1333333,
                'kredit' => 0,
                'referensi' => 'AST-MESIN',
                'tipe_referensi' => 'depreciation',
                'created_by' => 1,
                'created_at' => '2026-04-30 00:00:00',
                'updated_at' => '2026-04-30 00:00:00'
            ],
            [
                'coa_id' => 126,
                'tanggal' => '2026-04-30',
                'keterangan' => 'Penyusutan Aset Mesin Produksi (garis_lurus) 2026-04',
                'debit' => 0,
                'kredit' => 1333333,
                'referensi' => 'AST-MESIN',
                'tipe_referensi' => 'depreciation',
                'created_by' => 1,
                'created_at' => '2026-04-30 00:00:00',
                'updated_at' => '2026-04-30 00:00:00'
            ],
            // Peralatan Produksi - Rp 659.474 (CORRECTED)
            [
                'coa_id' => 553,
                'tanggal' => '2026-04-30',
                'keterangan' => 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04',
                'debit' => 659474,
                'kredit' => 0,
                'referensi' => 'AST-PERALATAN',
                'tipe_referensi' => 'depreciation',
                'created_by' => 1,
                'created_at' => '2026-04-30 00:00:00',
                'updated_at' => '2026-04-30 00:00:00'
            ],
            [
                'coa_id' => 120,
                'tanggal' => '2026-04-30',
                'keterangan' => 'Penyusutan Aset Peralatan Produksi (saldo_menurun) 2026-04',
                'debit' => 0,
                'kredit' => 659474,
                'referensi' => 'AST-PERALATAN',
                'tipe_referensi' => 'depreciation',
                'created_by' => 1,
                'created_at' => '2026-04-30 00:00:00',
                'updated_at' => '2026-04-30 00:00:00'
            ],
            // Kendaraan - Rp 888.889
            [
                'coa_id' => 554,
                'tanggal' => '2026-04-30',
                'keterangan' => 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04',
                'debit' => 888889,
                'kredit' => 0,
                'referensi' => 'AST-KENDARAAN',
                'tipe_referensi' => 'depreciation',
                'created_by' => 1,
                'created_at' => '2026-04-30 00:00:00',
                'updated_at' => '2026-04-30 00:00:00'
            ],
            [
                'coa_id' => 124,
                'tanggal' => '2026-04-30',
                'keterangan' => 'Penyusutan Aset Kendaraan Pengangkut Barang (sum_of_years_digits) 2026-04',
                'debit' => 0,
                'kredit' => 888889,
                'referensi' => 'AST-KENDARAAN',
                'tipe_referensi' => 'depreciation',
                'created_by' => 1,
                'created_at' => '2026-04-30 00:00:00',
                'updated_at' => '2026-04-30 00:00:00'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($correctEntries as $entry) {
            $stmt->execute([
                $entry['coa_id'],
                $entry['tanggal'],
                $entry['keterangan'],
                $entry['debit'],
                $entry['kredit'],
                $entry['referensi'],
                $entry['tipe_referensi'],
                $entry['created_by'],
                $entry['created_at'],
                $entry['updated_at']
            ]);
        }
        
        echo "✅ Inserted " . count($correctEntries) . " correct entries\n";
        
    } else {
        echo "❌ CLEANUP FAILED - Still have remaining data\n";
        $pdo->rollback();
        exit(1);
    }
    
    $pdo->commit();
    
    echo "\n🎉 FINAL CLEANUP COMPLETE!\n";
    echo "✅ All old entries with (GL), (SM), (SYD) patterns DELETED\n";
    echo "✅ Correct depreciation values inserted:\n";
    echo "   - Mesin: Rp 1.333.333\n";
    echo "   - Peralatan: Rp 659.474\n";
    echo "   - Kendaraan: Rp 888.889\n";
    echo "\nCheck /akuntansi/jurnal-umum now!\n";
    
} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>