-- ===================================================================
-- SCRIPT PERBAIKAN PENYUSUTAN APRIL 2026
-- Berdasarkan data aktual yang benar:
-- - Mesin Produksi: Rp 1.333.333
-- - Peralatan Produksi: Rp 659.474
-- - Kendaraan: Rp 888.889
-- ===================================================================

-- 1. BACKUP DATA JURNAL YANG AKAN DIHAPUS (OPSIONAL)
CREATE TABLE IF NOT EXISTS jurnal_umum_backup_april_2026 AS
SELECT * FROM jurnal_umum 
WHERE keterangan LIKE '%Penyusutan%' 
  AND tanggal = '2026-04-30';

-- 2. PERIKSA JURNAL PENYUSUTAN APRIL 2026 YANG SALAH
SELECT 
    ju.id,
    ju.tanggal,
    ju.keterangan,
    ju.debit,
    ju.kredit,
    c.kode_akun,
    c.nama_akun,
    CASE 
        WHEN ju.keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
        WHEN ju.keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
        WHEN ju.keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
        ELSE 'Lainnya'
    END as kategori_aset
FROM jurnal_umum ju
JOIN coa c ON ju.coa_id = c.id
WHERE ju.keterangan LIKE '%Penyusutan%'
  AND ju.tanggal = '2026-04-30'
ORDER BY ju.debit DESC;

-- 3. HAPUS JURNAL PENYUSUTAN APRIL 2026 YANG SALAH
-- Mesin Produksi (yang seharusnya Rp 1.333.333 tapi tercatat Rp 1.416.667)
DELETE FROM jurnal_umum 
WHERE keterangan LIKE '%Penyusutan%Mesin%' 
  AND tanggal = '2026-04-30';

-- Peralatan Produksi (yang seharusnya Rp 659.474 tapi tercatat Rp 2.833.333)
DELETE FROM jurnal_umum 
WHERE keterangan LIKE '%Penyusutan%Peralatan%' 
  AND tanggal = '2026-04-30';

-- Kendaraan (yang seharusnya Rp 888.889 tapi tercatat Rp 2.361.111)
DELETE FROM jurnal_umum 
WHERE keterangan LIKE '%Penyusutan%Kendaraan%' 
  AND tanggal = '2026-04-30';

-- 4. PASTIKAN COA PENYUSUTAN ADA
INSERT IGNORE INTO coa (kode_akun, nama_akun, tipe_akun, kategori_akun, saldo_normal, is_active) VALUES
('555', 'BOP TL - Biaya Penyusutan Mesin', 'Expense', 'Biaya Penyusutan', 'debit', 1),
('553', 'BOP TL - Biaya Penyusutan Peralatan', 'Expense', 'Biaya Penyusutan', 'debit', 1),
('554', 'BOP TL - Biaya Penyusutan Kendaraan', 'Expense', 'Biaya Penyusutan', 'debit', 1),
('126', 'Akumulasi Penyusutan Mesin', 'Asset', 'Akumulasi Penyusutan', 'credit', 1),
('120', 'Akumulasi Penyusutan Peralatan', 'Asset', 'Akumulasi Penyusutan', 'credit', 1),
('124', 'Akumulasi Penyusutan Kendaraan', 'Asset', 'Akumulasi Penyusutan', 'credit', 1);

-- 5. POST JURNAL PENYUSUTAN APRIL 2026 DENGAN NILAI YANG BENAR

-- 5a. Penyusutan Mesin Produksi - Rp 1.333.333
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at) 
SELECT 
    (SELECT id FROM coa WHERE kode_akun = '555') as coa_id,
    '2026-04-30' as tanggal,
    'Penyusutan Aset Mesin Produksi (GL) 2026-04' as keterangan,
    1333333.00 as debit,
    0 as kredit,
    'DEPR-202604-MESIN' as referensi,
    'depr' as tipe_referensi,
    NOW() as created_at,
    NOW() as updated_at;

INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at) 
SELECT 
    (SELECT id FROM coa WHERE kode_akun = '126') as coa_id,
    '2026-04-30' as tanggal,
    'Penyusutan Aset Mesin Produksi (GL) 2026-04' as keterangan,
    0 as debit,
    1333333.00 as kredit,
    'DEPR-202604-MESIN' as referensi,
    'depr' as tipe_referensi,
    NOW() as created_at,
    NOW() as updated_at;

-- 5b. Penyusutan Peralatan Produksi - Rp 659.474
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at) 
SELECT 
    (SELECT id FROM coa WHERE kode_akun = '553') as coa_id,
    '2026-04-30' as tanggal,
    'Penyusutan Aset Peralatan Produksi (SM) 2026-04' as keterangan,
    659474.00 as debit,
    0 as kredit,
    'DEPR-202604-PERALATAN' as referensi,
    'depr' as tipe_referensi,
    NOW() as created_at,
    NOW() as updated_at;

INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at) 
SELECT 
    (SELECT id FROM coa WHERE kode_akun = '120') as coa_id,
    '2026-04-30' as tanggal,
    'Penyusutan Aset Peralatan Produksi (SM) 2026-04' as keterangan,
    0 as debit,
    659474.00 as kredit,
    'DEPR-202604-PERALATAN' as referensi,
    'depr' as tipe_referensi,
    NOW() as created_at,
    NOW() as updated_at;

-- 5c. Penyusutan Kendaraan - Rp 888.889
INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at) 
SELECT 
    (SELECT id FROM coa WHERE kode_akun = '554') as coa_id,
    '2026-04-30' as tanggal,
    'Penyusutan Aset Kendaraan Pengangkut Barang (SYD) 2026-04' as keterangan,
    888889.00 as debit,
    0 as kredit,
    'DEPR-202604-KENDARAAN' as referensi,
    'depr' as tipe_referensi,
    NOW() as created_at,
    NOW() as updated_at;

INSERT INTO jurnal_umum (coa_id, tanggal, keterangan, debit, kredit, referensi, tipe_referensi, created_at, updated_at) 
SELECT 
    (SELECT id FROM coa WHERE kode_akun = '124') as coa_id,
    '2026-04-30' as tanggal,
    'Penyusutan Aset Kendaraan Pengangkut Barang (SYD) 2026-04' as keterangan,
    0 as debit,
    888889.00 as kredit,
    'DEPR-202604-KENDARAAN' as referensi,
    'depr' as tipe_referensi,
    NOW() as created_at,
    NOW() as updated_at;

-- 6. UPDATE PENYUSUTAN_PER_BULAN DI TABEL ASET (JIKA BELUM SESUAI)
UPDATE asets 
SET penyusutan_per_bulan = 1333333.00,
    penyusutan_per_tahun = 16000000.00
WHERE nama_aset LIKE '%Mesin%Produksi%'
  AND ABS(penyusutan_per_bulan - 1333333.00) > 0.01;

UPDATE asets 
SET penyusutan_per_bulan = 659474.00,
    penyusutan_per_tahun = 7913688.00
WHERE nama_aset LIKE '%Peralatan%Produksi%'
  AND ABS(penyusutan_per_bulan - 659474.00) > 0.01;

UPDATE asets 
SET penyusutan_per_bulan = 888889.00,
    penyusutan_per_tahun = 10666668.00
WHERE nama_aset LIKE '%Kendaraan%'
  AND ABS(penyusutan_per_bulan - 888889.00) > 0.01;

-- 7. UPDATE AKUMULASI PENYUSUTAN BERDASARKAN JURNAL
UPDATE asets a
SET akumulasi_penyusutan = (
    SELECT COALESCE(SUM(ju.debit), 0)
    FROM jurnal_umum ju
    JOIN coa c ON ju.coa_id = c.id
    WHERE c.kode_akun = '555'  -- Beban Penyusutan Mesin
      AND ju.keterangan LIKE '%Mesin%'
      AND ju.debit > 0
)
WHERE a.nama_aset LIKE '%Mesin%Produksi%';

UPDATE asets a
SET akumulasi_penyusutan = (
    SELECT COALESCE(SUM(ju.debit), 0)
    FROM jurnal_umum ju
    JOIN coa c ON ju.coa_id = c.id
    WHERE c.kode_akun = '553'  -- Beban Penyusutan Peralatan
      AND ju.keterangan LIKE '%Peralatan%'
      AND ju.debit > 0
)
WHERE a.nama_aset LIKE '%Peralatan%Produksi%';

UPDATE asets a
SET akumulasi_penyusutan = (
    SELECT COALESCE(SUM(ju.debit), 0)
    FROM jurnal_umum ju
    JOIN coa c ON ju.coa_id = c.id
    WHERE c.kode_akun = '554'  -- Beban Penyusutan Kendaraan
      AND ju.keterangan LIKE '%Kendaraan%'
      AND ju.debit > 0
)
WHERE a.nama_aset LIKE '%Kendaraan%';

-- 8. UPDATE NILAI BUKU
UPDATE asets 
SET nilai_buku = GREATEST(
    (harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(akumulasi_penyusutan, 0)),
    COALESCE(nilai_residu, 0)
)
WHERE nama_aset IN (
    SELECT nama_aset FROM (
        SELECT nama_aset FROM asets 
        WHERE nama_aset LIKE '%Mesin%Produksi%' 
           OR nama_aset LIKE '%Peralatan%Produksi%' 
           OR nama_aset LIKE '%Kendaraan%'
    ) as temp
);

-- 9. VALIDASI HASIL PERBAIKAN
SELECT 
    'JURNAL PENYUSUTAN APRIL 2026 - SETELAH PERBAIKAN' as keterangan;

SELECT 
    ju.tanggal,
    ju.keterangan,
    ju.debit,
    ju.kredit,
    c.kode_akun,
    c.nama_akun
FROM jurnal_umum ju
JOIN coa c ON ju.coa_id = c.id
WHERE ju.keterangan LIKE '%Penyusutan%'
  AND ju.tanggal = '2026-04-30'
ORDER BY ju.debit DESC, c.kode_akun;

-- 10. VALIDASI DATA ASET
SELECT 
    'DATA ASET - SETELAH PERBAIKAN' as keterangan;

SELECT 
    nama_aset,
    harga_perolehan + COALESCE(biaya_perolehan, 0) as total_perolehan,
    nilai_residu,
    umur_manfaat,
    metode_penyusutan,
    penyusutan_per_bulan,
    penyusutan_per_tahun,
    akumulasi_penyusutan,
    nilai_buku,
    -- Validasi perhitungan
    CASE 
        WHEN metode_penyusutan = 'garis_lurus' THEN
            ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / (umur_manfaat * 12), 2)
        ELSE penyusutan_per_bulan
    END as penyusutan_seharusnya,
    -- Status konsistensi
    CASE 
        WHEN ABS(penyusutan_per_bulan - 
            CASE 
                WHEN metode_penyusutan = 'garis_lurus' THEN
                    ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / (umur_manfaat * 12), 2)
                ELSE penyusutan_per_bulan
            END) < 0.01 
        THEN 'KONSISTEN' 
        ELSE 'PERLU REVIEW' 
    END as status
FROM asets 
WHERE nama_aset LIKE '%Mesin%Produksi%' 
   OR nama_aset LIKE '%Peralatan%Produksi%' 
   OR nama_aset LIKE '%Kendaraan%'
ORDER BY nama_aset;

-- 11. RINGKASAN PERBAIKAN
SELECT 
    'RINGKASAN PERBAIKAN PENYUSUTAN APRIL 2026' as keterangan;

SELECT 
    CASE 
        WHEN ju.keterangan LIKE '%Mesin%' THEN 'Mesin Produksi'
        WHEN ju.keterangan LIKE '%Peralatan%' THEN 'Peralatan Produksi'
        WHEN ju.keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
        ELSE 'Lainnya'
    END as kategori_aset,
    SUM(CASE WHEN ju.debit > 0 THEN ju.debit ELSE 0 END) as total_beban_penyusutan,
    SUM(CASE WHEN ju.kredit > 0 THEN ju.kredit ELSE 0 END) as total_akumulasi_penyusutan,
    COUNT(*)/2 as jumlah_aset  -- Dibagi 2 karena setiap aset ada 2 jurnal (debit & kredit)
FROM jurnal_umum ju
WHERE ju.keterangan LIKE '%Penyusutan%'
  AND ju.tanggal = '2026-04-30'
GROUP BY kategori_aset
ORDER BY total_beban_penyusutan DESC;

-- ===================================================================
-- CATATAN EKSEKUSI:
-- 1. Backup database sebelum menjalankan script ini
-- 2. Jalankan di environment testing terlebih dahulu
-- 3. Verifikasi setiap section sebelum melanjutkan
-- 4. Koordinasi dengan tim accounting
-- ===================================================================