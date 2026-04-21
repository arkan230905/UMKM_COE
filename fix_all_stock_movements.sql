-- Remove manual_conversion_data from ALL stock movements EXCEPT purchase
UPDATE stock_movements 
SET manual_conversion_data = NULL
WHERE item_id = 1 AND item_type = 'material' AND ref_type != 'purchase';

-- Verify: Check all stock movements for Ayam Potong
SELECT id, ref_type, qty, manual_conversion_data 
FROM stock_movements 
WHERE item_id = 1 AND item_type = 'material'
ORDER BY tanggal, id;
