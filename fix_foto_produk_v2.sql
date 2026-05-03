-- Update semua produk yang fotonya tidak ada di storage
-- Gunakan file yang sudah ada: CSGXKdrDlMQmQksG0lWUzoHcSjE8eTxfEkkN2OHA.jpg

UPDATE produks 
SET foto = 'produk/CSGXKdrDlMQmQksG0lWUzoHcSjE8eTxfEkkN2OHA.jpg'
WHERE id = 1;

SELECT id, nama_produk, foto FROM produks;
