-- Step 1: Verify COA IDs
SELECT id, kode_akun, nama_akun FROM coas WHERE id IN (123, 124, 125);

-- Step 2: Update HPP entries to use correct persediaan accounts
-- Sale 21/04 (Ayam Crispy Macdi) - change from 123 to 124 (1161)
UPDATE jurnal_umum 
SET coa_id = 124 
WHERE id = 195;

-- Sale 22/04 (Ayam Goreng Bundo) - change from 123 to 125 (1162)
UPDATE jurnal_umum 
SET coa_id = 125 
WHERE id = 197;

-- Sale 23/04 (Ayam Goreng Bundo) - change from 123 to 125 (1162)
UPDATE jurnal_umum 
SET coa_id = 125 
WHERE id = 199;

-- Step 3: Create missing sale entry for 22/04 in jurnal_umum
-- Get the COA ID for Kas (should be 107 based on entry 174)
-- Get the COA ID for Pendapatan (should be 143 based on entry 175)
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at)
VALUES 
(107, '2026-04-22', 'Penerimaan tunai penjualan', 1393050.00, 0.00, 'sale#2', 'sale', NOW(), NOW()),
(143, '2026-04-22', 'Pendapatan penjualan produk', 0.00, 1393050.00, 'sale#2', 'sale', NOW(), NOW());

-- Step 4: Verify the fixes
SELECT 
    ju.id,
    ju.tanggal,
    ju.keterangan,
    ju.debit,
    ju.kredit,
    c.kode_akun,
    c.nama_akun
FROM jurnal_umum ju
JOIN coas c ON c.id = ju.coa_id
WHERE ju.tanggal BETWEEN '2026-04-21' AND '2026-04-23'
ORDER BY ju.tanggal, ju.id;
