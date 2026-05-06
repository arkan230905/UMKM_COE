-- Check journal_entries data
SELECT je.id, je.tanggal, je.ref_type, je.ref_id, je.memo, je.user_id,
       COUNT(jl.id) as line_count,
       SUM(jl.debit) as total_debit,
       SUM(jl.credit) as total_credit
FROM journal_entries je
LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
GROUP BY je.id
ORDER BY je.id DESC
LIMIT 20;

-- Check user_id distribution
SELECT user_id, COUNT(*) as count FROM journal_entries GROUP BY user_id;

-- Check journal_lines
SELECT jl.id, jl.journal_entry_id, jl.coa_id, jl.debit, jl.credit, jl.memo,
       c.kode_akun, c.nama_akun
FROM journal_lines jl
LEFT JOIN coas c ON c.id = jl.coa_id
ORDER BY jl.journal_entry_id DESC, jl.id
LIMIT 30;

-- Check jurnal_umum
SELECT ju.id, ju.tanggal, ju.tipe_referensi, ju.referensi, ju.user_id,
       ju.debit, ju.kredit, c.kode_akun, c.nama_akun
FROM jurnal_umum ju
LEFT JOIN coas c ON c.id = ju.coa_id
ORDER BY ju.id DESC
LIMIT 20;
