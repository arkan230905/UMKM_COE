-- ===== AUDIT SEMUA TABEL TRANSAKSI =====

-- 1. Pembelian
SELECT 'pembelians' as tabel, user_id, COUNT(*) as cnt FROM pembelians GROUP BY user_id;

-- 2. Journal entries dari pembelian
SELECT 'journal_entries purchase' as tabel, je.user_id, COUNT(*) as cnt 
FROM journal_entries je WHERE je.ref_type = 'purchase' GROUP BY je.user_id;

-- 3. Semua journal entries
SELECT 'journal_entries ALL' as tabel, je.user_id, je.ref_type, COUNT(*) as cnt 
FROM journal_entries je GROUP BY je.user_id, je.ref_type;

-- 4. Jurnal umum
SELECT 'jurnal_umum' as tabel, user_id, tipe_referensi, COUNT(*) as cnt 
FROM jurnal_umum GROUP BY user_id, tipe_referensi;

-- 5. Penjualan
SELECT 'penjualans' as tabel, user_id, COUNT(*) as cnt FROM penjualans GROUP BY user_id;

-- 6. Penggajian
SELECT 'penggajians' as tabel, user_id, COUNT(*) as cnt FROM penggajians GROUP BY user_id;

-- 7. Produksi
SELECT 'produksis' as tabel, user_id, COUNT(*) as cnt FROM produksis GROUP BY user_id;

-- ===== FIX SEMUA NULL USER_ID =====

-- Fix pembelians
UPDATE pembelians SET user_id = 4 WHERE user_id IS NULL;

-- Fix penjualans
UPDATE penjualans SET user_id = 4 WHERE user_id IS NULL;

-- Fix penggajians
UPDATE penggajians SET user_id = 4 WHERE user_id IS NULL;

-- Fix produksis
UPDATE produksis SET user_id = 4 WHERE user_id IS NULL;

-- Fix journal_entries - dari pembelian
UPDATE journal_entries je
JOIN pembelians p ON je.ref_id = p.id AND je.ref_type = 'purchase'
SET je.user_id = p.user_id
WHERE je.user_id IS NULL;

-- Fix journal_entries - dari penjualan
UPDATE journal_entries je
JOIN penjualans p ON je.ref_id = p.id AND je.ref_type IN ('sale','penjualan')
SET je.user_id = p.user_id
WHERE je.user_id IS NULL;

-- Fix journal_entries - dari produksi
UPDATE journal_entries je
JOIN produksis p ON je.ref_id = p.id 
    AND je.ref_type IN ('production_material','production_labor_overhead','production_finish','production_finished','production')
SET je.user_id = p.user_id
WHERE je.user_id IS NULL;

-- Fix semua journal_entries yang masih NULL
UPDATE journal_entries SET user_id = 4 WHERE user_id IS NULL;

-- Fix jurnal_umum dari pembelian
UPDATE jurnal_umum ju
JOIN pembelians p ON ju.referensi = p.nomor_pembelian AND ju.tipe_referensi = 'pembelian'
SET ju.user_id = p.user_id
WHERE ju.user_id IS NULL;

-- Fix semua jurnal_umum yang masih NULL
UPDATE jurnal_umum SET user_id = 4 WHERE user_id IS NULL;

-- ===== VERIFY SETELAH FIX =====
SELECT 'AFTER FIX - journal_entries' as info, user_id, ref_type, COUNT(*) as cnt 
FROM journal_entries GROUP BY user_id, ref_type ORDER BY user_id, ref_type;

SELECT 'AFTER FIX - jurnal_umum' as info, user_id, tipe_referensi, COUNT(*) as cnt 
FROM jurnal_umum GROUP BY user_id, tipe_referensi ORDER BY user_id;
