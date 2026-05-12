-- Verify the fixed journal entries
SELECT 
    je.id as entry_id,
    je.tanggal,
    je.memo,
    je.ref_type,
    je.ref_id,
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

-- Count journal entries per date
SELECT 
    je.tanggal,
    COUNT(DISTINCT je.id) as total_entries,
    SUM(jl.debit) as total_debit,
    SUM(jl.credit) as total_credit
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
WHERE je.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
GROUP BY je.tanggal
ORDER BY je.tanggal;
