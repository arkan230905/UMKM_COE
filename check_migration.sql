-- Check penggajian data
SELECT 'Penggajian Records' as check_type, COUNT(*) as count FROM penggajians;

-- Check pembayaran beban data
SELECT 'Pembayaran Beban Records' as check_type, COUNT(*) as count FROM pembayaran_bebans;

-- Check journal entries for penggajian
SELECT 'Journal Entries - Penggajian' as check_type, COUNT(*) as count FROM journal_entries WHERE ref_type='penggajian';

-- Check journal entries for pembayaran beban
SELECT 'Journal Entries - Pembayaran Beban' as check_type, COUNT(*) as count FROM journal_entries WHERE ref_type='pembayaran_beban';

-- Show sample penggajian data
SELECT 'Sample Penggajian' as type, id, tanggal_penggajian, total_gaji FROM penggajians LIMIT 3;

-- Show sample pembayaran beban data
SELECT 'Sample Pembayaran Beban' as type, id, tanggal, jumlah FROM pembayaran_bebans LIMIT 3;
