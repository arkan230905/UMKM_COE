-- Lihat detail entry 29 dan 30
SELECT 
    je.id,
    je.tanggal,
    je.memo,
    jl.id as line_id,
    jl.account_id,
    jl.debit,
    jl.credit,
    jl.memo as line_memo
FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE je.id IN (29, 30)
ORDER BY je.id, jl.id;

-- Update akun 550 menjadi 551 untuk entry 29 (Pembayaran Beban Sewa)
UPDATE journal_lines 
SET account_id = 551
WHERE journal_entry_id = 29 
AND account_id = 550;

-- Verifikasi hasil
SELECT 
    je.id,
    je.tanggal,
    je.memo,
    jl.id as line_id,
    jl.account_id,
    jl.debit,
    jl.credit
FROM journal_entries je
JOIN journal_lines jl ON je.id = jl.journal_entry_id
WHERE je.id IN (29, 30)
ORDER BY je.id, jl.id;
