-- Get exact coa_id from working sale 21/04
SELECT 
    'COA for Kas' as info,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-21' 
AND je.memo LIKE '%Penjualan%'
AND jl.debit > 0;

SELECT 
    'COA for Pendapatan' as info,
    jl.coa_id,
    c.kode_akun,
    c.nama_akun
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-21' 
AND je.memo LIKE '%Penjualan%'
AND jl.credit > 0;
