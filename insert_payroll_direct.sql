-- Insert payroll journal entries directly

-- First, get the COA IDs
-- SELECT id FROM coas WHERE kode_akun IN ('52', '54', '112', '513', '514', '515');

-- Penggajian 1: Budi Susanto (2026-04-24, Total: 765.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) 
VALUES ('payroll', 1, '2026-04-24', 'Penggajian', NOW(), NOW());

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '52'), 140000, 0, 'Gaji Pokok', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '513'), 525000, 0, 'Beban Tunjangan', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '514'), 100000, 0, 'Beban Asuransi', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 765000, 'Pembayaran Gaji', NOW(), NOW());

-- Penggajian 3: Ahmad Suryanto (2026-04-24, Total: 847.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) 
VALUES ('payroll', 3, '2026-04-24', 'Penggajian', NOW(), NOW());

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '52'), 72000, 0, 'Gaji Pokok', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '513'), 495000, 0, 'Beban Tunjangan', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '514'), 80000, 0, 'Beban Asuransi', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '515'), 200000, 0, 'Beban Bonus', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 847000, 'Pembayaran Gaji', NOW(), NOW());

-- Penggajian 4: Rina Wijaya (2026-04-25, Total: 526.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) 
VALUES ('payroll', 4, '2026-04-25', 'Penggajian', NOW(), NOW());

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '52'), 51000, 0, 'Gaji Pokok', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '513'), 475000, 0, 'Beban Tunjangan', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 526000, 'Pembayaran Gaji', NOW(), NOW());

-- Penggajian 5: Dedi Gunawan (2026-04-26, Total: 3.250.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at) 
VALUES ('payroll', 5, '2026-04-26', 'Penggajian', NOW(), NOW());

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '54'), 2500000, 0, 'BOP Tenaga Kerja Tidak Langsung', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '513'), 600000, 0, 'Beban Tunjangan', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '514'), 150000, 0, 'Beban Asuransi', NOW(), NOW()),
  (LAST_INSERT_ID(), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 3250000, 'Pembayaran Gaji', NOW(), NOW());

-- Verify
SELECT 
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
