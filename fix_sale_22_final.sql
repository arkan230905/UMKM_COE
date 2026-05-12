-- Step 1: Find the journal_entry_id for sale 22/04
SELECT @entry_id := id FROM journal_entries 
WHERE tanggal = '2026-04-22' AND ref_type = 'sale' AND memo LIKE '%SJ-20260422%';

-- Step 2: Delete all wrong journal lines for this entry
DELETE FROM journal_lines WHERE journal_entry_id = @entry_id;

-- Step 3: Find correct COA IDs (same as used in sale 21/04)
SELECT @coa_kas := coa_id FROM journal_lines jl
JOIN journal_entries je ON je.id = jl.journal_entry_id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%SJ-20260421%' AND jl.debit > 0
LIMIT 1;

SELECT @coa_pendapatan := coa_id FROM journal_lines jl
JOIN journal_entries je ON je.id = jl.journal_entry_id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%SJ-20260421%' AND jl.credit > 0
LIMIT 1;

-- Step 4: Insert correct journal lines
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@entry_id, @coa_kas, 1393050.00, 0.00, 'Penerimaan tunai penjualan', '2026-04-22 08:45:02', '2026-04-22 08:45:02'),
(@entry_id, @coa_pendapatan, 0.00, 1393050.00, 'Pendapatan penjualan produk', '2026-04-22 08:45:02', '2026-04-22 08:45:02');

-- Step 5: Verify all 3 sales are now correct
SELECT 
    je.tanggal,
    je.memo,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
AND je.memo LIKE '%Penjualan%'
ORDER BY je.tanggal, jl.debit DESC;
