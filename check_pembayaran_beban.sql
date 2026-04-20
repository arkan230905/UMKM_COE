-- Check pembayaran beban records
SELECT 
    id,
    tanggal,
    beban_operasional_id,
    jumlah,
    keterangan
FROM pembayaran_bebans
ORDER BY tanggal;
