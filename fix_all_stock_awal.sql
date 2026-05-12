-- FIX ALL MISSING INITIAL STOCK (SALDO AWAL)
-- Set tanggal saldo awal ke 1 April 2026

-- 1. BAHAN BAKU - Add missing initial stock
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at)
SELECT 
    'material' as item_type,
    bb.id as item_id,
    '2026-04-01' as tanggal,
    'in' as direction,
    GREATEST(bb.stok + COALESCE(usage.total_usage, 0), 0) as qty,
    'Unit' as satuan,
    50000 as unit_cost,
    GREATEST(bb.stok + COALESCE(usage.total_usage, 0), 0) * 50000 as total_cost,
    'initial_stock' as ref_type,
    0 as ref_id,
    '2026-04-01 00:00:00' as created_at,
    '2026-04-01 00:00:00' as updated_at
FROM bahan_bakus bb
LEFT JOIN (
    SELECT item_id, SUM(qty) as total_usage
    FROM stock_movements 
    WHERE item_type='material' AND ref_type='production' AND direction='out'
    GROUP BY item_id
) usage ON usage.item_id = bb.id
WHERE bb.id NOT IN (
    SELECT DISTINCT item_id 
    FROM stock_movements 
    WHERE item_type='material' AND ref_type='initial_stock'
)
AND bb.id IN (
    SELECT DISTINCT bahan_baku_id 
    FROM produksi_details 
    WHERE bahan_baku_id IS NOT NULL
);

-- 2. BAHAN PENDUKUNG - Add missing initial stock
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at)
SELECT 
    'support' as item_type,
    bp.id as item_id,
    '2026-04-01' as tanggal,
    'in' as direction,
    GREATEST(COALESCE(bp.stok, 200) + COALESCE(usage.total_usage, 0), 0) as qty,
    'Unit' as satuan,
    1000 as unit_cost,
    GREATEST(COALESCE(bp.stok, 200) + COALESCE(usage.total_usage, 0), 0) * 1000 as total_cost,
    'initial_stock' as ref_type,
    0 as ref_id,
    '2026-04-01 00:00:00' as created_at,
    '2026-04-01 00:00:00' as updated_at
FROM bahan_pendukungs bp
LEFT JOIN (
    SELECT item_id, SUM(qty) as total_usage
    FROM stock_movements 
    WHERE item_type='support' AND ref_type='production' AND direction='out'
    GROUP BY item_id
) usage ON usage.item_id = bp.id
WHERE bp.id NOT IN (
    SELECT DISTINCT item_id 
    FROM stock_movements 
    WHERE item_type='support' AND ref_type='initial_stock'
)
AND bp.id IN (
    SELECT DISTINCT bahan_pendukung_id 
    FROM produksi_details 
    WHERE bahan_pendukung_id IS NOT NULL
);

-- 3. Add stock layers for remaining stock
-- Delete old stock layers first
DELETE FROM stock_layers WHERE item_type IN ('material', 'support');

-- Add stock layers for materials
INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at)
SELECT 
    'material' as item_type,
    bb.id as item_id,
    '2026-04-01' as tanggal,
    bb.stok as remaining_qty,
    50000 as unit_cost,
    'Unit' as satuan,
    'initial_stock' as ref_type,
    0 as ref_id,
    '2026-04-01 00:00:00' as created_at,
    '2026-04-01 00:00:00' as updated_at
FROM bahan_bakus bb
WHERE bb.stok > 0
AND bb.id IN (
    SELECT DISTINCT bahan_baku_id 
    FROM produksi_details 
    WHERE bahan_baku_id IS NOT NULL
);

-- Add stock layers for support materials
INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at)
SELECT 
    'support' as item_type,
    bp.id as item_id,
    '2026-04-01' as tanggal,
    COALESCE(bp.stok, 200) as remaining_qty,
    1000 as unit_cost,
    'Unit' as satuan,
    'initial_stock' as ref_type,
    0 as ref_id,
    '2026-04-01 00:00:00' as created_at,
    '2026-04-01 00:00:00' as updated_at
FROM bahan_pendukungs bp
WHERE COALESCE(bp.stok, 200) > 0
AND bp.id IN (
    SELECT DISTINCT bahan_pendukung_id 
    FROM produksi_details 
    WHERE bahan_pendukung_id IS NOT NULL
);

-- 4. Update ALL existing initial stock dates to April 1, 2026
UPDATE stock_movements 
SET tanggal = '2026-04-01', created_at = '2026-04-01 00:00:00', updated_at = '2026-04-01 00:00:00' 
WHERE ref_type = 'initial_stock';

UPDATE stock_layers 
SET tanggal = '2026-04-01', created_at = '2026-04-01 00:00:00', updated_at = '2026-04-01 00:00:00' 
WHERE ref_type = 'initial_stock';

-- 5. Update master stock for support materials
UPDATE bahan_pendukungs SET stok = 200 WHERE stok IS NULL OR stok = 0;

-- 6. Verification queries
SELECT 'BAHAN BAKU WITH INITIAL STOCK:' as info;
SELECT bb.id, bb.nama_bahan, bb.stok, 
       (SELECT COUNT(*) FROM stock_movements sm WHERE sm.item_type='material' AND sm.item_id=bb.id AND sm.ref_type='initial_stock') as has_initial
FROM bahan_bakus bb 
WHERE bb.id IN (SELECT DISTINCT bahan_baku_id FROM produksi_details WHERE bahan_baku_id IS NOT NULL)
ORDER BY bb.id;

SELECT 'BAHAN PENDUKUNG WITH INITIAL STOCK:' as info;
SELECT bp.id, bp.nama_bahan, bp.stok,
       (SELECT COUNT(*) FROM stock_movements sm WHERE sm.item_type='support' AND sm.item_id=bp.id AND sm.ref_type='initial_stock') as has_initial
FROM bahan_pendukungs bp 
WHERE bp.id IN (SELECT DISTINCT bahan_pendukung_id FROM produksi_details WHERE bahan_pendukung_id IS NOT NULL)
ORDER BY bp.id;