-- Fix penggajians table structure
-- Check if columns exist before adding them

-- Add tunjangan_jabatan column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'penggajians' 
     AND table_schema = 'eadt_umkm' 
     AND column_name = 'tunjangan_jabatan') = 0,
    'ALTER TABLE penggajians ADD COLUMN tunjangan_jabatan DECIMAL(15,2) DEFAULT 0 AFTER tunjangan',
    'SELECT "Column tunjangan_jabatan already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tunjangan_transport column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'penggajians' 
     AND table_schema = 'eadt_umkm' 
     AND column_name = 'tunjangan_transport') = 0,
    'ALTER TABLE penggajians ADD COLUMN tunjangan_transport DECIMAL(15,2) DEFAULT 0 AFTER tunjangan_jabatan',
    'SELECT "Column tunjangan_transport already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tunjangan_konsumsi column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'penggajians' 
     AND table_schema = 'eadt_umkm' 
     AND column_name = 'tunjangan_konsumsi') = 0,
    'ALTER TABLE penggajians ADD COLUMN tunjangan_konsumsi DECIMAL(15,2) DEFAULT 0 AFTER tunjangan_transport',
    'SELECT "Column tunjangan_konsumsi already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add total_tunjangan column if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'penggajians' 
     AND table_schema = 'eadt_umkm' 
     AND column_name = 'total_tunjangan') = 0,
    'ALTER TABLE penggajians ADD COLUMN total_tunjangan DECIMAL(15,2) DEFAULT 0 AFTER tunjangan_konsumsi',
    'SELECT "Column total_tunjangan already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show final table structure
DESCRIBE penggajians;