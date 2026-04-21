-- FIX AYAM POTONG ID=1: Change 120 Potong to 160 Potong
-- URL: laporan/stok?tipe=material&item_id=1&satuan_id=

-- 1. Check current data for item_id=1
SELECT 'ITEM ID=1 INFO:' as info;
SELECT id, nama_bahan, satuan_id, stok FROM bahan_bakus WHERE id = 1;

SELECT 'BEFORE FIX - Production Details for ID=1:' as info;
SELECT 
    pd.id,
    pd.produksi_id,
    pd.bahan_baku_id,
    pd.qty_resep,
    pd.satuan_resep,
    pd.qty_konversi
FROM produksi_details pd
WHERE pd.bahan_baku_id = 1;

SELECT 'BEFORE FIX - Stock Movements for ID=1:' as info;
SELECT 
    sm.id,
    sm.item_id,
    sm.ref_id,
    sm.qty,
    sm.qty_as_input,
    sm.satuan_as_input,
    sm.tanggal
FROM stock_movements sm
WHERE sm.item_type = 'material'
AND sm.item_id = 1
AND sm.ref_type = 'production';

-- 2. FORCE UPDATE: Set production details to 160 Potong for item_id=1
UPDATE produksi_details 
SET 
    qty_resep = 160,
    satuan_resep = 'Potong'
WHERE bahan_baku_id = 1;

-- 3. FORCE UPDATE: Set stock movements to use 160 Potong as input for item_id=1
UPDATE stock_movements 
SET 
    qty_as_input = 160,
    satuan_as_input = 'Potong'
WHERE item_type = 'material'
AND item_id = 1
AND ref_type = 'production';

-- 4. Verify the fix for item_id=1
SELECT 'AFTER FIX - Production Details for ID=1:' as info;
SELECT 
    pd.id,
    pd.produksi_id,
    pd.bahan_baku_id,
    pd.qty_resep,
    pd.satuan_resep,
    pd.qty_konversi
FROM produksi_details pd
WHERE pd.bahan_baku_id = 1;

SELECT 'AFTER FIX - Stock Movements for ID=1:' as info;
SELECT 
    sm.id,
    sm.item_id,
    sm.ref_id,
    sm.qty,
    sm.qty_as_input,
    sm.satuan_as_input,
    sm.tanggal
FROM stock_movements sm
WHERE sm.item_type = 'material'
AND sm.item_id = 1
AND sm.ref_type = 'production';

-- Expected result: All should show 160 Potong for item_id=1
SELECT 'FIX COMPLETE FOR ID=1! Refresh laporan/stok?tipe=material&item_id=1 to see 160 Potong' as result;