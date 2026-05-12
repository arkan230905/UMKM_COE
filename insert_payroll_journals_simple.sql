-- ===================================================================
-- INSERT PAYROLL JOURNAL ENTRIES - SIMPLE VERSION
-- ===================================================================

-- Get COA IDs first
-- COA 52: BTKL, COA 54: BOP, COA 513: Beban Tunjangan, COA 514: Beban Asuransi, COA 515: Beban Bonus, COA 112: Kas

-- Penggajian 1: Budi Susanto (2026-04-24, Total: 765.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 1, '2026-04-24', 'Penggajian', NOW(), NOW());

-- Get the ID of the last inserted entry
-- Then insert lines for this entry

-- Penggajian 3: Ahmad Suryanto (2026-04-24, Total: 847.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 3, '2026-04-24', 'Penggajian', NOW(), NOW());

-- Penggajian 4: Rina Wijaya (2026-04-25, Total: 526.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 4, '2026-04-25', 'Penggajian', NOW(), NOW());

-- Penggajian 5: Dedi Gunawan (2026-04-26, Total: 3.250.000)
INSERT INTO journal_entries (ref_type, ref_id, tanggal, memo, created_at, updated_at)
VALUES ('payroll', 5, '2026-04-26', 'Penggajian', NOW(), NOW());

-- Now insert the lines
-- For Penggajian 1 (entry_id will be the max id)
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 1), (SELECT id FROM coas WHERE kode_akun = '52'), 140000, 0, 'Gaji Pokok', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 1), (SELECT id FROM coas WHERE kode_akun = '513'), 525000, 0, 'Beban Tunjangan', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 1), (SELECT id FROM coas WHERE kode_akun = '514'), 100000, 0, 'Beban Asuransi', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 1), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 765000, 'Pembayaran Gaji', NOW(), NOW());

-- For Penggajian 3
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 3), (SELECT id FROM coas WHERE kode_akun = '52'), 72000, 0, 'Gaji Pokok', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 3), (SELECT id FROM coas WHERE kode_akun = '513'), 495000, 0, 'Beban Tunjangan', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 3), (SELECT id FROM coas WHERE kode_akun = '514'), 80000, 0, 'Beban Asuransi', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 3), (SELECT id FROM coas WHERE kode_akun = '515'), 200000, 0, 'Beban Bonus', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 3), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 847000, 'Pembayaran Gaji', NOW(), NOW());

-- For Penggajian 4
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 4), (SELECT id FROM coas WHERE kode_akun = '52'), 51000, 0, 'Gaji Pokok', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 4), (SELECT id FROM coas WHERE kode_akun = '513'), 475000, 0, 'Beban Tunjangan', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 4), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 526000, 'Pembayaran Gaji', NOW(), NOW());

-- For Penggajian 5
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 5), (SELECT id FROM coas WHERE kode_akun = '54'), 2500000, 0, 'BOP Tenaga Kerja Tidak Langsung', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 5), (SELECT id FROM coas WHERE kode_akun = '513'), 600000, 0, 'Beban Tunjangan', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 5), (SELECT id FROM coas WHERE kode_akun = '514'), 150000, 0, 'Beban Asuransi', NOW(), NOW()),
    ((SELECT MAX(id) FROM journal_entries WHERE ref_type = 'payroll' AND ref_id = 5), (SELECT id FROM coas WHERE kode_akun = '112'), 0, 3250000, 'Pembayaran Gaji', NOW(), NOW());
