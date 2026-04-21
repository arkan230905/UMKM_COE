-- Lihat struktur tabel journal_lines
DESCRIBE journal_lines;

-- Lihat sample data
SELECT * FROM journal_lines LIMIT 5;

-- Lihat detail entry 29
SELECT 
    je.id,
    je.tanggal,
    je.memo,
    jl.*
FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE je.id = 29;
