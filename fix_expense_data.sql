-- Fix expense_payments COA data
UPDATE expense_payments SET coa_beban_id = '551' WHERE id = 2;
UPDATE expense_payments SET coa_beban_id = '550' WHERE id = 3;

-- Delete old journal_entries
DELETE FROM journal_entries WHERE ref_type = 'expense_payment' AND ref_id IN (2, 3);

-- Create new journal_entries for ID 2 (Sewa)
INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
VALUES ('2026-04-28', 'expense_payment', 2, 'Pembayaran Beban: Pembayaran Beban Sewa', NOW(), NOW());

SET @je_id_2 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @je_id_2, id, 1500000, 0, 'Pembayaran beban', NOW(), NOW()
FROM coas WHERE kode_akun = '551';

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @je_id_2, id, 0, 1500000, 'Pembayaran beban operasional', NOW(), NOW()
FROM coas WHERE kode_akun = '111';

-- Create new journal_entries for ID 3 (Listrik)
INSERT INTO journal_entries (tanggal, ref_type, ref_id, memo, created_at, updated_at)
VALUES ('2026-04-29', 'expense_payment', 3, 'Pembayaran Beban: Pembayaran Beban Listrik', NOW(), NOW());

SET @je_id_3 = LAST_INSERT_ID();

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @je_id_3, id, 2030000, 0, 'Pembayaran beban', NOW(), NOW()
FROM coas WHERE kode_akun = '550';

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
SELECT @je_id_3, id, 0, 2030000, 'Pembayaran beban operasional', NOW(), NOW()
FROM coas WHERE kode_akun = '111';
