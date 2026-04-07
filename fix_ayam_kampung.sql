-- Fix Ayam Kampung Initial Stock with April 1 date
-- First, check current data
SELECT 'Current Stock Movements:' as info;
SELECT tanggal, direction, qty, satuan, ref_type FROM stock_movements 
WHERE item_type='material' AND item_id=2 ORDER BY tanggal;

-- Add initial stock for April 1, 2026 if not exists
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
SELECT 'material', 2, '2026-04-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-04-01 00:00:00', '2026-04-01 00:00:00'
WHERE NOT EXISTS (
    SELECT 1 FROM stock_movements 
    WHERE item_type='material' AND item_id=2 AND ref_type='initial_stock'
);

-- Calculate remaining stock after production
SET @production_usage = (
    SELECT COALESCE(SUM(qty), 0) FROM stock_movements 
    WHERE item_type='material' AND item_id=2 AND ref_type='production' AND direction='out'
);

SET @remaining_stock = 30.0 - @production_usage;

-- Delete old stock layers
DELETE FROM stock_layers WHERE item_type='material' AND item_id=2;

-- Add new stock layer for April 1, 2026 if remaining stock > 0
INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at)
SELECT 'material', 2, '2026-04-01', @remaining_stock, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-04-01 00:00:00', '2026-04-01 00:00:00'
WHERE @remaining_stock > 0;

-- Update master stock
UPDATE bahan_bakus SET stok = @remaining_stock WHERE id = 2;

-- Update ALL existing initial stock dates to April 1, 2026
UPDATE stock_movements 
SET tanggal = '2026-04-01', created_at = '2026-04-01 00:00:00', updated_at = '2026-04-01 00:00:00' 
WHERE ref_type = 'initial_stock';

UPDATE stock_layers 
SET tanggal = '2026-04-01', created_at = '2026-04-01 00:00:00', updated_at = '2026-04-01 00:00:00' 
WHERE ref_type = 'initial_stock';

-- Verify the fix
SELECT 'Final Stock Movements:' as info;
SELECT tanggal, direction, qty, satuan, ref_type, total_cost FROM stock_movements 
WHERE item_type='material' AND item_id=2 ORDER BY tanggal, created_at;

SELECT 'Final Stock Layers:' as info;
SELECT tanggal, remaining_qty, satuan, ref_type, unit_cost FROM stock_layers 
WHERE item_type='material' AND item_id=2;

SELECT 'Master Stock:' as info;
SELECT nama_bahan, stok FROM bahan_bakus WHERE id=2;

SELECT 'All Initial Stock Dates:' as info;
SELECT DISTINCT item_type, tanggal FROM stock_movements WHERE ref_type='initial_stock' ORDER BY tanggal;