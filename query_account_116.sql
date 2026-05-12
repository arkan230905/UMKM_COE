-- Find all journal lines using account 116 (Persediaan Barang Jadi parent)
SELECT 
    jl.id as line_id,
    je.id as entry_id,
    je.tanggal,
    je.memo,
    je.source_type,
    jl.debit,
    jl.credit,
    c.kode_akun,
    c.nama_akun
FROM journal_lines jl
JOIN journal_entries je ON je.id = jl.journal_entry_id
JOIN coas c ON c.id = jl.coa_id
WHERE c.kode_akun = '116'
ORDER BY je.tanggal, jl.id;
