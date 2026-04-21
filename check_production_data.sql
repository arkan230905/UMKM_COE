-- Check production data
SELECT id, tanggal, produk_id, qty_per_hari, total_qty, status 
FROM produksis 
ORDER BY tanggal DESC;

-- Check production details (bahan baku yang digunakan)
SELECT id, produksi_id, bahan_baku_id, jumlah, satuan 
FROM produksi_details 
ORDER BY produksi_id DESC;

-- Check stock movements for production
SELECT id, item_id, item_type, ref_type, ref_id, qty, tanggal, direction
FROM stock_movements 
WHERE item_id = 1 AND item_type = 'material' AND ref_type = 'production'
ORDER BY tanggal;
