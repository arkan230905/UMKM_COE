-- COMPREHENSIVE CLEANUP SCRIPT FOR AYAM KAMPUNG STOCK
-- This script will completely reset all Ayam Kampung stock data

-- Step 1: Check current data before cleanup
SELECT 'BEFORE CLEANUP - Stock Movements:' as info;
SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

SELECT 'BEFORE CLEANUP - Stock Layers:' as info;
SELECT id, qty, remaining_qty, unit_cost, ref_type 
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

-- Step 2: COMPLETE CLEANUP - Remove ALL related data
DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2;
DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2;

-- Step 3: Reset master data first
UPDATE bahan_bakus SET stok = 0 WHERE id = 2;

-- Step 4: Create ONLY correct initial stock - 30 Ekor at 45,000 (March 1st)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 5: Create ONLY correct stock layer - 30 Ekor at 45,000
INSERT INTO stock_layers 
(item_type, item_id, tanggal, qty, satuan, unit_cost, remaining_qty, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 30.0000, 'Ekor', 45000.0000, 30.0000, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 6: Add ONLY production consumption - OUT 2 Ekor at 45,000 (March 11th)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- Step 7: Update stock layer to remaining 28 Ekor
UPDATE stock_layers 
SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05'
WHERE item_type = 'material' AND item_id = 2;

-- Step 8: Update master data to final stock 28 Ekor
UPDATE bahan_bakus 
SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' 
WHERE id = 2;

-- Step 9: Verification - Check final results
SELECT 'AFTER CLEANUP - Stock Movements:' as info;
SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

SELECT 'AFTER CLEANUP - Stock Layers:' as info;
SELECT id, qty, remaining_qty, unit_cost, ref_type 
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

SELECT 'AFTER CLEANUP - Master Data:' as info;
SELECT id, nama_bahan, stok 
FROM bahan_bakus 
WHERE id = 2;

-- Expected Results:
-- Stock Movements should show EXACTLY 2 records:
-- 1. 2026-03-01 | in  | 30.0000 | 45000.0000 | 1350000.00 | initial_stock | 0
-- 2. 2026-03-11 | out | 2.0000  | 45000.0000 | 90000.00   | production    | 1
--
-- Stock Layer should show EXACTLY 1 record:
-- 1. qty: 30.0000 | remaining_qty: 28.0000 | unit_cost: 45000.0000
--
-- Master Data should show:
-- 1. nama_bahan: Ayam Kampung | stok: 28.0000