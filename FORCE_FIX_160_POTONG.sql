-- FORCE FIX: Change 120 Potong to 160 Potong
-- This script will directly update the database to fix the production data

-- 1. Check current data
SELECT 'BEFORE FIX - Production Detail:' as info;
SELECT 
    pd.id,
    pd.produksi_id,
    bb.nama_bahan,
    pd.qty_resep,
    pd.satuan_resep,
    pd.qty_konversi
FROM produksi_details pd
JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
WHERE bb.nama_bahan = 'Ayam Potong'
AND pd.produksi_id = 2;

SELECT 'BEFORE FIX - Stock Movement:' as info;
SELECT 
    sm.id,
    sm.ref_id,
    sm.qty,
    sm.qty_as_input,
    sm.satuan_as_input,
    sm.tanggal
FROM stock_movements sm
WHERE sm.item_type = 'material'
AND sm.item_id = 2
AND sm.ref_type = 'production';

-- 2. FORCE UPDATE: Set production detail to 160 Potong
UPDATE produksi_details 
SET 
    qty_resep = 160,
    satuan_resep = 'Potong'
WHERE bahan_baku_id = 2 
AND produksi_id = 2;

-- 3. FORCE UPDATE: Set stock movement to use 160 Potong as input
UPDATE stock_movements 
SET 
    qty_as_input = 160,
    satuan_as_input = 'Potong'
WHERE item_type = 'material'
AND item_id = 2
AND ref_type = 'production';

-- 4. Verify the fix
SELECT 'AFTER FIX - Production Detail:' as info;
SELECT 
    pd.id,
    pd.produksi_id,
    bb.nama_bahan,
    pd.qty_resep,
    pd.satuan_resep,
    pd.qty_konversi
FROM produksi_details pd
JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
WHERE bb.nama_bahan = 'Ayam Potong'
AND pd.produksi_id = 2;

SELECT 'AFTER FIX - Stock Movement:' as info;
SELECT 
    sm.id,
    sm.ref_id,
    sm.qty,
    sm.qty_as_input,
    sm.satuan_as_input,
    sm.tanggal
FROM stock_movements sm
WHERE sm.item_type = 'material'
AND sm.item_id = 2
AND sm.ref_type = 'production';

-- Expected result: Both should show 160 Potong
SELECT 'FIX COMPLETE! Now refresh laporan stok to see 160 Potong instead of 120 Potong' as result;