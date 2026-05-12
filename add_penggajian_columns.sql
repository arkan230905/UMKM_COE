-- Add missing columns to penggajians table
USE eadt_umkm;

-- Add tarif_per_jam column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS tarif_per_jam DECIMAL(15,2) DEFAULT 0 AFTER gaji_pokok;

-- Add asuransi column  
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS asuransi DECIMAL(15,2) DEFAULT 0 AFTER tunjangan;

-- Add bonus column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS bonus DECIMAL(15,2) DEFAULT 0 AFTER asuransi;

-- Add coa_kasbank column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS coa_kasbank VARCHAR(10) AFTER tanggal_penggajian;

-- Add status_pembayaran column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS status_pembayaran ENUM('belum_lunas', 'lunas', 'dibatalkan') DEFAULT 'belum_lunas' AFTER coa_kasbank;

-- Add tanggal_dibayar column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS tanggal_dibayar DATE NULL AFTER status_pembayaran;

-- Add metode_pembayaran column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS metode_pembayaran ENUM('transfer', 'tunai', 'cek') NULL AFTER tanggal_dibayar;

-- Add status_posting column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS status_posting ENUM('draft', 'posted') DEFAULT 'draft' AFTER metode_pembayaran;

-- Add tanggal_posting column
ALTER TABLE penggajians ADD COLUMN IF NOT EXISTS tanggal_posting DATETIME NULL AFTER status_posting;

-- Show final structure
DESCRIBE penggajians;