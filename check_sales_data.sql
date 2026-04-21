-- Check all sales between April 21-23
SELECT 
    p.id,
    p.nomor_penjualan,
    p.tanggal,
    p.total_harga,
    pd.produk_id,
    pr.nama_produk,
    pd.jumlah,
    pd.harga_satuan,
    pd.subtotal
FROM penjualans p
LEFT JOIN penjualan_details pd ON pd.penjualan_id = p.id
LEFT JOIN produks pr ON pr.id = pd.produk_id
WHERE p.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY p.tanggal, p.id;
