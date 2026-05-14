-- Check if jabatan_id column exists and add it if missing
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'pegawais' 
AND column_name = 'jabatan_id';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE pegawais ADD COLUMN jabatan_id BIGINT UNSIGNED NULL AFTER jenis_kelamin', 
    'SELECT "Column jabatan_id already exists" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint if it doesn't exist
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM information_schema.key_column_usage 
WHERE table_schema = DATABASE() 
AND table_name = 'pegawais' 
AND column_name = 'jabatan_id'
AND referenced_table_name = 'jabatans';

SET @sql = IF(@fk_exists = 0 AND @col_exists = 0, 
    'ALTER TABLE pegawais ADD CONSTRAINT pegawais_jabatan_id_foreign FOREIGN KEY (jabatan_id) REFERENCES jabatans(id) ON DELETE SET NULL', 
    'SELECT "Foreign key already exists or column was not added" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;