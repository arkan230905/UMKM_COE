-- Check all indexes on pegawais
SHOW INDEX FROM pegawais;

-- Check if budi@gmail.com exists with user_id=4
SELECT id, nama, email, user_id FROM pegawais WHERE email = 'budi@gmail.com';

-- Drop global email unique if still exists (try all possible names)
ALTER TABLE pegawais DROP INDEX IF EXISTS `pegawais_email_unique`;
ALTER TABLE pegawais DROP INDEX IF EXISTS `email`;

-- Verify composite unique exists, add if not
SELECT COUNT(*) as composite_exists 
FROM information_schema.statistics 
WHERE table_schema = DATABASE() 
AND table_name = 'pegawais' 
AND index_name = 'pegawais_email_user_id_unique';

-- Test insert with user_id=4 (should succeed)
INSERT IGNORE INTO pegawais (nama, email, user_id, jabatan, jenis_kelamin, alamat, no_telepon, gaji_pokok, tarif_per_jam, tunjangan, asuransi, bank, nomor_rekening, nama_rekening, kode_pegawai, created_at, updated_at)
VALUES ('Test Insert', 'budi@gmail.com', 4, 'Test', 'L', 'Test', '000', 0, 0, 0, 0, 'Test', '000', 'Test', 'TEST999', NOW(), NOW());

-- Check result
SELECT id, nama, email, user_id FROM pegawais WHERE email = 'budi@gmail.com';

-- Clean up test record
DELETE FROM pegawais WHERE kode_pegawai = 'TEST999';

-- Final state
SELECT id, nama, email, user_id FROM pegawais;
