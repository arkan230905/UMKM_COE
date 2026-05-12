<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=eadt_umkm;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Fixing penggajian journal entries...\n";

// Update penggajian status
$pdo->exec("UPDATE penggajians SET status_pembayaran = 'lunas', tanggal_dibayar = tanggal_penggajian WHERE status_pembayaran = 'belum_lunas'");

// Create DEBIT entries
$pdo->exec("
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
SELECT 
    (SELECT id FROM coas WHERE kode_akun = '52' LIMIT 1) as coa_id,
    p.tanggal_penggajian,
    CONCAT('Penggajian ID-', p.id),
    p.total_gaji,
    0,
    p.id,
    'penggajian',
    1,
    NOW(),
    NOW()
FROM penggajians p
WHERE NOT EXISTS (SELECT 1 FROM jurnal_umum WHERE tipe_referensi = 'penggajian' AND referensi = p.id AND debit > 0)
");

// Create CREDIT entries  
$pdo->exec("
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
SELECT 
    (SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1) as coa_id,
    p.tanggal_penggajian,
    CONCAT('Penggajian ID-', p.id),
    0,
    p.total_gaji,
    p.id,
    'penggajian',
    1,
    NOW(),
    NOW()
FROM penggajians p
WHERE NOT EXISTS (SELECT 1 FROM jurnal_umum WHERE tipe_referensi = 'penggajian' AND referensi = p.id AND kredit > 0)
");

$count = $pdo->query("SELECT COUNT(*) FROM jurnal_umum WHERE tipe_referensi = 'penggajian'")->fetchColumn();
echo "Created $count penggajian journal entries\n";
echo "Done!\n";
?>