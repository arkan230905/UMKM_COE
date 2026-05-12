-- =====================================================
-- UPDATE BAHAN PENDUKUNG STOCK FROM 50 TO 200
-- Run these commands in your database (phpMyAdmin, MySQL Workbench, etc.)
-- =====================================================

-- First, let's see the current stock levels
SELECT id, nama_bahan, stok, satuan_id FROM bahan_pendukungs ORDER BY id;

-- Update all bahan pendukung records from 50 to 200
UPDATE bahan_pendukungs SET stok = 200 WHERE stok = 50;

-- If you want to update specific items by name (alternative approach):
-- UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Air';
-- UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Minyak Goreng';
-- UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Gas';
-- UPDATE bahan_pendukungs SET stok = 200 WHERE nama_bahan = 'Kemasan';

-- Verify the update worked
SELECT id, nama_bahan, stok, satuan_id FROM bahan_pendukungs ORDER BY id;

-- Optional: Update any other bahan pendukung that might have different stock values
-- UPDATE bahan_pendukungs SET stok = 200 WHERE stok < 200;

-- =====================================================
-- NOTES:
-- 1. This will update the actual database records
-- 2. After running this, refresh your browser
-- 3. The stock report should show 200 instead of 50
-- 4. Production will now work with proper stock validation
-- =====================================================