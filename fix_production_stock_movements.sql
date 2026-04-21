-- Check current production stock movements
SELECT id, item_id, item_type, ref_type, qty, tanggal, manual_conversion_data 
FROM stock_movements 
WHERE item_id = 1 AND item_type = 'material' AND ref_type = 'production'
ORDER BY tanggal;

-- Remove manual_conversion_data from production movements (should use master data only)
UPDATE stock_movements 
SET manual_conversion_data = NULL
WHERE item_id = 1 AND item_type = 'material' AND ref_type = 'production';
