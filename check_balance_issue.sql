-- Cek total debit dan kredit di jurnal umum
SELECT 
    'Jurnal Umum Balance' as info,
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum;

-- Cek total debit dan kredit di journal_lines
SELECT 
    'Journal Entries Balance' as info,
    SUM(debit) as total_debit,
    SUM(credit) as total_kredit,
    SUM(debit) - SUM(credit) as selisih
FROM journal_lines;

-- Cek gabungan
SELECT 
    'Total Combined' as info,
    (SELECT SUM(debit) FROM jurnal_umum) + (SELECT SUM(debit) FROM journal_lines) as total_debit,
    (SELECT SUM(kredit) FROM jurnal_umum) + (SELECT SUM(credit) FROM journal_lines) as total_kredit,
    ((SELECT SUM(debit) FROM jurnal_umum) + (SELECT SUM(debit) FROM journal_lines)) - 
    ((SELECT SUM(kredit) FROM jurnal_umum) + (SELECT SUM(credit) FROM journal_lines)) as selisih;

-- Cek saldo awal COA
SELECT 
    'Saldo Awal COA' as info,
    SUM(CASE WHEN saldo_normal = 'Debit' THEN saldo_awal ELSE 0 END) as total_debit,
    SUM(CASE WHEN saldo_normal = 'Kredit' THEN saldo_awal ELSE 0 END) as total_kredit,
    SUM(CASE WHEN saldo_normal = 'Debit' THEN saldo_awal ELSE -saldo_awal END) as selisih
FROM coas
WHERE saldo_awal > 0;
