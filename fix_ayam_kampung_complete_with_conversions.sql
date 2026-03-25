-- COMPLETE FIX FOR AYAM KAMPUNG WITH MULTI-UNIT CONVERSIONS
-- This script fixes the stock data and ensures proper unit conversions

-- ============================================
-- STEP 1: CHECK CURRENT CONVERSION SETTINGS
-- ============================================
SELECT 'Current Satuan Conversions for Ayam Kampung:' as info;
SELECT s.id, s.nama, s.kode, 
       sk.satuan_id as parent_satuan_id, 
       sk.nilai_konversi,
       (SELECT nama FROM satuans WHERE id = sk.satuan_id) as parent_satuan_nama
FROM satuans s
LEFT JOIN satuan_konversis sk ON s.id = sk.sub_satuan_id
WHERE s.id IN (
    SELECT satuan_id FROM bahan_bakus WHERE id = 2
    UNION
    SELECT sub_satuan_1 FROM bahan_bakus WHERE id = 2
    UNION
    SELECT sub_satuan_2 FROM bahan_bakus WHERE id = 2
    UNION
    SELECT sub_satuan_3 FROM bahan_bakus WHERE id = 2
);

-- ============================================
-- STEP 2: VERIFY BAHAN BAKU CONFIGURATION
-- ============================================
SELECT 'Bahan Baku Configuration:' as info;
SELECT bb.id, bb.nama_bahan, bb.stok,
       s1.nama as satuan_utama,
       s2.nama as sub_satuan_1,
       s3.nama as sub_satuan_2,
       s4.nama as sub_satuan_3
FROM bahan_bakus bb
LEFT JOIN satuans s1 ON bb.satuan_id = s1.id
LEFT JOIN satuans s2 ON bb.sub_satuan_1 = s2.id
LEFT JOIN satuans s3 ON bb.sub_satuan_2 = s3.id
LEFT JOIN satuans s4 ON bb.sub_satuan_3 = s4.id
WHERE bb.id = 2;

-- ============================================
-- STEP 3: CHECK PROBLEMATIC STOCK DATA
-- ============================================
SELECT 'BEFORE FIX - Stock Movements:' as info;
SELECT id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

SELECT 'BEFORE FIX - Stock Layers (causing incorrect totals):' as info;
SELECT id, tanggal, remaining_qty, satuan, unit_cost, ref_type,
       (SELECT SUM(remaining_qty) FROM stock_layers WHERE item_type = 'material' AND item_id = 2) as total_sum
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

SELECT 'BEFORE FIX - Master Stock:' as info;
SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2;

-- ============================================
-- STEP 4: COMPLETE CLEANUP
-- ============================================
DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2;
DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2;
UPDATE bahan_bakus SET stok = 0 WHERE id = 2;

-- ============================================
-- STEP 5: CREATE CORRECT INITIAL STOCK
-- Stock is stored in PRIMARY UNIT (Ekor)
-- ============================================

-- Initial Stock: 30 Ekor at Rp 45,000 per Ekor (March 1st, 2026)
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- Create stock layer for initial stock
INSERT INTO stock_layers 
(item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00');

-- ============================================
-- STEP 6: ADD PRODUCTION CONSUMPTION
-- Production consumes 2 Ekor (March 11th, 2026)
-- ============================================

-- Production OUT: 2 Ekor at Rp 45,000 per Ekor
INSERT INTO stock_movements 
(item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) 
VALUES 
('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05');

-- Update stock layer: 30 - 2 = 28 Ekor remaining
UPDATE stock_layers 
SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05'
WHERE item_type = 'material' AND item_id = 2;

-- ============================================
-- STEP 7: UPDATE MASTER DATA
-- ============================================
UPDATE bahan_bakus 
SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' 
WHERE id = 2;

-- ============================================
-- STEP 8: VERIFICATION
-- ============================================
SELECT 'AFTER FIX - Stock Movements (should show 2 records):' as info;
SELECT id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id 
FROM stock_movements 
WHERE item_type = 'material' AND item_id = 2 
ORDER BY tanggal;

SELECT 'AFTER FIX - Stock Layers (should show 1 record with 28 Ekor):' as info;
SELECT id, tanggal, remaining_qty, satuan, unit_cost, ref_type,
       (SELECT SUM(remaining_qty) FROM stock_layers WHERE item_type = 'material' AND item_id = 2) as total_sum
FROM stock_layers 
WHERE item_type = 'material' AND item_id = 2;

SELECT 'AFTER FIX - Master Stock (should show 28):' as info;
SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2;

-- ============================================
-- STEP 9: VERIFY CONVERSIONS
-- ============================================
SELECT 'Expected Stock in All Units:' as info;
SELECT 
    28.0000 as qty_ekor,
    'Ekor' as unit_ekor,
    45000.0000 as price_ekor,
    1260000.00 as total_ekor,
    
    (28.0000 * 6) as qty_potong,
    'Potong' as unit_potong,
    (45000.0000 / 6) as price_potong,
    1260000.00 as total_potong,
    
    (28.0000 * 1.5) as qty_kg,
    'Kilogram' as unit_kg,
    (45000.0000 / 1.5) as price_kg,
    1260000.00 as total_kg,
    
    (28.0000 * 1500) as qty_gram,
    'Gram' as unit_gram,
    (45000.0000 / 1500) as price_gram,
    1260000.00 as total_gram;

-- ============================================
-- EXPECTED RESULTS SUMMARY
-- ============================================
/*
KARTU STOK - AYAM KAMPUNG (SATUAN EKOR)
========================================
Tanggal     | Stok Awal              | Pembelian | Produksi              | Total Stok
01/03/2026  | 30 Ekor @ Rp 45,000    |           |                       | 30 Ekor @ Rp 45,000 = Rp 1,350,000
            | = Rp 1,350,000         |           |                       |
11/03/2026  |                        |           | 2 Ekor @ Rp 45,000    | 28 Ekor @ Rp 45,000 = Rp 1,260,000
            |                        |           | = Rp 90,000           |

KARTU STOK - AYAM KAMPUNG (SATUAN POTONG)
==========================================
Tanggal     | Stok Awal              | Pembelian | Produksi              | Total Stok
01/03/2026  | 180 Potong @ Rp 7,500  |           |                       | 180 Potong @ Rp 7,500 = Rp 1,350,000
            | = Rp 1,350,000         |           |                       |
11/03/2026  |                        |           | 12 Potong @ Rp 7,500  | 168 Potong @ Rp 7,500 = Rp 1,260,000
            |                        |           | = Rp 90,000           |

KARTU STOK - AYAM KAMPUNG (SATUAN KILOGRAM)
============================================
Tanggal     | Stok Awal              | Pembelian | Produksi              | Total Stok
01/03/2026  | 45 Kg @ Rp 30,000      |           |                       | 45 Kg @ Rp 30,000 = Rp 1,350,000
            | = Rp 1,350,000         |           |                       |
11/03/2026  |                        |           | 3 Kg @ Rp 30,000      | 42 Kg @ Rp 30,000 = Rp 1,260,000
            |                        |           | = Rp 90,000           |

KARTU STOK - AYAM KAMPUNG (SATUAN GRAM)
========================================
Tanggal     | Stok Awal              | Pembelian | Produksi              | Total Stok
01/03/2026  | 45,000 Gram @ Rp 30    |           |                       | 45,000 Gram @ Rp 30 = Rp 1,350,000
            | = Rp 1,350,000         |           |                       |
11/03/2026  |                        |           | 3,000 Gram @ Rp 30    | 42,000 Gram @ Rp 30 = Rp 1,260,000
            |                        |           | = Rp 90,000           |

CONVERSION FORMULAS:
====================
1 Ekor = 6 Potong
1 Ekor = 1.5 Kilogram
1 Ekor = 1,500 Gram
1 Kilogram = 1,000 Gram

PRICE CONVERSIONS:
==================
Ekor:     Rp 45,000
Potong:   Rp 7,500 (45,000 ÷ 6)
Kilogram: Rp 30,000 (45,000 ÷ 1.5)
Gram:     Rp 30 (30,000 ÷ 1,000)

NOTE: The stock is stored in the PRIMARY UNIT (Ekor) in the database.
The view/report should handle the conversion to display in different units.
*/