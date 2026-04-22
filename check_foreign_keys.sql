-- Check foreign keys referencing coas.kode_akun
SELECT 
    TABLE_NAME, 
    CONSTRAINT_NAME, 
    COLUMN_NAME, 
    REFERENCED_TABLE_NAME, 
    REFERENCED_COLUMN_NAME 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_NAME = 'coas' 
    AND REFERENCED_COLUMN_NAME = 'kode_akun' 
    AND TABLE_SCHEMA = 'eadt_umkm';

-- Check indexes on coas table
SHOW INDEX FROM coas;
