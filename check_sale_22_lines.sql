-- Check the journal entry and lines for 22/04
SELECT 
    je.id as entry_id,
    je.tanggal,
    je.memo,
    jl.id as line_id,
    jl.coa_id,
    jl.debit,
    jl.credit,
    jl.memo as line_memo,
    c.kode_akun,
    c.nama_akun
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE je.tanggal = '2026-04-22' AND je.ref_type = 'sale'
ORDER BY jl.id;

-- Check what coa_id 41 and 112 are
SELECT id, kode_akun, nama_akun FROM coas WHERE id IN (41, 112, 1143);
