-- Find all journal entries between April 21-23, 2026
SELECT 
    je.id as entry_id,
    je.tanggal,
    je.memo,
    je.source_type,
    je.source_id,
    jl.id as line_id,
    jl.debit,
    jl.credit,
    c.kode_akun,
    c.nama_akun
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY je.tanggal, je.id, jl.id;
