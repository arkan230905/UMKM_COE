-- ===================================================================
-- INSERT PAYROLL JOURNAL ENTRIES
-- ===================================================================

-- Penggajian 1: Budi Susanto (2026-04-24, Total: 765.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 1, '2026-04-24', 'Penggajian', NOW(), NOW());

SET @entry_id_1 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @entry_id_1, id, 140000, 0, 'Gaji Pokok', NOW(), NOW() FROM coas WHERE kode_akun = '52'
UNION ALL
SELECT @entry_id_1, id, 525000, 0, 'Beban Tunjangan', NOW(), NOW() FROM coas WHERE kode_akun = '513'
UNION ALL
SELECT @entry_id_1, id, 100000, 0, 'Beban Asuransi', NOW(), NOW() FROM coas WHERE kode_akun = '514'
UNION ALL
SELECT @entry_id_1, id, 0, 765000, 'Pembayaran Gaji', NOW(), NOW() FROM coas WHERE kode_akun = '112';

-- Penggajian 3: Ahmad Suryanto (2026-04-24, Total: 847.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 3, '2026-04-24', 'Penggajian', NOW(), NOW());

SET @entry_id_3 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @entry_id_3, id, 72000, 0, 'Gaji Pokok', NOW(), NOW() FROM coas WHERE kode_akun = '52'
UNION ALL
SELECT @entry_id_3, id, 495000, 0, 'Beban Tunjangan', NOW(), NOW() FROM coas WHERE kode_akun = '513'
UNION ALL
SELECT @entry_id_3, id, 80000, 0, 'Beban Asuransi', NOW(), NOW() FROM coas WHERE kode_akun = '514'
UNION ALL
SELECT @entry_id_3, id, 200000, 0, 'Beban Bonus', NOW(), NOW() FROM coas WHERE kode_akun = '515'
UNION ALL
SELECT @entry_id_3, id, 0, 847000, 'Pembayaran Gaji', NOW(), NOW() FROM coas WHERE kode_akun = '112';

-- Penggajian 4: Rina Wijaya (2026-04-25, Total: 526.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 4, '2026-04-25', 'Penggajian', NOW(), NOW());

SET @entry_id_4 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @entry_id_4, id, 51000, 0, 'Gaji Pokok', NOW(), NOW() FROM coas WHERE kode_akun = '52'
UNION ALL
SELECT @entry_id_4, id, 475000, 0, 'Beban Tunjangan', NOW(), NOW() FROM coas WHERE kode_akun = '513'
UNION ALL
SELECT @entry_id_4, id, 0, 526000, 'Pembayaran Gaji', NOW(), NOW() FROM coas WHERE kode_akun = '112';

-- Penggajian 5: Dedi Gunawan (2026-04-26, Total: 3.250.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 5, '2026-04-26', 'Penggajian', NOW(), NOW());

SET @entry_id_5 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @entry_id_5, id, 2500000, 0, 'BOP Tenaga Kerja Tidak Langsung', NOW(), NOW() FROM coas WHERE kode_akun = '54'
UNION ALL
SELECT @entry_id_5, id, 600000, 0, 'Beban Tunjangan', NOW(), NOW() FROM coas WHERE kode_akun = '513'
UNION ALL
SELECT @entry_id_5, id, 150000, 0, 'Beban Asuransi', NOW(), NOW() FROM coas WHERE kode_akun = '514'
UNION ALL
SELECT @entry_id_5, id, 0, 3250000, 'Pembayaran Gaji', NOW(), NOW() FROM coas WHERE kode_akun = '112';

-- ===================================================================
-- VERIFY PAYROLL ENTRIES
-- ===================================================================

SELECT 
    'PAYROLL JOURNAL ENTRIES' as status,
    je.id,
    je.tanggal,
    je.ref_id,
    SUM(CASE WHEN jl.debit > 0 THEN jl.debit ELSE 0 END) as total_debit,
    SUM(CASE WHEN jl.credit > 0 THEN jl.credit ELSE 0 END) as total_credit
FROM journal_entries je
LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
WHERE je.ref_type = 'payroll'
GROUP BY je.id, je.tanggal, je.ref_id
ORDER BY je.tanggal;
