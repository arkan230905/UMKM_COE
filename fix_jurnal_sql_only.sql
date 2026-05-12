-- Script SQL murni untuk memperbaiki jurnal_umum April 2026
-- Jalankan langsung di database MySQL

-- Lihat data saat ini
SELECT 'DATA SAAT INI' as status;
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
CREATE TABLE IF NOT EXISTS jurnal_umum_backup_final AS
SELECT * FROM jurnal_umum 
WHERE tanggal = '2026-04-30' 
  AND keterangan LIKE '%Penyusutan%';

-- Update Mesin Produksi: 1.416.667 → 1.333.333
UPDATE jurnal_umum 
SET debit = 1333333.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Mesin%'
  AND debit = 1416667.00;

UPDATE jurnal_umum 
SET kredit = 1333333.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Mesin%'
  AND kredit = 1416667.00;

-- Update Peralatan Produksi: 2.833.333 → 659.474
UPDATE jurnal_umum 
SET debit = 659474.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Peralatan%'
  AND debit = 2833333.00;

UPDATE jurnal_umum 
SET kredit = 659474.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Peralatan%'
  AND kredit = 2833333.00;

-- Update Kendaraan: 2.361.111 → 888.889
UPDATE jurnal_umum 
SET debit = 888889.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Kendaraan%'
  AND debit = 2361111.00;

UPDATE jurnal_umum 
SET kredit = 888889.00
WHERE tanggal = '2026-04-30'
  AND keterangan LIKE '%Kendaraan%'
  AND kredit = 2361111.00;

-- Lihat hasil setelah update
SELECT 'DATA SETELAH UPDATE' as status;
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

-- Ringkasan perubahan
SELECT 'RINGKASAN PERUBAHAN' as status;
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