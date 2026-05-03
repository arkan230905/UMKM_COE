-- Fix foto produk yang NULL
-- Assign foto yang tersedia ke produk yang fotonya kosong

-- Cek kondisi sekarang
SELECT id, nama_produk, foto FROM produks;

-- Update produk yang fotonya NULL dengan foto yang tersedia di storage
UPDATE produks 
SET foto = 'produk/CSGXKdrDlMQmQksG0lWUzoHcSjE8eTxfEkkN2OHA.jpg'
WHERE foto IS NULL OR foto = '';

-- Verifikasi
SELECT id, nama_produk, foto FROM produks;
