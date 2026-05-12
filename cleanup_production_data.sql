-- Script untuk membersihkan data produksi yang tidak valid
-- Jalankan setelah memeriksa dengan check_production_data.sql

-- Jika tabel produk tidak memiliki company_id, maka data produksi adalah GLOBAL
-- Kita perlu membersihkan data produksi yang dibuat oleh seeder

-- HAPUS SEMUA DATA PRODUKSI DARI SEEDER (data global)
-- Data ini dibuat oleh CompleteBalancedDataSeeder dan tidak valid untuk user baru

-- 1. Hapus detail produksi
DELETE FROM produksi_details;

-- 2. Hapus header produksi  
DELETE FROM produksis;

-- 3. Hapus produk yang dibuat oleh seeder (jika tidak ada company_id)
-- PERHATIKAN: Ini akan menghapus SEMUA produk yang tidak memiliki company_id
-- Jika ada produk valid tanpa company_id, jangan jalankan ini
DELETE FROM produk WHERE company_id IS NULL;

-- 4. Jika tabel produk tidak memiliki company_id sama sekali, maka hapus semua produk
-- dan biarkan user membuat produk baru
-- DELETE FROM produk;

-- 5. Hapus data transaksi lain yang mungkin terkait
DELETE FROM pembelian_details;
DELETE FROM pembelians;
DELETE FROM penjualan_details;  
DELETE FROM penjualans;

-- 6. Hapus jurnal yang terkait transaksi
DELETE FROM journal_lines 
WHERE journal_entry_id IN (
    SELECT id FROM journal_entries 
    WHERE ref_type IN ('purchase', 'sale', 'production', 'production_finish', 'production_material', 'production_labor_overhead')
);

DELETE FROM journal_entries 
WHERE ref_type IN ('purchase', 'sale', 'production', 'production_finish', 'production_material', 'production_labor_overhead');

-- 7. Reset stock bahan baku ke 0 (jika ada data seeder)
UPDATE bahan_bakus SET stok = 0 WHERE company_id IS NULL;

-- 8. Reset stock produk ke 0 (jika ada data seeder)  
UPDATE produks SET stok = 0 WHERE company_id IS NULL;

-- CATATAN PENTING:
-- - Backup database terlebih dahulu sebelum menjalankan script ini
-- - Script ini akan membersihkan data sample yang dibuat oleh seeder
-- - Data COA dan master data lainnya akan dipertahankan
