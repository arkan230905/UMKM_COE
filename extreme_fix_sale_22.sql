-- EXTREME FIX: Delete and recreate sale 22/04 completely

-- Step 1: Delete everything for sale 22/04
DELETE FROM journal_lines WHERE journal_entry_id IN (
    SELECT id FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale'
);

DELETE FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale';

-- Step 2: Create new journal_entry
INSERT INTO journal_entries (tanggal, memo, ref_type, ref_id, created_at, updated_at)
VALUES ('2026-04-22', 'Penjualan #SJ-20260422-001', 'sale', 2, '2026-04-22 08:45:02', '2026-04-22 08:45:02');

SET @new_id = LAST_INSERT_ID();

-- Step 3: Get correct coa_id from sale 21/04
SELECT @coa_kas := jl.coa_id FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%Penjualan%' AND jl.debit > 0 LIMIT 1;

SELECT @coa_pendapatan := jl.coa_id FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%Penjualan%' AND jl.credit > 0 LIMIT 1;

-- Step 4: Create journal_lines with CORRECT coa_id from sale 21/04
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES (@new_id, @coa_kas, 1393050.00, 0.00, 'Penerimaan tunai penjualan', '2026-04-22 08:45:02', '2026-04-22 08:45:02');

INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES (@new_id, @coa_pendapatan, 0.00, 1393050.00, 'Pendapatan penjualan produk', '2026-04-22 08:45:02', '2026-04-22 08:45:02');

-- Verify
SELECT 
    je.tanggal,
    je.memo,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-22' AND je.ref_type = 'sale'
ORDER BY jl.debit DESC;
