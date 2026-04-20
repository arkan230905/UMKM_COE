<?php
// PERBAIKAN LANGSUNG JURNAL PENGGAJIAN
// Database: eadt_umkm

$host = '127.0.0.1';
$dbname = 'eadt_umkm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== PERBAIKAN JURNAL PENGGAJIAN ===\n";
    echo "Connected to database: $dbname\n\n";
    
    // 1. Cek data penggajian saat ini
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM penggajians");
    $totalPenggajian = $stmt->fetch()['total'];
    echo "Total penggajian records: $totalPenggajian\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM penggajians WHERE status_pembayaran = 'belum_lunas'");
    $belumLunas = $stmt->fetch()['total'];
    echo "Penggajian belum lunas: $belumLunas\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jurnal_umum WHERE tipe_referensi = 'penggajian'");
    $jurnalPenggajian = $stmt->fetch()['total'];
    echo "Jurnal penggajian entries: $jurnalPenggajian\n\n";
    
    if ($belumLunas > 0) {
        echo "STEP 1: Updating penggajian status to 'lunas'...\n";
        
        // Update status penggajian
        $updateSql = "UPDATE penggajians SET 
                        status_pembayaran = 'lunas', 
                        tanggal_dibayar = tanggal_penggajian, 
                        updated_at = NOW() 
                      WHERE status_pembayaran = 'belum_lunas'";
        
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute();
        echo "✓ Updated " . $stmt->rowCount() . " penggajian records\n\n";
    }
    
    echo "STEP 2: Creating DEBIT journal entries...\n";
    
    // Buat DEBIT entries (Beban Gaji)
    $debitSql = "
    INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
    SELECT 
        COALESCE(
            (SELECT id FROM coas WHERE kode_akun = '52' LIMIT 1),
            (SELECT id FROM coas WHERE kode_akun = '54' LIMIT 1),
            (SELECT id FROM coas WHERE nama_akun LIKE '%gaji%' OR nama_akun LIKE '%tenaga kerja%' LIMIT 1)
        ) as coa_id,
        p.tanggal_penggajian as tanggal,
        CONCAT('Penggajian ', COALESCE(pg.nama, CONCAT('ID-', p.id))) as keterangan,
        p.total_gaji as debit,
        0 as kredit,
        p.id as referensi,
        'penggajian' as tipe_referensi,
        1 as created_by,
        NOW() as created_at,
        NOW() as updated_at
    FROM penggajians p
    LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
    WHERE NOT EXISTS (
        SELECT 1 FROM jurnal_umum ju 
        WHERE ju.tipe_referensi = 'penggajian' 
        AND ju.referensi = p.id 
        AND ju.debit > 0
    )
    ";
    
    $stmt = $pdo->prepare($debitSql);
    $stmt->execute();
    $debitCount = $stmt->rowCount();
    echo "✓ Created $debitCount DEBIT entries\n";
    
    echo "STEP 3: Creating CREDIT journal entries...\n";
    
    // Buat CREDIT entries (Kas/Bank)
    $creditSql = "
    INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
    SELECT 
        COALESCE(
            (SELECT id FROM coas WHERE kode_akun = COALESCE(p.coa_kasbank, '111') LIMIT 1),
            (SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1),
            (SELECT id FROM coas WHERE kode_akun = '112' LIMIT 1),
            (SELECT id FROM coas WHERE nama_akun LIKE '%kas%' LIMIT 1)
        ) as coa_id,
        p.tanggal_penggajian as tanggal,
        CONCAT('Penggajian ', COALESCE(pg.nama, CONCAT('ID-', p.id))) as keterangan,
        0 as debit,
        p.total_gaji as kredit,
        p.id as referensi,
        'penggajian' as tipe_referensi,
        1 as created_by,
        NOW() as created_at,
        NOW() as updated_at
    FROM penggajians p
    LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
    WHERE NOT EXISTS (
        SELECT 1 FROM jurnal_umum ju 
        WHERE ju.tipe_referensi = 'penggajian' 
        AND ju.referensi = p.id 
        AND ju.kredit > 0
    )
    ";
    
    $stmt = $pdo->prepare($creditSql);
    $stmt->execute();
    $creditCount = $stmt->rowCount();
    echo "✓ Created $creditCount CREDIT entries\n\n";
    
    echo "STEP 4: Verification...\n";
    
    // Verifikasi hasil
    $verifySql = "
    SELECT 
        'Total Penggajian' as item,
        COUNT(*) as jumlah
    FROM penggajians
    UNION ALL
    SELECT 
        'Penggajian Lunas' as item,
        COUNT(*) as jumlah
    FROM penggajians WHERE status_pembayaran = 'lunas'
    UNION ALL
    SELECT 
        'Jurnal Penggajian (Debit)' as item,
        COUNT(*) as jumlah
    FROM jurnal_umum 
    WHERE tipe_referensi = 'penggajian' AND debit > 0
    UNION ALL
    SELECT 
        'Jurnal Penggajian (Credit)' as item,
        COUNT(*) as jumlah
    FROM jurnal_umum 
    WHERE tipe_referensi = 'penggajian' AND kredit > 0
    ";
    
    $stmt = $pdo->prepare($verifySql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "• " . $row['item'] . ": " . $row['jumlah'] . "\n";
    }
    
    echo "\n=== HASIL PERBAIKAN ===\n";
    
    // Tampilkan jurnal yang dibuat
    $jurnalSql = "
    SELECT 
        ju.tanggal,
        ju.keterangan,
        c.kode_akun,
        c.nama_akun,
        ju.debit,
        ju.kredit
    FROM jurnal_umum ju
    LEFT JOIN coas c ON ju.coa_id = c.id
    WHERE ju.tipe_referensi = 'penggajian'
    ORDER BY ju.tanggal, ju.referensi, ju.debit DESC
    ";
    
    $stmt = $pdo->prepare($jurnalSql);
    $stmt->execute();
    $jurnalResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($jurnalResults) > 0) {
        echo "Jurnal entries yang dibuat:\n";
        foreach ($jurnalResults as $jurnal) {
            $debit = $jurnal['debit'] > 0 ? number_format($jurnal['debit'], 0, ',', '.') : '-';
            $kredit = $jurnal['kredit'] > 0 ? number_format($jurnal['kredit'], 0, ',', '.') : '-';
            echo "• {$jurnal['tanggal']} | {$jurnal['kode_akun']} - {$jurnal['nama_akun']} | Debit: Rp $debit | Credit: Rp $kredit\n";
        }
    }
    
    echo "\n✅ PERBAIKAN SELESAI!\n";
    echo "Silakan refresh halaman jurnal umum dan pilih filter 'Penggajian'\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}