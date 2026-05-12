-- Fix NULL user_id di semua tabel transaksi untuk user_id = 4 (Muhammad Arkan Abiyyu)

-- 1. Produksi
UPDATE produksis SET user_id = 4 WHERE user_id IS NULL OR user_id = 0;
SELECT 'produksis', COUNT(*) as fixed FROM produksis WHERE user_id = 4;

-- 2. Pembelian
UPDATE pembelians SET user_id = 4 WHERE user_id IS NULL OR user_id = 0;
SELECT 'pembelians', COUNT(*) as total FROM pembelians WHERE user_id = 4;

-- 3. Penjualan
UPDATE penjualans SET user_id = 4 WHERE user_id IS NULL OR user_id = 0;
SELECT 'penjualans', COUNT(*) as total FROM penjualans WHERE user_id = 4;

-- 4. Penggajian
UPDATE penggajians SET user_id = 4 WHERE user_id IS NULL OR user_id = 0;
SELECT 'penggajians', COUNT(*) as total FROM penggajians WHERE user_id = 4;

-- 5. Presensi (via pegawai user_id)
UPDATE presensis p
JOIN pegawais pg ON p.pegawai_id = pg.id
SET p.user_id = pg.user_id
WHERE p.user_id IS NULL AND pg.user_id IS NOT NULL;
SELECT 'presensis', COUNT(*) as total FROM presensis WHERE user_id = 4;

-- 6. Pelunasan Utang (via pembelian user_id)
UPDATE pelunasan_utangs pu
JOIN pembelians pb ON pu.pembelian_id = pb.id
SET pu.user_id = pb.user_id
WHERE pu.user_id IS NULL AND pb.user_id IS NOT NULL;
SELECT 'pelunasan_utangs', COUNT(*) as total FROM pelunasan_utangs WHERE user_id = 4;

-- Verify produksis
SELECT id, produk_id, status, user_id, created_at FROM produksis ORDER BY id DESC LIMIT 5;
