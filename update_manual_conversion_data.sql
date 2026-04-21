-- Update pembelian_details ID 1 dengan manual_conversion_data
UPDATE pembelian_details 
SET 
  sub_satuan_id = 6,
  sub_satuan_nama = 'Potong',
  manual_conversion_factor = 3.0000,
  jumlah_sub_satuan = 120.00,
  manual_conversion_data = '{"sub_satuan_id":6,"sub_satuan_nama":"Potong","manual_conversion_factor":3.0000,"jumlah_sub_satuan":120.00,"keterangan":"Konversi manual sub satuan - 1 unit = 3 Potong"}'
WHERE id = 1;

-- Update stock_movements dengan manual_conversion_data
UPDATE stock_movements 
SET 
  manual_conversion_data = '{"sub_satuan_id":6,"sub_satuan_nama":"Potong","manual_conversion_factor":3.0000,"jumlah_sub_satuan":120.00,"keterangan":"Konversi manual sub satuan - 1 unit = 3 Potong"}'
WHERE ref_type = 'purchase' 
AND item_id = 1 
AND item_type = 'material'
AND qty = 40;

-- Verify
SELECT id, jumlah_satuan_utama, sub_satuan_id, sub_satuan_nama, manual_conversion_factor, jumlah_sub_satuan, manual_conversion_data 
FROM pembelian_details 
WHERE id = 1;
