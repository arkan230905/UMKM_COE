-- SQL untuk menambahkan kolom yang hilang ke tabel produksi_proses
-- Jalankan ini di phpMyAdmin atau database tool Anda

-- Cek struktur tabel saat ini
DESCRIBE produksi_proses;

-- Tambahkan kolom yang hilang
ALTER TABLE produksi_proses 
ADD COLUMN estimasi_durasi DECIMAL(8,2) NULL AFTER status,
ADD COLUMN kapasitas_per_jam DECIMAL(8,2) NULL AFTER estimasi_durasi,
ADD COLUMN tarif_per_jam DECIMAL(10,2) NULL AFTER kapasitas_per_jam;

-- Verifikasi kolom sudah ditambahkan
DESCRIBE produksi_proses;

-- Update existing records dengan nilai default
UPDATE produksi_proses 
SET estimasi_durasi = 1.00, 
    kapasitas_per_jam = 1.00, 
    tarif_per_jam = 0.00 
WHERE estimasi_durasi IS NULL;