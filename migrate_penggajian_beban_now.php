<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=eadt_umkm;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== MIGRATING PENGGAJIAN ===\n";

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
    $stmt = $pdo->prepare("
        INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
        VALUES (?, 'penggajian', ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $penggajian['tanggal_penggajian'],
        $penggajian['id'],
        'Penggajian ' . $penggajian['nama']
    ]);
    
    $journalEntryId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '52' LIMIT 1");
    $stmt->execute();
    $coaBebanId = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = ? LIMIT 1");
    $stmt->execute([$penggajian['coa_kasbank']]);
    $coaKasId = $stmt->fetchColumn();
    
    if (!$coaBebanId) {
        $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '54' LIMIT 1");
        $stmt->execute();
        $coaBebanId = $stmt->fetchColumn();
    }
    
    if (!$coaKasId) {
        $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1");
        $stmt->execute();
        $coaKasId = $stmt->fetchColumn();
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
        VALUES (?, ?, ?, 0, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $journalEntryId,
        $coaBebanId,
        $penggajian['total_gaji'],
        'Beban Gaji ' . $penggajian['nama']
    ]);
    
    $stmt->execute([
        $journalEntryId,
        $coaKasId,
        0,
        $penggajian['total_gaji'],
        'Pembayaran Gaji ' . $penggajian['nama']
    ]);
    
    echo "✅ Migrated penggajian ID: " . $penggajian['id'] . "\n";
}

echo "\n=== MIGRATING PEMBAYARAN BEBAN ===\n";

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
    
    $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '550' LIMIT 1");
    $stmt->execute();
    $coaBebanId = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1");
    $stmt->execute();
    $coaKasId = $stmt->fetchColumn();
    
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
    
    $stmt->execute([
        $journalEntryId,
        $coaKasId,
        0,
        $beban['jumlah'],
        'Pembayaran Beban'
    ]);
    
    echo "✅ Migrated pembayaran beban ID: " . $beban['id'] . "\n";
}

echo "\n=== VERIFICATION ===\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries WHERE ref_type = 'penggajian'");
$penggajianCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM journal_entries WHERE ref_type = 'pembayaran_beban'");
$bebanCount = $stmt->fetchColumn();

echo "Penggajian in journal_entries: " . $penggajianCount . "\n";
echo "Pembayaran Beban in journal_entries: " . $bebanCount . "\n";
echo "\n✅ MIGRATION COMPLETE!\n";
