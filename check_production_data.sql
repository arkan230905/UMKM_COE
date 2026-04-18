-- Script untuk memeriksa data produksi yang bermasalah
-- Jalankan script ini untuk melihat data produksi yang tidak valid

-- 1. Cek apakah tabel produk memiliki company_id
DESCRIBE produks;

-- 2. Cek struktur tabel produksi
DESCRIBE produksis;

-- 3. Lihat semua data produk (untuk melihat apakah ada company_id)
SELECT id, nama_produk, company_id FROM produks ORDER BY id;

-- 4. Lihat data produksi yang ada
SELECT 
    p.id,
    p.tanggal,
    pr.nama_produk,
    pr.company_id,
    p.total_biaya,
    p.status
FROM produksis p
JOIN produks pr ON p.produk_id = pr.id
ORDER BY p.id;

-- 5. Cari produk yang tidak memiliki company_id (NULL)
SELECT id, nama_produk FROM produks WHERE company_id IS NULL;

-- 6. Cari produksi yang terhubung ke produk tanpa company_id
SELECT 
    p.id,
    p.tanggal,
    pr.nama_produk,
    p.total_biaya,
    p.status
FROM produksis p
JOIN produks pr ON p.produk_id = pr.id
WHERE pr.company_id IS NULL
ORDER BY p.id;

-- 7. Cek company yang ada
SELECT id, nama, kode_perusahaan FROM companies ORDER BY id DESC;
