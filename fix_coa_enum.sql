-- Script SQL untuk memperbaiki enum tipe_akun COA
-- Jalankan di MySQL/phpMyAdmin

-- Step 1: Update existing 'BEBAN' values to 'Expense' (jika ada)
UPDATE coas SET tipe_akun = 'Expense' WHERE tipe_akun = 'BEBAN';

-- Step 2: Alter table to include all possible enum values
ALTER TABLE coas MODIFY COLUMN tipe_akun ENUM(
    'Asset', 'Aset',
    'Liability', 'Kewajiban', 
    'Equity', 'Ekuitas', 'Modal',
    'Revenue', 'Pendapatan',
    'Expense', 'Beban', 'BEBAN', 'Biaya',
    'Biaya Bahan Baku', 'Biaya Tenaga Kerja Langsung', 
    'Biaya Overhead Pabrik', 'Biaya Tenaga Kerja Tidak Langsung', 
    'BOP Tidak Langsung Lainnya'
) NOT NULL;

-- Step 3: Verify the change
SHOW COLUMNS FROM coas LIKE 'tipe_akun';

-- Step 4: Test the problematic update
UPDATE coas 
SET nama_akun = 'Biaya TENAGA KERJA TIDAK LANGSUNG', 
    tipe_akun = 'BEBAN', 
    tanggal_saldo_awal = '2026-04-01 00:00:00' 
WHERE id = 166;