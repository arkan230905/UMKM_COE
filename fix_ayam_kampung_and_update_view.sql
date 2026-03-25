-- ============================================
-- STEP 1: VERIFY CURRENT AYAM KAMPUNG CONFIGURATION
-- ============================================
SELECT 'Current Ayam Kampung Configuration:' as info;
SELECT 
    bb.id,
    bb.nama_bahan,
    bb.stok,
    s_utama.nama as satuan_utama,
    s_sub1.nama as sub_satuan_1_nama,
    bb.sub_satuan_1_konversi,
    s_sub2.nama as sub_satuan_2_nama,
    bb.sub_satuan_2_konversi,
    s_sub3.nama as sub_satuan_3_nama,
    bb.sub_satuan_3_konversi
FROM bahan_bakus bb
LEFT JOIN satuans s_utama ON bb.satuan_id = s_utama.id
LEFT JOIN satuans s_sub1 ON bb.sub_satuan_1_id = s_sub1.id
LEFT JOIN satuans s_sub2 ON bb.sub_satuan_2_id = s_sub2.id
LEFT JOIN satuans s_sub3 ON bb.sub_satuan_3_id = s_sub3.id
WHERE bb.id = 2;

-- ============================================
-- STEP 2: FIX STOCK DATA
-- ============================================

-- Clean up all existing stock data
DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2;
DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2;
UPDATE bahan_bakus SET stok = 0 WHERE id = 2;

-- Create initial stock: 30 Ekor at Rp 45,000 (March 1st, 2026)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Create stock layer
INSERT INTO stock_layers 
(item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Add production consumption: 2 Ekor (March 11th, 2026)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- Update stock layer: 30 - 2 = 28 Ekor
UPDATE stock_layers 
SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05'
WHERE item_type = 'material' AND item_id = 2;

-- Update master data
UPDATE bahan_bakus 
SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' 
WHERE id = 2;

-- ============================================
-- STEP 3: ENSURE SUB-UNIT CONFIGURATION IS CORRECT
-- ============================================

-- Get satuan IDs
SET @ekor_id = (SELECT id FROM satuans WHERE nama = 'Ekor' LIMIT 1);
SET @potong_id = (SELECT id FROM satuans WHERE nama = 'Potong' LIMIT 1);
SET @kg_id = (SELECT id FROM satuans WHERE nama = 'Kilogram' OR nama = 'Kg' LIMIT 1);
SET @gram_id = (SELECT id FROM satuans WHERE nama = 'Gram' LIMIT 1);

-- Update Ayam Kampung with correct sub-unit configuration
UPDATE bahan_bakus SET
    satuan_id = @ekor_id,
    -- Sub Satuan 1: Potong (1 Ekor = 6 Potong)
    sub_satuan_1_id = @potong_id,
    sub_satuan_1_konversi = 6.0000,
    -- Sub Satuan 2: Kilogram (1 Ekor = 1.5 Kg)
    sub_satuan_2_id = @kg_id,
    sub_satuan_2_konversi = 1.5000,
    -- Sub Satuan 3: Gram (1 Ekor = 1,500 Gram)
    sub_satuan_3_id = @gram_id,
    sub_satuan_3_konversi = 1500.0000
WHERE id = 2;

-- ============================================
-- STEP 4: VERIFICATION
-- ============================================

SELECT 'AFTER FIX - Stock Movements:' as info;
SELECT id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

SELECT 'AFTER FIX - Stock Layers:' as info;
SELECT id, tanggal, remaining_qty, satuan, unit_cost, ref_type,
       (SELECT SUM(remaining_qty) FROM stock_layers WHERE item_type = 'material' AND item_id = 2) as total_sum
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

SELECT 'AFTER FIX - Master Data with Sub-Units:' as info;
SELECT 
    bb.id,
    bb.nama_bahan,
    bb.stok,
    s_utama.nama as satuan_utama,
    s_sub1.nama as sub_satuan_1,
    bb.sub_satuan_1_konversi as konversi_1,
    s_sub2.nama as sub_satuan_2,
    bb.sub_satuan_2_konversi as konversi_2,
    s_sub3.nama as sub_satuan_3,
    bb.sub_satuan_3_konversi as konversi_3
FROM bahan_bakus bb
LEFT JOIN satuans s_utama ON bb.satuan_id = s_utama.id
LEFT JOIN satuans s_sub1 ON bb.sub_satuan_1_id = s_sub1.id
LEFT JOIN satuans s_sub2 ON bb.sub_satuan_2_id = s_sub2.id
LEFT JOIN satuans s_sub3 ON bb.sub_satuan_3_id = s_sub3.id
WHERE bb.id = 2;

-- ============================================
-- EXPECTED RESULTS
-- ============================================
/*
After running this script, the Kartu Stok should show:

SATUAN EKOR (Primary Unit):
- 01/03/2026: Stok Awal 30 Ekor @ Rp 45,000 = Rp 1,350,000 | Total: 30 Ekor
- 11/03/2026: Produksi 2 Ekor @ Rp 45,000 = Rp 90,000 | Total: 28 Ekor @ Rp 45,000 = Rp 1,260,000

SATUAN POTONG (1 Ekor = 6 Potong):
- 01/03/2026: Stok Awal 180 Potong @ Rp 7,500 = Rp 1,350,000 | Total: 180 Potong
- 11/03/2026: Produksi 12 Potong @ Rp 7,500 = Rp 90,000 | Total: 168 Potong @ Rp 7,500 = Rp 1,260,000

SATUAN KILOGRAM (1 Ekor = 1.5 Kg):
- 01/03/2026: Stok Awal 45 Kg @ Rp 30,000 = Rp 1,350,000 | Total: 45 Kg
- 11/03/2026: Produksi 3 Kg @ Rp 30,000 = Rp 90,000 | Total: 42 Kg @ Rp 30,000 = Rp 1,260,000

SATUAN GRAM (1 Ekor = 1,500 Gram):
- 01/03/2026: Stok Awal 45,000 Gram @ Rp 30 = Rp 1,350,000 | Total: 45,000 Gram
- 11/03/2026: Produksi 3,000 Gram @ Rp 30 = Rp 90,000 | Total: 42,000 Gram @ Rp 30 = Rp 1,260,000

CONVERSION FORMULAS:
- Potong = Ekor × 6
- Kilogram = Ekor × 1.5
- Gram = Ekor × 1,500
- Price Potong = Price Ekor ÷ 6
- Price Kilogram = Price Ekor ÷ 1.5
- Price Gram = Price Ekor ÷ 1,500
*/