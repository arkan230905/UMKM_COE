-- First check the structure of penjualans table
DESCRIBE penjualans;

-- Then query sales data with correct column names
SELECT 
    p.id,
    p.nomor_penjualan,
    p.tanggal,
    p.total,
    pd.produk_id,
    pr.nama_produk,
    pr.harga_bom,
    pd.jumlah,
    pd.harga_satuan
FROM penjualans p
LEFT JOIN penjualan_details pd ON pd.penjualan_id = p.id
LEFT JOIN produks pr ON pr.id = pd.produk_id
WHERE p.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY p.tanggal;

-- Check HPP COA
SELECT id, kode_akun, nama_akun FROM coas WHERE kode_akun IN ('1600', '51', '510', '5100');

-- Check finished goods COA
SELECT id, kode_akun, nama_akun FROM coas WHERE kode_akun IN ('1161', '1162', '116');
