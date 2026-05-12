-- Script untuk menghapus entries yang salah/duplikasi
-- Backup dulu sebelum jalankan!

-- 1. Lihat entries yang akan dihapus
SELECT 
    je.id,
    je.entry_date,
    je.description,
    GROUP_CONCAT(CONCAT('Akun:', jl.account_id, ' Debit:', jl.debit) SEPARATOR ' | ') as lines_detail
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je.id
ORDER BY je.entry_date, je.id;

-- 2. Hapus entries yang salah
-- Untuk 28/04 - Pembayaran Beban Sewa dengan akun 550 (salah, seharusnya 551)
DELETE FROM journal_lines 
WHERE journal_entry_id IN (
    SELECT je.id FROM journal_entries je
    WHERE DATE(je.entry_date) = '2026-04-28'
    AND je.description LIKE '%Sewa%'
    AND je.id IN (
        SELECT DISTINCT je2.id FROM journal_entries je2
        JOIN journal_lines jl ON je2.id = jl.journal_entry_id
        WHERE jl.account_id = 550
        AND DATE(je2.entry_date) = '2026-04-28'
        AND je2.description LIKE '%Sewa%'
    )
);

DELETE FROM journal_entries 
WHERE DATE(entry_date) = '2026-04-28'
AND description LIKE '%Sewa%'
AND id IN (
    SELECT DISTINCT je.id FROM journal_entries je
    JOIN journal_lines jl ON je.id = jl.journal_entry_id
    WHERE jl.account_id = 550
    AND DATE(je.entry_date) = '2026-04-28'
    AND je.description LIKE '%Sewa%'
);

-- Untuk 29/04 - Pembayaran Beban Listrik dengan memo 'operasional' (duplikasi)
DELETE FROM journal_lines 
WHERE journal_entry_id IN (
    SELECT je.id FROM journal_entries je
    WHERE DATE(je.entry_date) = '2026-04-29'
    AND je.description LIKE '%Listrik%'
    AND je.id IN (
        SELECT DISTINCT je2.id FROM journal_entries je2
        JOIN journal_lines jl ON je2.id = jl.journal_entry_id
        WHERE jl.memo LIKE '%operasional%'
        AND DATE(je2.entry_date) = '2026-04-29'
        AND je2.description LIKE '%Listrik%'
    )
);

DELETE FROM journal_entries 
WHERE DATE(entry_date) = '2026-04-29'
AND description LIKE '%Listrik%'
AND id IN (
    SELECT DISTINCT je.id FROM journal_entries je
    JOIN journal_lines jl ON je.id = jl.journal_entry_id
    WHERE jl.memo LIKE '%operasional%'
    AND DATE(je.entry_date) = '2026-04-29'
    AND je.description LIKE '%Listrik%'
);

-- 3. Verifikasi hasil
SELECT 
    je.id,
    je.entry_date,
    je.description,
    GROUP_CONCAT(CONCAT('Akun:', jl.account_id, ' Debit:', jl.debit) SEPARATOR ' | ') as lines_detail
FROM journal_entries je
LEFT JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE DATE(je.entry_date) BETWEEN '2026-04-28' AND '2026-04-29'
GROUP BY je.id
ORDER BY je.entry_date, je.id;
