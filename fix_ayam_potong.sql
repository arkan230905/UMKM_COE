-- Update pembelian_details ID 1: ubah jumlah_satuan_utama dari 50 ke 40 dan isi manual_conversion_data
UPDATE pembelian_details 
SET 
  jumlah_satuan_utama = 40,
  sub_satuan_id = 6,
  sub_satuan_nama = 'Potong',
  manual_conversion_factor = 3.0000,
  jumlah_sub_satuan = 120.00,
  manual_conversion_data = '{"sub_satuan_id":6,"sub_satuan_nama":"Potong","manual_conversion_factor":3.0000,"jumlah_sub_satuan":120.00,"keterangan":"Konversi manual sub satuan - 1 unit = 3 Potong"}'
WHERE id = 1;

-- Update stock_movements: ubah qty dari 50 ke 40 dan isi manual_conversion_data
UPDATE stock_movements 
SET 
  qty = 40,
  total_cost = 40 * unit_cost,
  manual_conversion_data = '{"sub_satuan_id":6,"sub_satuan_nama":"Potong","manual_conversion_factor":3.0000,"jumlah_sub_satuan":120.00,"keterangan":"Konversi manual sub satuan - 1 unit = 3 Potong"}'
WHERE ref_type = 'purchase' 
AND item_id = 1 
AND item_type = 'material'
AND qty = 50;

-- Verify changes
SELECT 'pembelian_details' as source, id, jumlah_satuan_utama, manual_conversion_data FROM pembelian_details WHERE id = 1;
SELECT 'stock_movements' as source, id, qty, manual_conversion_data FROM stock_movements WHERE item_id = 1 AND item_type = 'material' AND ref_type = 'purchase';
