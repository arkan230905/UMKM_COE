-- MIGRATE PENGGAJIAN & PEMBAYARAN BEBAN TO MODERN JOURNAL SYSTEM
-- Database: eadt_umkm

USE eadt_umkm;

-- ============================================================
-- STEP 1: MIGRATE PENGGAJIAN TO JOURNAL_ENTRIES
-- ============================================================

-- Create journal entries for penggajian
INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
SELECT 
    p.tanggal_penggajian,
    'penggajian',
    p.id,
    CONCAT('Penggajian ', COALESCE(pg.nama, CONCAT('ID-', p.id))),
    NOW(),
    NOW()
FROM penggajians p
LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
WHERE NOT EXISTS (
    SELECT 1 FROM journal_entries je 
    WHERE je.ref_type = 'penggajian' 
    AND je.ref_id = p.id
);

-- Get the inserted journal entry IDs and create journal lines
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT 
    je.id,
    COALESCE(
        (SELECT id FROM coas WHERE kode_akun = '52' LIMIT 1),
        (SELECT id FROM coas WHERE kode_akun = '54' LIMIT 1)
    ),
    p.total_gaji,
    0,
    CONCAT('Beban Gaji ', COALESCE(pg.nama, CONCAT('ID-', p.id))),
    NOW(),
    NOW()
FROM journal_entries je
JOIN penggajians p ON je.ref_id = p.id AND je.ref_type = 'penggajian'
LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
WHERE NOT EXISTS (
    SELECT 1 FROM journal_lines jl 
    WHERE jl.journal_entry_id = je.id 
    AND jl.debit > 0
);

-- Create credit entries for penggajian
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT 
    je.id,
    COALESCE(
        (SELECT id FROM coas WHERE kode_akun = p.coa_kasbank LIMIT 1),
        (SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1)
    ),
    0,
    p.total_gaji,
    CONCAT('Pembayaran Gaji ', COALESCE(pg.nama, CONCAT('ID-', p.id))),
    NOW(),
    NOW()
FROM journal_entries je
JOIN penggajians p ON je.ref_id = p.id AND je.ref_type = 'penggajian'
LEFT JOIN pegawais pg ON p.pegawai_id = pg.id
WHERE NOT EXISTS (
    SELECT 1 FROM journal_lines jl 
    WHERE jl.journal_entry_id = je.id 
    AND jl.credit > 0
);

-- ============================================================
-- STEP 2: MIGRATE PEMBAYARAN BEBAN TO JOURNAL_ENTRIES
-- ============================================================

-- Create journal entries for pembayaran beban
INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
SELECT 
    pb.tanggal,
    'pembayaran_beban',
    pb.id,
    CONCAT('Pembayaran Beban: ', COALESCE(pb.keterangan, 'Tanpa catatan')),
    NOW(),
    NOW()
FROM pembayaran_bebans pb
WHERE NOT EXISTS (
    SELECT 1 FROM journal_entries je 
    WHERE je.ref_type = 'pembayaran_beban' 
    AND je.ref_id = pb.id
);

-- Create debit entries for pembayaran beban
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT 
    je.id,
    COALESCE(
        (SELECT id FROM coas WHERE kode_akun = '550' LIMIT 1),
        (SELECT id FROM coas WHERE kode_akun LIKE '5%' LIMIT 1)
    ),
    pb.jumlah,
    0,
    'Pembayaran Beban',
    NOW(),
    NOW()
FROM journal_entries je
JOIN pembayaran_bebans pb ON je.ref_id = pb.id AND je.ref_type = 'pembayaran_beban'
WHERE NOT EXISTS (
    SELECT 1 FROM journal_lines jl 
    WHERE jl.journal_entry_id = je.id 
    AND jl.debit > 0
);

-- Create credit entries for pembayaran beban
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT 
    je.id,
    (SELECT id FROM coas WHERE kode_akun = '111' LIMIT 1),
    0,
    pb.jumlah,
    'Pembayaran Beban',
    NOW(),
    NOW()
FROM journal_entries je
JOIN pembayaran_bebans pb ON je.ref_id = pb.id AND je.ref_type = 'pembayaran_beban'
WHERE NOT EXISTS (
    SELECT 1 FROM journal_lines jl 
    WHERE jl.journal_entry_id = je.id 
    AND jl.credit > 0
);

-- ============================================================
-- STEP 3: VERIFICATION
-- ============================================================

SELECT 
    'Total journal_entries' as item,
    COUNT(*) as jumlah
FROM journal_entries
UNION ALL
SELECT 
    'Penggajian in journal_entries' as item,
    COUNT(*) as jumlah
FROM journal_entries 
WHERE ref_type = 'penggajian'
UNION ALL
SELECT 
    'Pembayaran Beban in journal_entries' as item,
    COUNT(*) as jumlah
FROM journal_entries 
WHERE ref_type = 'pembayaran_beban'
UNION ALL
SELECT 
    'Total journal_lines' as item,
    COUNT(*) as jumlah
FROM journal_lines;

-- Show migrated data
SELECT 
    je.tanggal,
    je.ref_type,
    je.memo,
    c.kode_akun,
    c.nama_akun,
    FORMAT(jl.debit, 0) as debit,
    FORMAT(jl.credit, 0) as credit
FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
LEFT JOIN coas c ON jl.coa_id = c.id
WHERE je.ref_type IN ('penggajian', 'pembayaran_beban')
ORDER BY je.tanggal, je.id, jl.debit DESC;