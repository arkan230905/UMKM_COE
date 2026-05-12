-- Check for orphaned data in bahan_bakus
SELECT 'Bahan Baku without user_id:' as info;
SELECT id, nama_bahan, created_at FROM bahan_bakus WHERE user_id IS NULL;

-- Check for orphaned data in bahan_pendukungs
SELECT 'Bahan Pendukung without user_id:' as info;
SELECT id, nama_bahan, created_at FROM bahan_pendukungs WHERE user_id IS NULL;

-- Show all users
SELECT 'Available users:' as info;
SELECT id, name, email, created_at FROM users ORDER BY id;

-- Count orphaned records
SELECT 'Summary:' as info;
SELECT 
    (SELECT COUNT(*) FROM bahan_bakus WHERE user_id IS NULL) as orphaned_bahan_baku,
    (SELECT COUNT(*) FROM bahan_pendukungs WHERE user_id IS NULL) as orphaned_bahan_pendukung,
    (SELECT COUNT(*) FROM users) as total_users;
