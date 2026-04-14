-- ===================================================================
-- SCRIPT SQL UNTUK MEMPERBAIKI MASALAH PENYUSUTAN
-- Berdasarkan data jurnal umum yang menunjukkan ketidaksesuaian
-- ===================================================================

-- 1. PERIKSA DATA ASET SAAT INI
SELECT 
    id,
    nama_aset,
    harga_perolehan,
    biaya_perolehan,
    nilai_residu,
    umur_manfaat,
    metode_penyusutan,
    penyusutan_per_bulan,
    penyusutan_per_tahun,
    akumulasi_penyusutan,
    nilai_buku
FROM asets 
WHERE nama_aset LIKE '%Mesin%' 
   OR nama_aset LIKE '%Peralatan%' 
   OR nama_aset LIKE '%Kendaraan%'
ORDER BY nama_aset;

-- 2. HITUNG PENYUSUTAN YANG BENAR (METODE GARIS LURUS)
SELECT 
    id,
    nama_aset,
    harga_perolehan,
    COALESCE(biaya_perolehan, 0) as biaya_perolehan,
    COALESCE(nilai_residu, 0) as nilai_residu,
    umur_manfaat,
    -- Total yang akan disusutkan
    (harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) as nilai_disusutkan,
    -- Penyusutan per tahun
    ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / umur_manfaat, 2) as penyusutan_per_tahun_benar,
    -- Penyusutan per bulan
    ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / (umur_manfaat * 12), 2) as penyusutan_per_bulan_benar,
    -- Bandingkan dengan yang tersimpan
    penyusutan_per_bulan as penyusutan_tersimpan,
    -- Selisih
    ABS(penyusutan_per_bulan - ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / (umur_manfaat * 12), 2)) as selisih
FROM asets 
WHERE metode_penyusutan IS NOT NULL 
  AND umur_manfaat > 0
  AND (nama_aset LIKE '%Mesin%' OR nama_aset LIKE '%Peralatan%' OR nama_aset LIKE '%Kendaraan%')
ORDER BY selisih DESC;

-- 3. PERBAIKI NILAI PENYUSUTAN UNTUK METODE GARIS LURUS
UPDATE asets 
SET 
    penyusutan_per_tahun = ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / umur_manfaat, 2),
    penyusutan_per_bulan = ROUND((harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(nilai_residu, 0)) / (umur_manfaat * 12), 2)
WHERE metode_penyusutan = 'garis_lurus' 
  AND umur_manfaat > 0
  AND (nama_aset LIKE '%Mesin%' OR nama_aset LIKE '%Peralatan%' OR nama_aset LIKE '%Kendaraan%');

-- 4. PERIKSA COA YANG DIGUNAKAN UNTUK PENYUSUTAN
SELECT 
    kode_akun,
    nama_akun,
    tipe_akun,
    kategori_akun
FROM coa 
WHERE kode_akun IN ('555', '553', '554', '126', '120', '124')
   OR nama_akun LIKE '%Penyusutan%'
ORDER BY kode_akun;

-- 5. PASTIKAN COA PENYUSUTAN ADA (BUAT JIKA BELUM ADA)
INSERT IGNORE INTO coa (kode_akun, nama_akun, tipe_akun, kategori_akun, saldo_normal, is_active) VALUES
('555', 'BOP TL - Biaya Penyusutan Mesin', 'Expense', 'Biaya Penyusutan', 'debit', 1),
('553', 'BOP TL - Biaya Penyusutan Peralatan', 'Expense', 'Biaya Penyusutan', 'debit', 1),
('554', 'BOP TL - Biaya Penyusutan Kendaraan', 'Expense', 'Biaya Penyusutan', 'debit', 1),
('126', 'Akumulasi Penyusutan Mesin', 'Asset', 'Akumulasi Penyusutan', 'credit', 1),
('120', 'Akumulasi Penyusutan Peralatan', 'Asset', 'Akumulasi Penyusutan', 'credit', 1),
('124', 'Akumulasi Penyusutan Kendaraan', 'Asset', 'Akumulasi Penyusutan', 'credit', 1);

-- 6. UPDATE MAPPING COA UNTUK ASET
-- Aset Mesin
UPDATE asets a
JOIN coa c1 ON c1.kode_akun = '555'  -- Beban Penyusutan Mesin
JOIN coa c2 ON c2.kode_akun = '126'  -- Akumulasi Penyusutan Mesin
SET 
    a.expense_coa_id = c1.id,
    a.accum_depr_coa_id = c2.id
WHERE a.nama_aset LIKE '%Mesin%';

-- Aset Peralatan
UPDATE asets a
JOIN coa c1 ON c1.kode_akun = '553'  -- Beban Penyusutan Peralatan
JOIN coa c2 ON c2.kode_akun = '120'  -- Akumulasi Penyusutan Peralatan
SET 
    a.expense_coa_id = c1.id,
    a.accum_depr_coa_id = c2.id
WHERE a.nama_aset LIKE '%Peralatan%';

-- Aset Kendaraan
UPDATE asets a
JOIN coa c1 ON c1.kode_akun = '554'  -- Beban Penyusutan Kendaraan
JOIN coa c2 ON c2.kode_akun = '124'  -- Akumulasi Penyusutan Kendaraan
SET 
    a.expense_coa_id = c1.id,
    a.accum_depr_coa_id = c2.id
WHERE a.nama_aset LIKE '%Kendaraan%';

-- 7. PERIKSA JURNAL PENYUSUTAN YANG ADA
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

-- 8. HITUNG AKUMULASI PENYUSUTAN DARI JURNAL
SELECT 
    CASE 
        WHEN ju.keterangan LIKE '%Mesin%' THEN 'Mesin'
        WHEN ju.keterangan LIKE '%Peralatan%' THEN 'Peralatan'
        WHEN ju.keterangan LIKE '%Kendaraan%' THEN 'Kendaraan'
        ELSE 'Lainnya'
    END as kategori_aset,
    SUM(CASE WHEN ju.debit > 0 THEN ju.debit ELSE 0 END) as total_beban_penyusutan,
    SUM(CASE WHEN ju.kredit > 0 THEN ju.kredit ELSE 0 END) as total_akumulasi_penyusutan,
    COUNT(*) as jumlah_jurnal
FROM jurnal_umum ju
WHERE ju.keterangan LIKE '%Penyusutan%'
GROUP BY kategori_aset
ORDER BY total_beban_penyusutan DESC;

-- 9. UPDATE AKUMULASI PENYUSUTAN DI TABEL ASET BERDASARKAN JURNAL
-- (Hanya jika menggunakan tabel jurnal_umum)
UPDATE asets a
SET akumulasi_penyusutan = (
    SELECT COALESCE(SUM(ju.debit), 0)
    FROM jurnal_umum ju
    WHERE ju.keterangan LIKE CONCAT('%', a.nama_aset, '%')
      AND ju.keterangan LIKE '%Penyusutan%'
      AND ju.debit > 0
)
WHERE a.metode_penyusutan IS NOT NULL;

-- 10. UPDATE NILAI BUKU BERDASARKAN AKUMULASI PENYUSUTAN
UPDATE asets 
SET nilai_buku = GREATEST(
    (harga_perolehan + COALESCE(biaya_perolehan, 0) - COALESCE(akumulasi_penyusutan, 0)),
    COALESCE(nilai_residu, 0)
)
WHERE metode_penyusutan IS NOT NULL;

-- 11. VALIDASI HASIL AKHIR
SELECT 
    a.nama_aset,
    a.harga_perolehan + COALESCE(a.biaya_perolehan, 0) as total_perolehan,
    a.nilai_residu,
    a.umur_manfaat,
    a.penyusutan_per_bulan,
    a.akumulasi_penyusutan,
    a.nilai_buku,
    -- Validasi perhitungan
    ROUND((a.harga_perolehan + COALESCE(a.biaya_perolehan, 0) - COALESCE(a.nilai_residu, 0)) / (a.umur_manfaat * 12), 2) as penyusutan_seharusnya,
    -- Status
    CASE 
        WHEN ABS(a.penyusutan_per_bulan - ROUND((a.harga_perolehan + COALESCE(a.biaya_perolehan, 0) - COALESCE(a.nilai_residu, 0)) / (a.umur_manfaat * 12), 2)) < 0.01 
        THEN 'OK' 
        ELSE 'PERLU PERBAIKAN' 
    END as status
FROM asets a
WHERE a.metode_penyusutan IS NOT NULL
  AND (a.nama_aset LIKE '%Mesin%' OR a.nama_aset LIKE '%Peralatan%' OR a.nama_aset LIKE '%Kendaraan%')
ORDER BY a.nama_aset;

-- ===================================================================
-- CATATAN PENTING:
-- 1. Backup database sebelum menjalankan script ini
-- 2. Jalankan query SELECT terlebih dahulu untuk melihat data
-- 3. Jalankan UPDATE hanya setelah yakin data sudah benar
-- 4. Setelah perbaikan, post ulang jurnal penyusutan bulan ini
-- ===================================================================