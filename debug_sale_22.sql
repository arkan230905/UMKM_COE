-- Check journal_entry for 22/04
SELECT 'Journal Entry 22/04' as info, * FROM journal_entries 
WHERE tanggal = '2026-04-22' AND memo LIKE '%Penjualan%';

-- Check journal_lines for 22/04
SELECT 
    'Journal Lines 22/04' as info,
    jl.id,
    jl.journal_entry_id,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit,
    jl.memo
FROM journal_lines jl
JOIN journal_entries je ON je.id = jl.journal_entry_id
LEFT JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-22' AND je.memo LIKE '%Penjualan%';

-- Check what COA IDs should be used (from 21/04)
SELECT 
    'Correct COA from 21/04' as info,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_lines jl
JOIN journal_entries je ON je.id = jl.journal_entry_id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-21' AND je.memo LIKE '%Penjualan%';
