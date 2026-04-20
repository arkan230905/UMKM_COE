<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== MIGRATING PENGGAJIAN & PEMBAYARAN BEBAN TO MODERN JOURNAL ===\n\n";

try {
    // 1. Migrate penggajian to journal_entries
    echo "STEP 1: MIGRATING PENGGAJIAN\n";
    
    $stmt = $pdo->query("
        SELECT p.id, p.tanggal_penggajian, p.total_gaji, p.coa_kasbank, pg.nama
        FROM penggajians p
        LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
        WHERE NOT EXISTS (
            SELECT 1 FROM journal_entries je 
            WHERE je.ref_type = 'penggajian' 
            AND je.ref_id = p.id
        )
    ");
    
    $penggajianList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($penggajianList) . " penggajian records to migrate\n";
    
    foreach ($penggajianList as $penggajian) {
        // Create journal entry
        $stmt = $pdo->prepare("
            INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
            VALUES (?, 'penggajian', ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $penggajian['tanggal_penggajian'],
            $penggajian['id'],
            "Penggajian {$penggajian['nama']}"
        ]);
        
        $journalEntryId = $pdo->lastInsertId();
        
        // Get COA IDs
        $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '52' LIMIT 1");
        $stmt->execute();
        $coaBebanId = $stmt->fetchColumn();
        
        if (!$coaBebanId) {
            $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '54' LIMIT 1");
            $stmt->execute();
            $coaBebanId = $stmt->fetchColumn();
        }
        
        $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = ? LIMIT 1");
        $stmt->execute([$penggajian['coa_kasbank']]);
        $coaKasId = $stmt->fetchColumn();
        
        if (!$coaKasId) {
            $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1");
            $stmt->execute();
            $coaKasId = $stmt->fetchColumn();
        }
        
        // Create journal lines - DEBIT
        $stmt = $pdo->prepare("
            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
            VALUES (?, ?, ?, 0, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $journalEntryId,
            $coaBebanId,
            $penggajian['total_gaji'],
            "Beban Gaji {$penggajian['nama']}"
        ]);
        
        // Create journal lines - CREDIT
        $stmt = $pdo->prepare("
            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
            VALUES (?, ?, 0, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $journalEntryId,
            $coaKasId,
            $penggajian['total_gaji'],
            "Pembayaran Gaji {$penggajian['nama']}"
        ]);
        
        echo "✅ Migrated penggajian ID: {$penggajian['id']} - Rp " . number_format($penggajian['total_gaji'], 0, ',', '.') . "\n";
    }
    
    // 2. Migrate pembayaran beban to journal_entries
    echo "\nSTEP 2: MIGRATING PEMBAYARAN BEBAN\n";
    
    $stmt = $pdo->query("
        SELECT pb.id, pb.tanggal, pb.jumlah, pb.keterangan, bo.nama_beban
        FROM pembayaran_bebans pb
        LEFT JOIN beban_operasional bo ON pb.beban_operasional_id = bo.id
        WHERE NOT EXISTS (
            SELECT 1 FROM journal_entries je 
            WHERE je.ref_type = 'pembayaran_beban' 
            AND je.ref_id = pb.id
        )
    ");
    
    $bebanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($bebanList) . " pembayaran beban records to migrate\n";
    
    foreach ($bebanList as $beban) {
        // Create journal entry
        $stmt = $pdo->prepare("
            INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
            VALUES (?, 'pembayaran_beban', ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $beban['tanggal'],
            $beban['id'],
            'Pembayaran Beban: ' . ($beban['keterangan'] ?: 'Tanpa catatan')
        ]);
        
        $journalEntryId = $pdo->lastInsertId();
        
        // Get COA IDs
        $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '550' LIMIT 1");
        $stmt->execute();
        $coaBebanId = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1");
        $stmt->execute();
        $coaKasId = $stmt->fetchColumn();
        
        // Create journal lines - DEBIT
        $stmt = $pdo->prepare("
            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
            VALUES (?, ?, ?, 0, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $journalEntryId,
            $coaBebanId,
            $beban['jumlah'],
            'Pembayaran Beban'
        ]);
        
        // Create journal lines - CREDIT
        $stmt = $pdo->prepare("
            INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
            VALUES (?, ?, 0, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $journalEntryId,
            $coaKasId,
            $beban['jumlah'],
            'Pembayaran Beban'
        ]);
        
        echo "✅ Migrated pembayaran beban ID: {$beban['id']} - Rp " . number_format($beban['jumlah'], 0, ',', '.') . "\n";
    }
    
    // 3. Verification
    echo "\nSTEP 3: VERIFICATION\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries WHERE ref_type = 'penggajian'");
    $penggajianCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries WHERE ref_type = 'pembayaran_beban'");
    $bebanCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries");
    $totalCount = $stmt->fetchColumn();
    
    echo "Total journal_entries: $totalCount\n";
    echo "Penggajian in journal_entries: $penggajianCount\n";
    echo "Pembayaran Beban in journal_entries: $bebanCount\n";
    
    echo "\n✅ MIGRATION COMPLETE!\n";
    echo "Sekarang buka: http://127.0.0.1:8000/akuntansi/jurnal-umum\n";
    echo "Data penggajian dan pembayaran beban sudah muncul!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>