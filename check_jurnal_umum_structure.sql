-- Check jurnal_umum table structure
DESCRIBE jurnal_umum;

-- Find all entries in jurnal_umum for April 21-23
SELECT * FROM jurnal_umum 
WHERE tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY tanggal, id;

-- Check if there are HPP entries in jurnal_umum
SELECT * FROM jurnal_umum 
WHERE keterangan LIKE '%HPP%'
ORDER BY tanggal, id;
