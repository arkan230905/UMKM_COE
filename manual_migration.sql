-- Manual Migration SQL for Production Server
-- Run these queries in order via phpMyAdmin or your database manager

-- ============================================
-- 1. Fix coa_period_balances table
-- ============================================

-- Check if column exists first, then rename
SET @dbname = DATABASE();
SET @tablename = 'coa_period_balances';
SET @columnname = 'coa_period_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'ALTER TABLE coa_period_balances DROP FOREIGN KEY coa_period_balances_coa_period_id_foreign;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'ALTER TABLE coa_period_balances DROP INDEX coa_period_balances_coa_period_id_index;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'ALTER TABLE coa_period_balances DROP INDEX unique_balance_per_company_period;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'ALTER TABLE coa_period_balances CHANGE coa_period_id period_id BIGINT UNSIGNED NOT NULL;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Re-add constraints
ALTER TABLE coa_period_balances 
  ADD CONSTRAINT coa_period_balances_period_id_foreign 
  FOREIGN KEY (period_id) REFERENCES coa_periods(id) ON DELETE CASCADE;

ALTER TABLE coa_period_balances ADD INDEX coa_period_balances_period_id_index (period_id);

ALTER TABLE coa_period_balances 
  ADD UNIQUE unique_balance_per_company_period (company_id, period_id, kode_akun);

-- ============================================
-- 2. Add bank_id to pembelians table
-- ============================================

SET @tablename = 'pembelians';
SET @columnname = 'bank_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN bank_id BIGINT UNSIGNED NULL AFTER payment_method;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add foreign key to coas table
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'ALTER TABLE pembelians ADD CONSTRAINT pembelians_bank_id_foreign FOREIGN KEY (bank_id) REFERENCES coas(id) ON DELETE SET NULL;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add index
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'ALTER TABLE pembelians ADD INDEX pembelians_bank_id_index (bank_id);',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- ============================================
-- 3. Add financial columns to pembelians
-- ============================================

-- Add subtotal
SET @columnname = 'subtotal';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN subtotal DECIMAL(15,2) DEFAULT 0 AFTER tanggal;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add biaya_kirim
SET @columnname = 'biaya_kirim';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN biaya_kirim DECIMAL(15,2) DEFAULT 0 AFTER subtotal;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add ppn_persen
SET @columnname = 'ppn_persen';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN ppn_persen DECIMAL(5,2) DEFAULT 0 AFTER biaya_kirim;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add ppn_nominal
SET @columnname = 'ppn_nominal';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN ppn_nominal DECIMAL(15,2) DEFAULT 0 AFTER ppn_persen;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add total_harga
SET @columnname = 'total_harga';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN total_harga DECIMAL(15,2) DEFAULT 0 AFTER ppn_nominal;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add terbayar
SET @columnname = 'terbayar';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN terbayar DECIMAL(15,2) DEFAULT 0 AFTER total_harga;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add sisa_pembayaran
SET @columnname = 'sisa_pembayaran';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN sisa_pembayaran DECIMAL(15,2) DEFAULT 0 AFTER terbayar;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add status
SET @columnname = 'status';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN status VARCHAR(50) DEFAULT "belum_lunas" AFTER sisa_pembayaran;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add keterangan
SET @columnname = 'keterangan';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = 'pembelians')
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE pembelians ADD COLUMN keterangan TEXT NULL AFTER bank_id;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- ============================================
-- 4. Add columns to stock_movements
-- ============================================

-- Add keterangan
SET @tablename = 'stock_movements';
SET @columnname = 'keterangan';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE stock_movements ADD COLUMN keterangan TEXT NULL AFTER ref_id;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add manual_conversion_data
SET @columnname = 'manual_conversion_data';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) = 0,
  'ALTER TABLE stock_movements ADD COLUMN manual_conversion_data JSON NULL AFTER keterangan;',
  'SELECT 1;'
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- ============================================
-- Verification queries
-- ============================================

SELECT 'Pembelians columns:' as info;
SHOW COLUMNS FROM pembelians;

SELECT 'Stock_movements columns:' as info;
SHOW COLUMNS FROM stock_movements;

SELECT 'Coa_period_balances columns:' as info;
SHOW COLUMNS FROM coa_period_balances;
