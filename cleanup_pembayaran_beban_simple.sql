-- Hapus data pembayaran beban dan jurnal terkait
-- User ID 1 = user yang sedang login

-- Hapus jurnal umum untuk pembayaran beban
DELETE FROM jurnal_umum 
WHERE tipe_referensi = 'pembayaran_beban' 
AND user_id = 1;

-- Soft delete pembayaran beban
UPDATE pembayaran_bebans 
SET deleted_at = NOW() 
WHERE user_id = 1;

-- Verifikasi hasil
SELECT 'Cleanup completed' AS status;
