-- Check if the updates were applied
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

-- Check if sale on 22/04 was added
SELECT COUNT(*) as count_22_april
FROM jurnal_umum
WHERE tanggal = '2026-04-22' AND keterangan LIKE '%Penjualan%';
