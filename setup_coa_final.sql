USE eadt_umkm;

-- Tambahkan COA yang diperlukan untuk sistem penggajian
-- Pastikan user_id disesuaikan dengan user yang sedang login

-- COA Beban Gaji BTKL (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '52', 'BIAYA TENAGA KERJA LANGSUNG (BTKL)', 'Expense', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '52' AND user_id = 1);

-- COA Beban Gaji BOP/BTKTL (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '54', 'BIAYA TENAGA KERJA TIDAK LANGSUNG (BOP)', 'Expense', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '54' AND user_id = 1);

-- COA Beban Tunjangan (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '513', 'BEBAN TUNJANGAN', 'Expense', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '513' AND user_id = 1);

-- COA Beban Asuransi (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '514', 'BEBAN ASURANSI', 'Expense', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '514' AND user_id = 1);

-- COA Beban Bonus (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '515', 'BEBAN BONUS', 'Expense', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '515' AND user_id = 1);

-- COA Potongan Gaji (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '516', 'POTONGAN GAJI', 'Expense', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '516' AND user_id = 1);

-- COA Kas Bank (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '111', 'KAS BANK', 'Asset', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '111' AND user_id = 1);

-- COA Kas Tunai (jika belum ada)
INSERT IGNORE INTO coas (kode_akun, nama_akun, tipe_akun, saldo_awal, user_id, created_at, updated_at) 
SELECT '112', 'KAS TUNAI', 'Asset', 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM coas WHERE kode_akun = '112' AND user_id = 1);
