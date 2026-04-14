-- ===================================================================
-- SCRIPT LANGSUNG UNTUK UPDATE JURNAL APRIL 2026
-- Mengubah nilai yang salah menjadi nilai yang benar
-- ===================================================================

-- BACKUP JURNAL LAMA (OPSIONAL)
CREATE TABLE IF NOT EXISTS jurnal_umum_backup_20260430 AS
SELECT * FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
  AND keterangan LIKE '%Penyusutan%';

-- LIHAT JURNAL YANG AKAN DIUPDATE
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
ORDER BY debit DESC, kredit DESC;

-- ===================================================================
-- UPDATE JURNAL MESIN PRODUKSI
-- Dari Rp 1.416.667 menjadi Rp 1.333.333
-- ===================================================================

-- Update debit (beban penyusutan)
UPDATE jurnal_umum 
SET debit = 1333333.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Mesin%'
  AND debit = 1416667.00;

-- Update kredit (akumulasi penyusutan)
UPDATE jurnal_umum 
SET kredit = 1333333.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Mesin%'
  AND kredit = 1416667.00;

-- ===================================================================
-- UPDATE JURNAL PERALATAN PRODUKSI
-- Dari Rp 2.833.333 menjadi Rp 659.474
-- ===================================================================

-- Update debit (beban penyusutan)
UPDATE jurnal_umum 
SET debit = 659474.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Peralatan%'
  AND debit = 2833333.00;

-- Update kredit (akumulasi penyusutan)
UPDATE jurnal_umum 
SET kredit = 659474.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Peralatan%'
  AND kredit = 2833333.00;

-- ===================================================================
-- UPDATE JURNAL KENDARAAN
-- Dari Rp 2.361.111 menjadi Rp 888.889
-- ===================================================================

-- Update debit (beban penyusutan)
UPDATE jurnal_umum 
SET debit = 888889.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Kendaraan%'
  AND debit = 2361111.00;

-- Update kredit (akumulasi penyusutan)
UPDATE jurnal_umum 
SET kredit = 888889.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Penyusutan%'
  AND keterangan LIKE '%Kendaraan%'
  AND kredit = 2361111.00;

-- ===================================================================
-- UPDATE DATA ASET AGAR KONSISTEN
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

-- Lihat jurnal setelah update
SELECT 
    'JURNAL SETELAH UPDATE' as status,
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
ORDER BY debit DESC, kredit DESC;

-- Lihat data aset setelah update
SELECT 
    'ASET SETELAH UPDATE' as status,
    nama_aset,
    penyusutan_per_bulan,
    penyusutan_per_tahun,
    akumulasi_penyusutan
FROM asets 
WHERE nama_aset LIKE '%Mesin%Produksi%'
   OR nama_aset LIKE '%Peralatan%Produksi%'
   OR nama_aset LIKE '%Kendaraan%'
ORDER BY nama_aset;

-- Ringkasan perubahan
SELECT 
    'RINGKASAN PERUBAHAN' as status,
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
-- 1. Script ini akan langsung mengubah data di database
-- 2. Pastikan backup sudah dibuat sebelum menjalankan
-- 3. Jalankan di environment testing terlebih dahulu
-- 4. Setelah eksekusi, cek kembali jurnal umum di aplikasi
-- ===================================================================