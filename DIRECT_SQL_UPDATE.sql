-- =====================================================
-- DIRECT SQL UPDATE FOR BAHAN PENDUKUNG STOCK
-- Copy and paste these commands into your database tool
-- =====================================================

-- First, check current stock values
SELECT id, nama_bahan, stok, harga_satuan FROM bahan_pendukungs ORDER BY id;

-- Update ALL bahan pendukung stock to 200
UPDATE bahan_pendukungs SET stok = 200;

-- Verify the update
SELECT id, nama_bahan, stok, harga_satuan FROM bahan_pendukungs ORDER BY id;

-- If you want to update specific items only:
-- UPDATE bahan_pendukungs SET stok = 200 WHERE id = 13; -- Air
-- UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Minyak Goreng';
-- UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Gas';
-- UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Kemasan';

-- =====================================================
-- IMPORTANT: After running this SQL:
-- 1. Refresh your browser (Ctrl+F5)
-- 2. Clear browser cache if needed
-- 3. The stock report should show 200 instead of 50
-- =====================================================