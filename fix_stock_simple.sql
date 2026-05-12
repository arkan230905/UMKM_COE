-- Fix stock issue for product #2 (Ayam Ketumbar)

-- Add stock layer
INSERT INTO stock_layers (item_type, item_id, qty, remaining_qty, unit_cost, ref_type, ref_id, created_at, updated_at)
VALUES ('product', 2, 20, 20, 35000, 'initial_stock', 0, NOW(), NOW());

-- Add stock movement
INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at)
VALUES ('product', 2, CURDATE(), 'in', 20, 'Pcs', 35000, 700000, 'initial_stock', 0, NOW(), NOW());

-- Update product stock
UPDATE produks SET stok = 20, updated_at = NOW() WHERE id = 2;

-- Verify the fix
SELECT 'Product Info' as info, id, nama_produk, stok FROM produks WHERE id = 2
UNION ALL
SELECT 'Stock Layers' as info, item_id, CONCAT('Qty: ', remaining_qty), CONCAT('Cost: ', unit_cost) FROM stock_layers WHERE item_type = 'product' AND item_id = 2;