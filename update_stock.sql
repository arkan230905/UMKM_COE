-- Update bahan pendukung stock from 50 to 200
UPDATE bahan_pendukungs SET stok = 200 WHERE stok = 50;

-- Show updated records
SELECT id, nama_bahan, stok FROM bahan_pendukungs ORDER BY id;