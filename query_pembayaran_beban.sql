-- Query untuk analisis duplikasi journal entries pembayaran beban
-- Tanggal: 28/04/2026 - 29/04/2026

-- 1. Lihat semua entries pada tanggal tersebut
SELECT 
    je.id,
    je.entry_date,
    je.ref_type,
    je.description,
    je.created_at,
    COUNT(jl.id) as line_count,
    SUM(jl.debit) as total_debit,
    SUM(jl.credit) as total_credit
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je.id
ORDER BY je.entry_date, je.id;

-- 2. Lihat detail lines untuk setiap entry
SELECT 
    je.id as entry_id,
    je.entry_date,
    je.description,
    jl.id as line_id,
    jl.account_id,
    jl.debit,
    jl.credit
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
ORDER BY je.entry_date, je.id, jl.id;

-- 3. Cari duplikasi berdasarkan tanggal dan deskripsi
SELECT 
    je1.id as entry1_id,
    je2.id as entry2_id,
    je1.entry_date,
    je1.description,
    je1.created_at as created1,
    je2.created_at as created2
FROM journal_entries je1
JOIN journal_entries je2 ON 
    DATE(je1.entry_date) = DATE(je2.entry_date) AND
    je1.description = je2.description AND
    je1.id < je2.id
WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29';

-- 4. Cari entries dengan nominal sama per akun
SELECT 
    je1.id as entry1_id,
    je2.id as entry2_id,
    je1.entry_date,
    jl1.account_id,
    jl1.debit,
    jl1.credit
FROM journal_entries je1
JOIN journal_lines jl1 ON je1.id = jl1.journal_entry_id
JOIN journal_entries je2 ON DATE(je1.entry_date) = DATE(je2.entry_date) AND je1.id < je2.id
JOIN journal_lines jl2 ON je2.id = jl2.journal_entry_id
WHERE DATE(je1.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
  AND jl1.account_id = jl2.account_id
  AND jl1.debit = jl2.debit
  AND jl1.credit = jl2.credit
GROUP BY je1.id, je2.id, jl1.account_id;

-- 5. Summary
SELECT 
    'Total Entries' as metric,
    COUNT(DISTINCT je.id) as value
FROM journal_entries je
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
UNION ALL
SELECT 
    'Total Lines' as metric,
    COUNT(jl.id) as value
FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29';
