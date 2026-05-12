-- FINAL FIX UNTUK AYAM KAMPUNG
-- Jalankan script ini di phpMyAdmin atau MySQL command line

-- Mulai transaksi
START TRANSACTION;

-- 1. Perbaiki rasio konversi di tabel bahan_bakus
UPDATE bahan_bakus SET 
    satuan_id = (SELECT id FROM satuans WHERE nama = 'Ekor' LIMIT 1),
    sub_satuan_1_id = (SELECT id FROM satuans WHERE nama = 'Potong' LIMIT 1),
    sub_satuan_1_konversi = 6.0000,
    sub_satuan_2_id = (SELECT id FROM satuans WHERE nama IN ('Kilogram', 'Kg') LIMIT 1),
    sub_satuan_2_konversi = 1.5000,
    sub_satuan_3_id = (SELECT id FROM satuans WHERE nama = 'Gram' LIMIT 1),
    sub_satuan_3_konversi = 1500.0000
WHERE id = 2;

-- 2. Hapus semua data stok lama
DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2;
DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2;

-- 3. Insert stok awal yang benar (30 Ekor)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- 4. Insert produksi yang benar (1.6667 Ekor = 10 Potong)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-11', 'out', 1.6667, 'Ekor', 45000.0000, 75001.50, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- 5. Insert stock layer sisa (28.3333 Ekor)
INSERT INTO stock_layers 
(item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 28.3333, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- 6. Update stok master
UPDATE bahan_bakus SET stok = 28.3333 WHERE id = 2;

-- Commit transaksi
COMMIT;

-- Verifikasi hasil
SELECT 'RASIO KONVERSI:' as info;
SELECT 
    bb.nama_bahan,
    s1.nama as satuan_utama,
    s2.nama as sub_satuan_1, bb.sub_satuan_1_konversi,
    s3.nama as sub_satuan_2, bb.sub_satuan_2_konversi,
    s4.nama as sub_satuan_3, bb.sub_satuan_3_konversi
FROM bahan_bakus bb
LEFT JOIN satuans s1 ON bb.satuan_id = s1.id
LEFT JOIN satuans s2 ON bb.sub_satuan_1_id = s2.id
LEFT JOIN satuans s3 ON bb.sub_satuan_2_id = s3.id
LEFT JOIN satuans s4 ON bb.sub_satuan_3_id = s4.id
WHERE bb.id = 2;

SELECT 'STOCK MOVEMENTS:' as info;
SELECT tanggal, direction, qty, satuan, total_cost, ref_type 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

SELECT 'STOCK LAYERS:' as info;
SELECT remaining_qty, unit_cost, satuan 
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

SELECT 'MASTER STOCK:' as info;
SELECT stok FROM bahan_bakus WHERE id = 2;