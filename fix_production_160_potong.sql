-- Fix Production Display Issue: 160 potong vs 120 potong
-- Problem: Transaksi Produksi shows 160 potong, but Laporan Stok shows different value

-- 1. Check current data for production ID 2
SELECT 'Current Production Detail:' as info;
SELECT 
    pd.produksi_id,
    bb.nama_bahan,
    pd.qty_resep,
    pd.satuan_resep,
    pd.qty_konversi
FROM produksi_details pd
JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
WHERE pd.produksi_id = 2 AND bb.nama_bahan = 'Ayam Potong';

SELECT 'Current Stock Movement:' as info;
SELECT 
    sm.ref_id,
    sm.qty,
    sm.qty_as_input,
    sm.satuan_as_input,
    sm.direction,
    sm.tanggal
FROM stock_movements sm
WHERE sm.item_type = 'material' 
AND sm.item_id = 2 
AND sm.ref_type = 'production' 
AND sm.ref_id = 2;

-- 2. Update stock_movements to match production_details
-- This ensures kartu stok shows the same data as transaksi produksi
UPDATE stock_movements 
SET 
    qty_as_input = (
        SELECT pd.qty_resep 
        FROM produksi_details pd 
        JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
        WHERE pd.produksi_id = 2 
        AND bb.nama_bahan = 'Ayam Potong'
        LIMIT 1
    ),
    satuan_as_input = (
        SELECT pd.satuan_resep 
        FROM produksi_details pd 
        JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
        WHERE pd.produksi_id = 2 
        AND bb.nama_bahan = 'Ayam Potong'
        LIMIT 1
    )
WHERE item_type = 'material' 
AND item_id = 2 
AND ref_type = 'production' 
AND ref_id = 2;

-- 3. Verify the fix
SELECT 'After Fix - Production Detail:' as info;
SELECT 
    pd.produksi_id,
    bb.nama_bahan,
    pd.qty_resep,
    pd.satuan_resep,
    pd.qty_konversi
FROM produksi_details pd
JOIN bahan_bakus bb ON pd.bahan_baku_id = bb.id
WHERE pd.produksi_id = 2 AND bb.nama_bahan = 'Ayam Potong';

SELECT 'After Fix - Stock Movement:' as info;
SELECT 
    sm.ref_id,
    sm.qty,
    sm.qty_as_input,
    sm.satuan_as_input,
    sm.direction,
    sm.tanggal
FROM stock_movements sm
WHERE sm.item_type = 'material' 
AND sm.item_id = 2 
AND sm.ref_type = 'production' 
AND sm.ref_id = 2;

-- Expected result: Both should show 160 Potong