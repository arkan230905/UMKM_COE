-- Update saldo awal untuk akun persediaan bahan baku
-- Ayam Potong (1141): 50 kg × Rp 32.000 = Rp 1.600.000
UPDATE coas SET saldo_awal = 1600000 WHERE kode_akun = '1141';

-- Ayam Kampung (1142): 40 kg × Rp 45.000 = Rp 1.800.000
UPDATE coas SET saldo_awal = 1800000 WHERE kode_akun = '1142';

-- Bebek (1143): 50 kg × Rp 50.000 = Rp 2.500.000
UPDATE coas SET saldo_awal = 2500000 WHERE kode_akun = '1143';

-- Update saldo awal untuk akun persediaan bahan pendukung
-- Tepung Terigu (1152): 400 kg × Rp 50.000 = Rp 20.000.000
UPDATE coas SET saldo_awal = 20000000 WHERE kode_akun = '1152';

-- Tepung Maizena (1153): 400 kg × Rp 50.000 = Rp 20.000.000
UPDATE coas SET saldo_awal = 20000000 WHERE kode_akun = '1153';

-- Lada (1154): 400 kg × Rp 15.000 = Rp 6.000.000
UPDATE coas SET saldo_awal = 6000000 WHERE kode_akun = '1154';

-- Bubuk Kaldu (1155): 400 kg × Rp 40.000 = Rp 16.000.000
UPDATE coas SET saldo_awal = 16000000 WHERE kode_akun = '1155';

-- Bubuk Bawang Putih (1156): 400 kg × Rp 62.000 = Rp 24.800.000
UPDATE coas SET saldo_awal = 24800000 WHERE kode_akun = '1156';

-- Verifikasi hasil
SELECT 
    kode_akun,
    nama_akun,
    saldo_awal,
    FORMAT(saldo_awal, 0) as saldo_formatted
FROM coas
WHERE kode_akun IN ('1141', '1142', '1143', '1152', '1153', '1154', '1155', '1156')
ORDER BY kode_akun;

-- Hitung total saldo awal persediaan
SELECT 
    'Total Saldo Awal Persediaan di COA' as info,
    SUM(saldo_awal) as total
FROM coas
WHERE kode_akun IN ('1141', '1142', '1143', '1152', '1153', '1154', '1155', '1156');
