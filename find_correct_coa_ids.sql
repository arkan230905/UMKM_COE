-- Find the correct COA IDs for Kas and Pendapatan
SELECT id, kode_akun, nama_akun FROM coas WHERE kode_akun = '112'; -- Kas
SELECT id, kode_akun, nama_akun FROM coas WHERE kode_akun = '41'; -- Pendapatan
SELECT id, kode_akun, nama_akun FROM coas WHERE nama_akun LIKE '%PENDAPATAN%';

-- Check what was used in sale 21/04 (which is correct)
SELECT 
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
