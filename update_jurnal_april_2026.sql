-- Script untuk memperbaiki jurnal penggajian yang hilang
-- Tanggal: April 2026

-- 1. Update status penggajian menjadi lunas
UPDATE penggajians 
SET 
    status_pembayaran = 'lunas',
    tanggal_dibayar = tanggal_penggajian,
    updated_at = NOW()
WHERE status_pembayaran = 'belum_lunas';

-- 2. Buat jurnal entries untuk penggajian yang belum ada jurnalnya
-- DEBIT: Beban Gaji (COA 52 atau 54)
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
SELECT 
    COALESCE(
        (SELECT id FROM coas WHERE kode_akun = '52' LIMIT 1),
        (SELECT id FROM coas WHERE kode_akun = '54' LIMIT 1)
    ) as coa_id,
    p.tanggal_penggajian as tanggal,
    CONCAT('Penggajian ', COALESCE(pg.nama, 'Unknown')) as keterangan,
    p.total_gaji as debit,
    0 as kredit,
    p.id as referensi,
    'penggajian' as tipe_referensi,
    1 as created_by,
    NOW() as created_at,
    NOW() as updated_at
FROM penggajians p
LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
LEFT JOIN jurnal_umum ju ON ju.tipe_referensi = 'penggajian' AND ju.referensi = p.id AND ju.debit > 0
WHERE ju.id IS NULL;

-- 3. CREDIT: Kas/Bank
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_by, created_at, updated_at)
SELECT 
    COALESCE(
        (SELECT id FROM coas WHERE kode_akun = p.coa_kasbank LIMIT 1),
        (SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1)
    ) as coa_id,
    p.tanggal_penggajian as tanggal,
    CONCAT('Penggajian ', COALESCE(pg.nama, 'Unknown')) as keterangan,
    0 as debit,
    p.total_gaji as kredit,
    p.id as referensi,
    'penggajian' as tipe_referensi,
    1 as created_by,
    NOW() as created_at,
    NOW() as updated_at
FROM penggajians p
LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
LEFT JOIN jurnal_umum ju ON ju.tipe_referensi = 'penggajian' AND ju.referensi = p.id AND ju.kredit > 0
WHERE ju.id IS NULL;

-- 4. Verifikasi hasil
SELECT 
    'Total Penggajian' as item,
    COUNT(*) as jumlah
FROM penggajians
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