-- ============================================================================
-- SCRIPT TO FIX ORPHANED DATA (Records without user_id)
-- ============================================================================
-- IMPORTANT: Review the output before running UPDATE statements!
-- This script identifies orphaned records and provides UPDATE statements
-- to assign them to the correct user.
-- ============================================================================

-- Step 1: Check all users in the system
SELECT '=== USERS IN SYSTEM ===' as info;
SELECT id, name, email, created_at FROM users ORDER BY id;

-- Step 2: Count orphaned records in each table
SELECT '=== ORPHANED RECORDS COUNT ===' as info;
SELECT 
    'bahan_bakus' as table_name,
    COUNT(*) as orphaned_count
FROM bahan_bakus WHERE user_id IS NULL
UNION ALL
SELECT 
    'bahan_pendukungs' as table_name,
    COUNT(*) as orphaned_count
FROM bahan_pendukungs WHERE user_id IS NULL
UNION ALL
SELECT 
    'vendors' as table_name,
    COUNT(*) as orphaned_count
FROM vendors WHERE user_id IS NULL
UNION ALL
SELECT 
    'produks' as table_name,
    COUNT(*) as orphaned_count
FROM produks WHERE user_id IS NULL
UNION ALL
SELECT 
    'jabatans' as table_name,
    COUNT(*) as orphaned_count
FROM jabatans WHERE user_id IS NULL
UNION ALL
SELECT 
    'bops' as table_name,
    COUNT(*) as orphaned_count
FROM bops WHERE user_id IS NULL
UNION ALL
SELECT 
    'asets' as table_name,
    COUNT(*) as orphaned_count
FROM asets WHERE user_id IS NULL
UNION ALL
SELECT 
    'pelanggans' as table_name,
    COUNT(*) as orphaned_count
FROM pelanggans WHERE user_id IS NULL
UNION ALL
SELECT 
    'btkls' as table_name,
    COUNT(*) as orphaned_count
FROM btkls WHERE user_id IS NULL
UNION ALL
SELECT 
    'pegawais' as table_name,
    COUNT(*) as orphaned_count
FROM pegawais WHERE user_id IS NULL
UNION ALL
SELECT 
    'coas' as table_name,
    COUNT(*) as orphaned_count
FROM coas WHERE user_id IS NULL;

-- Step 3: Show orphaned records details
SELECT '=== ORPHANED BAHAN BAKU ===' as info;
SELECT id, nama_bahan, kode_bahan, created_at 
FROM bahan_bakus 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED BAHAN PENDUKUNG ===' as info;
SELECT id, nama_bahan, created_at 
FROM bahan_pendukungs 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED VENDORS ===' as info;
SELECT id, nama_vendor, kategori, created_at 
FROM vendors 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED PRODUKS ===' as info;
SELECT id, nama_produk, kode_produk, created_at 
FROM produks 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED JABATANS ===' as info;
SELECT id, nama, kode_jabatan, kategori, created_at 
FROM jabatans 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED BOPS ===' as info;
SELECT id, nama_akun, kode_akun, created_at 
FROM bops 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED ASETS ===' as info;
SELECT id, nama_aset, kode_aset, created_at 
FROM asets 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED PELANGGANS ===' as info;
SELECT id, nama_pelanggan, kode_pelanggan, created_at 
FROM pelanggans 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED BTKLS ===' as info;
SELECT id, nama_btkl, kode_proses, created_at 
FROM btkls 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED PEGAWAIS ===' as info;
SELECT id, nama, nik, created_at 
FROM pegawais 
WHERE user_id IS NULL 
ORDER BY created_at;

SELECT '=== ORPHANED COAS ===' as info;
SELECT id, kode_akun, nama_akun, created_at 
FROM coas 
WHERE user_id IS NULL 
ORDER BY created_at;

-- ============================================================================
-- Step 4: UPDATE STATEMENTS (UNCOMMENT AND MODIFY BEFORE RUNNING)
-- ============================================================================
-- IMPORTANT: Replace <USER_ID> with the actual user ID you want to assign
-- Example: If you want to assign all orphaned records to user ID 1:
-- UPDATE bahan_bakus SET user_id = 1 WHERE user_id IS NULL;
-- ============================================================================

-- Assign orphaned bahan_bakus to user ID <USER_ID>
-- UPDATE bahan_bakus SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned bahan_pendukungs to user ID <USER_ID>
-- UPDATE bahan_pendukungs SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned vendors to user ID <USER_ID>
-- UPDATE vendors SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned produks to user ID <USER_ID>
-- UPDATE produks SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned jabatans to user ID <USER_ID>
-- UPDATE jabatans SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned bops to user ID <USER_ID>
-- UPDATE bops SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned asets to user ID <USER_ID>
-- UPDATE asets SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned pelanggans to user ID <USER_ID>
-- UPDATE pelanggans SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned btkls to user ID <USER_ID>
-- UPDATE btkls SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned pegawais to user ID <USER_ID>
-- UPDATE pegawais SET user_id = <USER_ID> WHERE user_id IS NULL;

-- Assign orphaned coas to user ID <USER_ID>
-- UPDATE coas SET user_id = <USER_ID> WHERE user_id IS NULL;

-- ============================================================================
-- Step 5: Verify the fix (Run after UPDATE statements)
-- ============================================================================

-- SELECT '=== VERIFICATION: Count orphaned records after fix ===' as info;
-- SELECT 
--     'bahan_bakus' as table_name,
--     COUNT(*) as orphaned_count
-- FROM bahan_bakus WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'bahan_pendukungs' as table_name,
--     COUNT(*) as orphaned_count
-- FROM bahan_pendukungs WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'vendors' as table_name,
--     COUNT(*) as orphaned_count
-- FROM vendors WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'produks' as table_name,
--     COUNT(*) as orphaned_count
-- FROM produks WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'jabatans' as table_name,
--     COUNT(*) as orphaned_count
-- FROM jabatans WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'bops' as table_name,
--     COUNT(*) as orphaned_count
-- FROM bops WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'asets' as table_name,
--     COUNT(*) as orphaned_count
-- FROM asets WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'pelanggans' as table_name,
--     COUNT(*) as orphaned_count
-- FROM pelanggans WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'btkls' as table_name,
--     COUNT(*) as orphaned_count
-- FROM btkls WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'pegawais' as table_name,
--     COUNT(*) as orphaned_count
-- FROM pegawais WHERE user_id IS NULL
-- UNION ALL
-- SELECT 
--     'coas' as table_name,
--     COUNT(*) as orphaned_count
-- FROM coas WHERE user_id IS NULL;

-- ============================================================================
-- NOTES:
-- ============================================================================
-- 1. This script is READ-ONLY by default (all UPDATE statements are commented)
-- 2. Review the orphaned records first before running any UPDATE
-- 3. Determine which user should own each orphaned record
-- 4. Uncomment and modify the UPDATE statements with the correct user_id
-- 5. Run the verification query to confirm all orphaned records are fixed
-- 6. Test the application to ensure data appears correctly for each user
-- ============================================================================
