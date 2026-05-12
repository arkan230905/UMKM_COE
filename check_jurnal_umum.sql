-- Check jurnal_umum for sales entries
SELECT 
    id,
    tanggal,
    keterangan,
    tipe_transaksi,
    referensi_id,
    coa_id,
    debit,
    kredit
FROM jurnal_umum
WHERE tanggal BETWEEN '2026-04-21' AND '2026-04-23'
AND (tipe_transaksi = 'sale' OR keterangan LIKE '%Penjualan%' OR keterangan LIKE '%HPP%')
ORDER BY tanggal, id;

-- Check if there's a sale record for 22/04
SELECT * FROM penjualans WHERE tanggal = '2026-04-22';

-- Check journal_entries for 22/04
SELECT * FROM journal_entries WHERE tanggal = '2026-04-22';
