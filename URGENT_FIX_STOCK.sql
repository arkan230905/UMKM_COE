-- URGENT FIX: Add stock for product #2 (Ayam Ketumbar)
-- Run this in phpMyAdmin or your database tool

-- 1. Add stock layer (this is what the system checks)
INSERT INTO stock_layers (item_type, item_id, qty, remaining_qty, unit_cost, ref_type, ref_id, created_at, updated_at)
VALUES ('product', 2, 20, 20, 35000, 'initial_stock', 0, NOW(), NOW());

-- 2. Add stock movement record (for audit trail)
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at)
VALUES ('product', 2, CURDATE(), 'in', 20, 'Pcs', 35000, 700000, 'initial_stock', 0, NOW(), NOW());

-- 3. Update product master data
UPDATE produks SET stok = 20, updated_at = NOW() WHERE id = 2;

-- 4. Verify the fix
SELECT 'VERIFICATION' as status, 
       (SELECT nama_produk FROM produks WHERE id = 2) as product_name,
       (SELECT stok FROM produks WHERE id = 2) as product_stock,
       (SELECT SUM(remaining_qty) FROM stock_layers WHERE item_type = 'product' AND item_id = 2) as layer_stock;