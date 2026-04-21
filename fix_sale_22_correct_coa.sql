-- Step 1: Check correct COA IDs from sale 21/04
SELECT 
    'Sale 21/04 (CORRECT)' as info,
    jl.id,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%Penjualan%'
ORDER BY jl.id;

-- Step 2: Check what's wrong with sale 22/04
SELECT 
    'Sale 22/04 (WRONG)' as info,
    je.id as entry_id,
    jl.id as line_id,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-22' AND je.memo LIKE '%Penjualan%'
ORDER BY jl.id;

-- Step 3: Delete wrong journal lines for sale 22/04
DELETE jl FROM journal_lines jl
JOIN journal_entries je ON je.id = jl.journal_entry_id
WHERE je.tanggal = '2026-04-22' AND je.ref_type = 'sale';

-- Step 4: Get the journal_entry_id for sale 22/04
SELECT @entry_id_22 := id FROM journal_entries WHERE tanggal = '2026-04-22' AND ref_type = 'sale';

-- Step 5: Recreate correct journal lines using same COA IDs as sale 21/04
-- Get COA IDs from sale 21/04
SELECT @coa_kas := jl.coa_id 
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%Penjualan%' AND jl.debit > 0
LIMIT 1;

SELECT @coa_pendapatan := jl.coa_id 
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%Penjualan%' AND jl.credit > 0
LIMIT 1;

-- Insert correct lines
INSERT INTO journal_lines (journal_entry_id, coa_id, debit, credit, memo, created_at, updated_at)
VALUES 
(@entry_id_22, @coa_kas, 1393050.00, 0.00, 'Penerimaan tunai penjualan', NOW(), NOW()),
(@entry_id_22, @coa_pendapatan, 0.00, 1393050.00, 'Pendapatan penjualan produk', NOW(), NOW());

-- Step 6: Verify the fix
SELECT 
    'Sale 22/04 (FIXED)' as info,
    jl.id,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-22' AND je.memo LIKE '%Penjualan%'
ORDER BY jl.id;
