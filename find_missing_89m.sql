-- Cek saldo awal modal
SELECT 
    'Saldo Awal Modal' as info,
    kode_akun,
    nama_akun,
    saldo_awal,
    tipe_akun
FROM coas
WHERE kode_akun LIKE '31%' OR tipe_akun = 'Equity'
ORDER BY kode_akun;

-- Cek semua transaksi yang mempengaruhi modal/ekuitas
SELECT 
    'Transaksi Modal dari jurnal_umum' as source,
    ju.tanggal,
    ju.keterangan,
    c.kode_akun,
    c.nama_akun,
    ju.debit,
    ju.kredit
FROM jurnal_umum ju
JOIN coas c ON c.id = ju.coa_id
WHERE c.kode_akun LIKE '31%'
ORDER BY ju.tanggal;

-- Cek dari journal_entries
SELECT 
    'Transaksi Modal dari journal_entries' as source,
    je.tanggal,
    je.memo,
    c.kode_akun,
    c.nama_akun,
    jl.debit,
    jl.credit
FROM journal_entries je
JOIN journal_lines jl ON jl.journal_entry_id = je.id
JOIN coas c ON c.id = jl.coa_id
WHERE c.kode_akun LIKE '31%'
ORDER BY je.tanggal;

-- Hitung total saldo awal semua akun
SELECT 
    'Total Saldo Awal per Tipe' as info,
    tipe_akun,
    SUM(saldo_awal) as total_saldo_awal
FROM coas
WHERE saldo_awal > 0
GROUP BY tipe_akun;
