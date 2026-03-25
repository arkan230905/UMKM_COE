-- Fix Ayam Kampung Stock Data - COMPLETE CLEANUP
-- Current issue: Stock shows wrong calculations and wrong direction for production

-- Step 1: DELETE ALL existing stock data for Ayam Kampung (material id 2) - COMPLETE CLEANUP
DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2;
DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2;

-- Step 2: DELETE any existing production details for Ayam Kampung to avoid conflicts
DELETE FROM produksi_details WHERE bahan_baku_id = 2;

-- Step 3: Fix BOM data - Update BOM Job BBB to use 2 Ekor instead of 1.6667
UPDATE bom_job_bbb 
SET jumlah = 2.0000, subtotal = 90000.00 
WHERE bahan_baku_id = 2 AND bom_job_costing_id IN (
    SELECT id FROM bom_job_costings WHERE produk_id = 1
);

-- Step 4: Fix BOM Details data if exists
UPDATE bom_details 
SET jumlah = 2.0000, total_harga = 90000.00 
WHERE bahan_baku_id = 2 AND bom_id IN (
    SELECT id FROM boms WHERE produk_id = 1
);

-- Step 5: Create correct initial stock (March 1st, 2026) - 30 Ekor at 45,000
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 6: Create correct initial stock layer - 30 Ekor at 45,000
INSERT INTO stock_layers (item_type, item_id, tanggal, qty, satuan, unit_cost, remaining_qty, ref_type, ref_id, created_at, updated_at) 
VALUES ('material', 2, '2026-03-01', 30.0000, 'Ekor', 45000.0000, 30.0000, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Step 7: Add production consumption (March 11th, 2026) - OUT 2 Ekor at 45,000 (REDUCTION)
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES ('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- Step 8: Update stock layer to reflect remaining stock after production - 28 Ekor remaining
UPDATE stock_layers 
SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05'
WHERE item_type = 'material' AND item_id = 2;

-- Step 9: Update master data to show correct remaining stock - 28 Ekor
UPDATE bahan_bakus SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE id = 2;

-- Step 10: Re-create production detail with correct data
INSERT INTO produksi_details (produksi_id, bahan_baku_id, bahan_pendukung_id, qty_resep, satuan_resep, qty_konversi, harga_satuan, subtotal, satuan, created_at, updated_at)
VALUES (1, 2, NULL, 2.0000, 'Ekor', 2.0000, 45000.0000, 90000.00, 'Ekor', '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- Verification queries
SELECT 'Stock Movements for Ayam Kampung:' as info;
SELECT id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal;

SELECT 'Stock Layers for Ayam Kampung:' as info;
SELECT id, tanggal, qty, remaining_qty, unit_cost FROM stock_layers WHERE item_type = 'material' AND item_id = 2;

SELECT 'Master Data Stock:' as info;
SELECT id, nama, stok FROM bahan_bakus WHERE id = 2;

-- Expected Results After Running This Script:
-- 
-- Kartu Stok Ayam Kampung should show:
-- 01/03/2026 | Saldo Awal    | 30 Ekor | Rp 45.000 | Rp 1.350.000 | (empty) | (empty) | (empty) | (empty) | (empty) | (empty) | 30 Ekor | Rp 45.000 | Rp 1.350.000
-- 11/03/2026 | Production #1 | (empty) | (empty) | (empty) | (empty) | (empty) | (empty) | 2 Ekor | Rp 45.000 | Rp 90.000 | 28 Ekor | Rp 45.000 | Rp 1.260.000
--
-- Key Points:
-- - Initial stock: 30 Ekor at Rp 45,000 = Rp 1,350,000
-- - Production REDUCES stock by 2 Ekor at Rp 45,000 = Rp 90,000
-- - Final stock: 28 Ekor at Rp 45,000 = Rp 1,260,000
-- - Unit price remains constant at Rp 45,000 (FIFO principle)
--
-- IMPORTANT: Run this entire script in one transaction to avoid partial updates!