-- Query untuk identifikasi entry ID yang duplikasi

-- 1. Lihat semua entries pada 28-29 April dengan detail
SELECT 
    je.id,
    je.tanggal,
    je.description,
    je.created_at,
    GROUP_CONCAT(CONCAT('Akun:', jl.account_id, ' Debit:', jl.debit, ' Memo:', jl.memo) SEPARATOR ' | ') as lines_detail
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.tanggal) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je.id
ORDER BY je.tanggal, je.id;

-- 2. Cari duplikasi berdasarkan tanggal + deskripsi
SELECT 
    je1.id as entry1_id,
    je2.id as entry2_id,
    je1.tanggal,
    je1.description,
    je1.created_at as created1,
    je2.created_at as created2
FROM journal_entries je1
JOIN journal_entries je2 ON 
    DATE(je1.tanggal) = DATE(je2.tanggal) AND
    je1.description = je2.description AND
    je1.id < je2.id
WHERE DATE(je1.tanggal) BETWEEN '2026-04-28' AND '2026-04-29';

-- 3. Cari entries dengan akun yang salah (28/04 Sewa dengan akun 550)
SELECT 
    je.id,
    je.tanggal,
    je.description,
    jl.account_id,
    jl.debit,
    jl.memo
FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.tanggal) = '2026-04-28'
AND je.description LIKE '%Sewa%'
AND jl.account_id = 550;

-- 4. Cari entries dengan memo yang salah (29/04 Listrik dengan memo 'operasional')
SELECT 
    je.id,
    je.tanggal,
    je.description,
    jl.account_id,
    jl.debit,
    jl.memo
FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.tanggal) = '2026-04-29'
AND je.description LIKE '%Listrik%'
AND jl.memo LIKE '%operasional%';
