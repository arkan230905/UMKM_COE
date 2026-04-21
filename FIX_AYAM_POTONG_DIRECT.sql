-- FIX AYAM POTONG ID=1: Change 120 Potong to 160 Potong
-- This fixes the production data inconsistency

-- First, check current data
SELECT 'BEFORE UPDATE - Production Details:' as info;
SELECT id, produksi_id, bahan_baku_id, qty_resep, satuan_resep, qty_konversi 
FROM produksi_details 
WHERE bahan_baku_id = 1;

SELECT 'BEFORE UPDATE - Stock Movements:' as info;
SELECT id, item_id, tanggal, ref_type, ref_id, direction, qty, qty_as_input, satuan_as_input 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production';

-- Update production details to 160 Potong
UPDATE produksi_details 
SET qty_resep = 160, 
    satuan_resep = 'Potong'
WHERE bahan_baku_id = 1;

-- Update stock movements to 160 Potong
UPDATE stock_movements 
SET qty_as_input = 160, 
    satuan_as_input = 'Potong'
WHERE item_type = 'material' 
  AND item_id = 1 
  AND ref_type = 'production';

-- Verify the changes
SELECT 'AFTER UPDATE - Production Details:' as info;
SELECT id, produksi_id, bahan_baku_id, qty_resep, satuan_resep, qty_konversi 
FROM produksi_details 
WHERE bahan_baku_id = 1;

SELECT 'AFTER UPDATE - Stock Movements:' as info;
SELECT id, item_id, tanggal, ref_type, ref_id, direction, qty, qty_as_input, satuan_as_input 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 1 AND ref_type = 'production';

SELECT 'FIX COMPLETED - Ayam Potong ID=1 updated from 120 to 160 Potong' as result;