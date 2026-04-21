-- Update HPP entry for sale 21/04 (Ayam Crispy Macdi)
UPDATE jurnal_umum 
SET coa_id = 124 
WHERE id = 195;

-- Update HPP entry for sale 22/04 (Ayam Goreng Bundo)
UPDATE jurnal_umum 
SET coa_id = 125 
WHERE id = 197;

-- Update HPP entry for sale 23/04 (Ayam Goreng Bundo)
UPDATE jurnal_umum 
SET coa_id = 125 
WHERE id = 199;

-- Add missing sale entry for 22/04 - Debit Kas
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at)
VALUES (107, '2026-04-22', 'Penerimaan tunai penjualan', 1393050.00, 0.00, 'sale#2', 'sale', NOW(), NOW());

-- Add missing sale entry for 22/04 - Credit Pendapatan
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at)
VALUES (143, '2026-04-22', 'Pendapatan penjualan produk', 0.00, 1393050.00, 'sale#2', 'sale', NOW(), NOW());

-- Verify the changes
SELECT 
    ju.id,
    ju.coa_id,
    ju.tanggal,
    ju.keterangan,
    ju.debit,
    ju.kredit,
    c.kode_akun,
    c.nama_akun
FROM jurnal_umum ju
JOIN coas c ON c.id = ju.coa_id
WHERE ju.id IN (195, 197, 199)
ORDER BY ju.id;
