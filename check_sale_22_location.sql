-- Check if sale 22/04 is in journal_entries
SELECT 'journal_entries' as source, je.id, je.tanggal, je.memo, je.ref_type, je.ref_id
FROM journal_entries je
WHERE je.tanggal = '2026-04-22' AND (je.ref_type = 'sale' OR je.memo LIKE '%Penjualan%');

-- Check if sale 22/04 is in jurnal_umum
SELECT 'jurnal_umum' as source, ju.id, ju.tanggal, ju.keterangan, ju.tipe_referensi, ju.debit, ju.kredit, c.kode_akun
FROM jurnal_umum ju
LEFT JOIN coas c ON c.id = ju.coa_id
WHERE ju.tanggal = '2026-04-22' AND ju.keterangan LIKE '%Penjualan%'
ORDER BY ju.id;
