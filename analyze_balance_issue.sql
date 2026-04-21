-- Hitung total saldo awal persediaan
SELECT 
    'Total Saldo Awal Persediaan' as info,
    SUM(saldo_awal) as total
FROM coas
WHERE kode_akun LIKE '114%' OR kode_akun LIKE '115%' OR kode_akun LIKE '116%';

-- Lihat detail saldo awal per akun persediaan
SELECT 
    kode_akun,
    nama_akun,
    saldo_awal
FROM coas
WHERE (kode_akun LIKE '114%' OR kode_akun LIKE '115%' OR kode_akun LIKE '116%')
AND saldo_awal > 0
ORDER BY kode_akun;

-- Cek saldo awal modal
SELECT 
    kode_akun,
    nama_akun,
    saldo_awal
FROM coas
WHERE kode_akun LIKE '31%'
ORDER BY kode_akun;

-- Hitung total saldo awal semua aset
SELECT 
    'Total Saldo Awal Aset' as info,
    SUM(saldo_awal) as total
FROM coas
WHERE tipe_akun IN ('Asset', 'Aset', 'ASET');

-- Hitung total saldo awal modal + kewajiban
SELECT 
    'Total Saldo Awal Modal + Kewajiban' as info,
    SUM(saldo_awal) as total
FROM coas
WHERE tipe_akun IN ('Equity', 'Modal', 'MODAL', 'Liability', 'Kewajiban', 'KEWAJIBAN');
