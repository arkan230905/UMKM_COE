-- Hapus data pembayaran beban dan jurnal terkait
-- Ganti USER_ID dengan ID user yang sedang login (biasanya 1)

-- Hapus jurnal umum yang terkait dengan pembayaran beban
DELETE FROM jurnal_umum 
WHERE tipe_referensi = 'pembayaran_beban' 
AND user_id = 1;

-- Hapus data pembayaran beban (soft delete)
UPDATE pembayaran_bebans 
SET deleted_at = NOW() 
WHERE user_id = 1;

-- Atau hard delete jika ingin menghapus permanen
-- DELETE FROM pembayaran_bebans WHERE user_id = 1;

SELECT 'Cleanup pembayaran beban selesai' as message;
