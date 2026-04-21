-- Update pembelian_details untuk Ayam Potong dari 50 ke 40 Kilogram
UPDATE pembelian_details 
SET jumlah_satuan_utama = 40 
WHERE bahan_baku_id = 1 AND jumlah_satuan_utama = 50;

-- Update stock_movements untuk Ayam Potong dari 50 ke 40 Kilogram
UPDATE stock_movements 
SET qty = 40, total_cost = 40 * unit_cost 
WHERE ref_type = 'purchase' 
AND item_id = 1 
AND item_type = 'material'
AND qty = 50;

-- Verify the changes
SELECT 'pembelian_details' as table_name, jumlah_satuan_utama FROM pembelian_details WHERE bahan_baku_id = 1;
SELECT 'stock_movements' as table_name, qty FROM stock_movements WHERE item_id = 1 AND item_type = 'material' AND ref_type = 'purchase';
