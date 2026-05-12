-- ===================================================================
-- SCRIPT SQL LANGSUNG UNTUK MEMPERBAIKI JURNAL APRIL 2026
-- Jalankan langsung di database MySQL/MariaDB
-- ===================================================================

-- Lihat data saat ini
SELECT 'DATA JURNAL SAAT INI' as status;
SELECT 
    id,
    tanggal,
    keterangan,
    debit,
    kredit,
    CASE 
        WHEN keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
        WHEN keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
        WHEN keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
        ELSE 'Lainnya'
    END as kategori
FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
  AND keterangan LIKE '%Penyusutan%'
ORDER BY debit DESC;

-- Backup data lama
CREATE TABLE IF NOT EXISTS jurnal_umum_backup_april_2026_fix AS
SELECT * FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
  AND keterangan LIKE '%Penyusutan%';

-- ===================================================================
-- PERBAIKAN JURNAL
-- ===================================================================

-- 1. Update Mesin Produksi: 1.416.667 → 1.333.333
UPDATE jurnal_umum 
SET debit = 1333333.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Mesin%'
  AND debit = 1416667.00;

UPDATE jurnal_umum 
SET kredit = 1333333.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Mesin%'
  AND kredit = 1416667.00;

-- 2. Update Peralatan Produksi: 2.833.333 → 659.474
UPDATE jurnal_umum 
SET debit = 659474.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Peralatan%'
  AND debit = 2833333.00;

UPDATE jurnal_umum 
SET kredit = 659474.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Peralatan%'
  AND kredit = 2833333.00;

-- 3. Update Kendaraan: 2.361.111 → 888.889
UPDATE jurnal_umum 
SET debit = 888889.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Kendaraan%'
  AND debit = 2361111.00;

UPDATE jurnal_umum 
SET kredit = 888889.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Kendaraan%'
  AND kredit = 2361111.00;

-- ===================================================================
-- UPDATE DATA ASET
-- ===================================================================

-- Update Mesin Produksi
UPDATE asets 
SET penyusutan_per_bulan = 1333333.00,
    penyusutan_per_tahun = 16000000.00
WHERE nama_aset LIKE '%Mesin%'
  AND nama_aset LIKE '%Produksi%';

-- Update Peralatan Produksi
UPDATE asets 
SET penyusutan_per_bulan = 659474.00,
    penyusutan_per_tahun = 7913688.00
WHERE nama_aset LIKE '%Peralatan%'
  AND nama_aset LIKE '%Produksi%';

-- Update Kendaraan
UPDATE asets 
SET penyusutan_per_bulan = 888889.00,
    penyusutan_per_tahun = 10666668.00
WHERE nama_aset LIKE '%Kendaraan%';

-- ===================================================================
-- VALIDASI HASIL
-- ===================================================================

SELECT 'DATA JURNAL SETELAH PERBAIKAN' as status;
SELECT 
    tanggal,
    keterangan,
    debit,
    kredit,
    CASE 
        WHEN keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
        WHEN keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
        WHEN keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
        ELSE 'Lainnya'
    END as kategori
FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
  AND keterangan LIKE '%Penyusutan%'
ORDER BY debit DESC;

SELECT 'DATA ASET SETELAH PERBAIKAN' as status;
SELECT 
    nama_aset,
    penyusutan_per_bulan,
    penyusutan_per_tahun
FROM asets 
WHERE nama_aset LIKE '%Mesin%Produksi%'
   OR nama_aset LIKE '%Peralatan%Produksi%'
   OR nama_aset LIKE '%Kendaraan%'
ORDER BY nama_aset;

SELECT 'RINGKASAN PERBAIKAN' as status;
SELECT 
    CASE 
        WHEN keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
        WHEN keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
        WHEN keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
        ELSE 'Lainnya'
    END as kategori_aset,
    SUM(CASE WHEN debit > 0 THEN debit ELSE 0 END) as total_beban_penyusutan,
    SUM(CASE WHEN kredit > 0 THEN kredit ELSE 0 END) as total_akumulasi_penyusutan
FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
  AND keterangan LIKE '%Penyusutan%'
GROUP BY kategori_aset
ORDER BY total_beban_penyusutan DESC;

-- ===================================================================
-- CATATAN:
-- 1. Script ini sudah siap dijalankan langsung di database
-- 2. Backup otomatis dibuat di tabel jurnal_umum_backup_april_2026_fix
-- 3. Setelah eksekusi, refresh halaman jurnal umum di aplikasi
-- 4. Jika ada masalah, restore dari backup table
-- ===================================================================