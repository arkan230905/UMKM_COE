SELECT je.id, je.ref_type, je.ref_id, je.memo, je.user_id,
       COUNT(jl.id) as line_count
FROM journal_entries je
LEFT JOIN journal_lines jl ON jl.journal_entry_id = je.id
GROUP BY je.id
ORDER BY je.id;

SELECT tipe_referensi, COUNT(*) as cnt, SUM(debit) as total_debit, SUM(kredit) as total_kredit
FROM jurnal_umum
WHERE user_id = 4
GROUP BY tipe_referensi;
