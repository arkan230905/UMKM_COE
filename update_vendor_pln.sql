-- Script untuk mengupdate kategori vendor PLN ke Bahan Pendukung
-- Jalankan di database MySQL/MariaDB

-- Lihat semua vendor
SELECT id, nama_vendor, kategori FROM vendors;

-- Update vendor PLN ke Bahan Pendukung
UPDATE vendors SET kategori = 'Bahan Pendukung' WHERE nama_vendor LIKE '%PLN%';

-- Verifikasi
SELECT id, nama_vendor, kategori FROM vendors;
