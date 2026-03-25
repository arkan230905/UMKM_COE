-- =====================================================
-- FINAL FIX FOR AYAM KAMPUNG STOCK
-- Copy and paste this ENTIRE script into phpMyAdmin
-- =====================================================

-- Step 1: Fix conversion ratios
UPDATE bahan_bakus 
SET 
    sub_satuan_1_konversi = 6.0000,
    sub_satuan_2_konversi = 1.5000,
    sub_satuan_3_konversi = 1500.0000
WHERE id = 2;

-- Step 2: Delete ALL old stock data
DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2;
DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2;

-- Step 3: Insert correct initial stock (30 Ekor)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 4: Insert correct production (1.6667 Ekor = 10 Potong)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-11', 'out', 1.6667, 'Ekor', 45000.0000, 75001.50, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- Step 5: Insert stock layer (28.3333 Ekor remaining)
INSERT INTO stock_layers 
(item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 28.3333, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 6: Update master stock
UPDATE bahan_bakus SET stok = 28.3333 WHERE id = 2;

-- Verification queries
SELECT 'Stock Movements:' as info;
SELECT id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

SELECT 'Stock Layers:' as info;
SELECT id, remaining_qty, unit_cost, satuan 
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

SELECT 'Master Stock:' as info;
SELECT id, nama_bahan, stok 
FROM bahan_bakus 
WHERE id = 2;

-- Expected results:
-- Stock Movements: 2 records (30 Ekor IN, 1.6667 Ekor OUT)
-- Stock Layers: 1 record (28.3333 Ekor remaining)
-- Master Stock: 28.3333 Ekor