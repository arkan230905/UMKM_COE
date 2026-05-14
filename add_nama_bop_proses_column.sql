-- Add nama_bop_proses column to bop_proses table
ALTER TABLE `bop_proses` 
ADD COLUMN `nama_bop_proses` VARCHAR(255) NULL AFTER `id`;

-- Make proses_produksi_id nullable
ALTER TABLE `bop_proses` 
MODIFY COLUMN `proses_produksi_id` BIGINT UNSIGNED NULL;

-- Check the table structure
DESCRIBE `bop_proses`;