-- PERBAIKAN JURNAL PENGGAJIAN
-- Database: eadt_umkm

USE eadt_umkm;

-- 1. Update status penggajian menjadi lunas
UPDATE penggajians 
SET 
    status_pembayaran = 'lunas',
    tanggal_dibayar = tanggal_penggajian,
    updated_at = NOW()
WHERE status_pembayaran = 'belum_lunas';

-- 2. Buat jurnal DEBIT (Beban Gaji)
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
SELECT 
    COALESCE(
        (SELECT id FROM coas WHERE kode_akun = '52' LIMIT 1),
        (SELECT id FROM coas WHERE kode_akun = '54' LIMIT 1)
    ) as coa_id,
    p.tanggal_penggajian as tanggal,
    CONCAT('Penggajian ID-', p.id) as keterangan,
    p.total_gaji as debit,
    0 as kredit,
    p.id as referensi,
    'penggajian' as tipe_referensi,
    1 as created_by,
    NOW() as created_at,
    NOW() as updated_at
FROM penggajians p
WHERE NOT EXISTS (
    SELECT 1 FROM jurnal_umum ju 
    WHERE ju.tipe_referensi = 'penggajian' 
    AND ju.referensi = p.id 
    AND ju.debit > 0
);

-- 3. Buat jurnal CREDIT (Kas/Bank)
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
SELECT 
    COALESCE(
        (SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1),
        (SELECT id FROM coas WHERE kode_akun = '112' LIMIT 1)
    ) as coa_id,
    p.tanggal_penggajian as tanggal,
    CONCAT('Penggajian ID-', p.id) as keterangan,
    0 as debit,
    p.total_gaji as kredit,
    p.id as referensi,
    'penggajian' as tipe_referensi,
    1 as created_by,
    NOW() as created_at,
    NOW() as updated_at
FROM penggajians p
WHERE NOT EXISTS (
    SELECT 1 FROM jurnal_umum ju 
    WHERE ju.tipe_referensi = 'penggajian' 
    AND ju.referensi = p.id 
    AND ju.kredit > 0
);

-- 4. Verifikasi hasil
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
WHERE tipe_referensi = 'penggajian' AND kredit > 0;

-- 5. Tampilkan jurnal yang dibuat
SELECT 
    ju.tanggal,
    ju.keterangan,
    c.kode_akun,
    c.nama_akun,
    FORMAT(ju.debit, 0) as debit,
    FORMAT(ju.kredit, 0) as kredit
FROM jurnal_umum ju
LEFT JOIN coas c ON ju.coa_id = c.id
WHERE ju.tipe_referensi = 'penggajian'
ORDER BY ju.tanggal, ju.referensi, ju.debit DESC;