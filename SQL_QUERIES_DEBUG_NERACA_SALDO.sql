-- ============================================================================
-- SQL QUERIES UNTUK DEBUG NERACA SALDO TIDAK SEIMBANG
-- ============================================================================
-- Ganti {user_id} dengan ID user yang sedang login
-- Ganti {bulan} dan {tahun} dengan periode yang ingin dicek
-- ============================================================================

-- ============================================================================
-- QUERY 1: CEK JURNAL YANG TIDAK SEIMBANG
-- ============================================================================
-- Tujuan: Menemukan jurnal dengan total debit ≠ total kredit
-- Hasil: Jika ada, berarti ada jurnal yang salah input

SELECT 
    tipe_referensi,
    referensi,
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih,
    COUNT(*) as jumlah_baris,
    GROUP_CONCAT(id) as jurnal_ids
FROM jurnal_umum
WHERE user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
GROUP BY tipe_referensi, referensi
HAVING ABS(SUM(debit) - SUM(kredit)) > 0.01
ORDER BY ABS(SUM(debit) - SUM(kredit)) DESC;

-- ============================================================================
-- QUERY 2: CEK DUPLIKASI COA
-- ============================================================================
-- Tujuan: Menemukan COA dengan kode_akun yang sama untuk user yang sama
-- Hasil: Jika ada, berarti ada duplikasi yang perlu dihapus

SELECT 
    user_id,
    kode_akun,
    COUNT(*) as jumlah_duplikasi,
    GROUP_CONCAT(id) as coa_ids,
    GROUP_CONCAT(nama_akun) as nama_akun_list
FROM coas
WHERE user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
GROUP BY user_id, kode_akun
HAVING COUNT(*) > 1;

-- ============================================================================
-- QUERY 3: CEK TOTAL DEBIT VS KREDIT KESELURUHAN (PERIODE TERTENTU)
-- ============================================================================
-- Tujuan: Melihat total debit dan kredit untuk periode tertentu
-- Hasil: Jika selisih > 0, berarti debit lebih besar dari kredit

SELECT 
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih,
    COUNT(*) as jumlah_jurnal
FROM jurnal_umum
WHERE user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND tanggal BETWEEN '2026-05-01' AND '2026-05-31';  -- GANTI DENGAN PERIODE YANG INGIN DICEK

-- ============================================================================
-- QUERY 4: CEK JURNAL DUPLIKASI
-- ============================================================================
-- Tujuan: Menemukan jurnal yang sama (tanggal, akun, debit, kredit)
-- Hasil: Jika ada, berarti ada jurnal yang duplikasi

SELECT 
    tanggal,
    coa_id,
    debit,
    kredit,
    keterangan,
    COUNT(*) as jumlah_duplikasi,
    GROUP_CONCAT(id) as jurnal_ids
FROM jurnal_umum
WHERE user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND tanggal BETWEEN '2026-05-01' AND '2026-05-31'  -- GANTI DENGAN PERIODE YANG INGIN DICEK
GROUP BY tanggal, coa_id, debit, kredit, keterangan
HAVING COUNT(*) > 1
ORDER BY COUNT(*) DESC;

-- ============================================================================
-- QUERY 5: CEK TOTAL DEBIT/KREDIT PER TIPE REFERENSI
-- ============================================================================
-- Tujuan: Melihat tipe referensi mana yang tidak seimbang
-- Hasil: Jika ada selisih, berarti ada bug di proses tersebut

SELECT 
    tipe_referensi,
    COUNT(*) as jumlah_jurnal,
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum
WHERE user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND tanggal BETWEEN '2026-05-01' AND '2026-05-31'  -- GANTI DENGAN PERIODE YANG INGIN DICEK
GROUP BY tipe_referensi
ORDER BY ABS(SUM(debit) - SUM(kredit)) DESC;

-- ============================================================================
-- QUERY 6: CEK TOTAL DEBIT/KREDIT PER AKUN (TOP 20 DENGAN SELISIH TERBESAR)
-- ============================================================================
-- Tujuan: Melihat akun mana yang memiliki selisih terbesar
-- Hasil: Fokus pada akun dengan selisih terbesar

SELECT 
    c.kode_akun,
    c.nama_akun,
    c.saldo_normal,
    SUM(ju.debit) as total_debit,
    SUM(ju.kredit) as total_kredit,
    SUM(ju.debit) - SUM(ju.kredit) as selisih,
    COUNT(*) as jumlah_jurnal
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND ju.tanggal BETWEEN '2026-05-01' AND '2026-05-31'  -- GANTI DENGAN PERIODE YANG INGIN DICEK
GROUP BY c.id, c.kode_akun, c.nama_akun, c.saldo_normal
ORDER BY ABS(SUM(ju.debit) - SUM(ju.kredit)) DESC
LIMIT 20;

-- ============================================================================
-- QUERY 7: LIHAT DETAIL JURNAL UNTUK TIPE REFERENSI TERTENTU
-- ============================================================================
-- Tujuan: Melihat detail jurnal untuk tipe referensi yang tidak seimbang
-- Contoh: Lihat detail jurnal penjualan

SELECT 
    ju.id,
    ju.tanggal,
    c.kode_akun,
    c.nama_akun,
    ju.debit,
    ju.kredit,
    ju.keterangan,
    ju.tipe_referensi,
    ju.referensi
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND ju.tipe_referensi = 'penjualan'  -- GANTI DENGAN TIPE REFERENSI YANG INGIN DICEK
  AND ju.tanggal BETWEEN '2026-05-01' AND '2026-05-31'  -- GANTI DENGAN PERIODE YANG INGIN DICEK
ORDER BY ju.tanggal, ju.referensi, ju.id;

-- ============================================================================
-- QUERY 8: LIHAT DETAIL JURNAL UNTUK REFERENSI TERTENTU
-- ============================================================================
-- Tujuan: Melihat detail jurnal untuk referensi tertentu (misal penjualan #1)
-- Contoh: Lihat detail jurnal penjualan #1

SELECT 
    ju.id,
    ju.tanggal,
    c.kode_akun,
    c.nama_akun,
    ju.debit,
    ju.kredit,
    ju.keterangan,
    ju.tipe_referensi,
    ju.referensi
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND ju.tipe_referensi = 'penjualan'  -- GANTI DENGAN TIPE REFERENSI
  AND ju.referensi = '1'  -- GANTI DENGAN NOMOR REFERENSI
ORDER BY ju.id;

-- ============================================================================
-- QUERY 9: LIHAT DETAIL JURNAL UNTUK AKUN TERTENTU
-- ============================================================================
-- Tujuan: Melihat detail jurnal untuk akun tertentu
-- Contoh: Lihat detail jurnal akun 1101 (Kas)

SELECT 
    ju.id,
    ju.tanggal,
    c.kode_akun,
    c.nama_akun,
    ju.debit,
    ju.kredit,
    ju.keterangan,
    ju.tipe_referensi,
    ju.referensi
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND c.kode_akun = '1101'  -- GANTI DENGAN KODE AKUN YANG INGIN DICEK
  AND ju.tanggal BETWEEN '2026-05-01' AND '2026-05-31'  -- GANTI DENGAN PERIODE YANG INGIN DICEK
ORDER BY ju.tanggal, ju.id;

-- ============================================================================
-- QUERY 10: HITUNG SALDO AKHIR AKUN TERTENTU (UNTUK VERIFIKASI)
-- ============================================================================
-- Tujuan: Menghitung saldo akhir akun tertentu untuk verifikasi
-- Contoh: Hitung saldo akhir akun 1101 (Kas)

SELECT 
    c.kode_akun,
    c.nama_akun,
    c.saldo_awal,
    SUM(ju.debit) as total_debit_periode,
    SUM(ju.kredit) as total_kredit_periode,
    c.saldo_awal + SUM(ju.debit) - SUM(ju.kredit) as saldo_akhir
FROM coas c
LEFT JOIN jurnal_umum ju ON c.id = ju.coa_id 
    AND ju.user_id = c.user_id
    AND ju.tanggal BETWEEN '2026-05-01' AND '2026-05-31'  -- GANTI DENGAN PERIODE YANG INGIN DICEK
WHERE c.user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND c.kode_akun = '1101'  -- GANTI DENGAN KODE AKUN YANG INGIN DICEK
GROUP BY c.id, c.kode_akun, c.nama_akun, c.saldo_awal;

-- ============================================================================
-- QUERY 11: PERBAIKAN - DELETE JURNAL DUPLIKASI
-- ============================================================================
-- HATI-HATI! Query ini akan menghapus data
-- Tujuan: Menghapus jurnal duplikasi
-- Contoh: Hapus jurnal duplikasi dengan ID 123

-- Lihat dulu sebelum delete:
SELECT * FROM jurnal_umum WHERE id = 123;

-- Jika yakin, jalankan delete:
-- DELETE FROM jurnal_umum WHERE id = 123;

-- ============================================================================
-- QUERY 12: PERBAIKAN - UPDATE NILAI JURNAL YANG SALAH
-- ============================================================================
-- HATI-HATI! Query ini akan mengubah data
-- Tujuan: Memperbaiki nilai debit/kredit yang salah
-- Contoh: Ubah kredit jurnal ID 123 dari 14.500.000 menjadi 15.000.000

-- Lihat dulu sebelum update:
SELECT * FROM jurnal_umum WHERE id = 123;

-- Jika yakin, jalankan update:
-- UPDATE jurnal_umum SET kredit = 15000000 WHERE id = 123;

-- ============================================================================
-- QUERY 13: PERBAIKAN - MERGE DUPLIKASI COA
-- ============================================================================
-- HATI-HATI! Query ini akan mengubah data
-- Tujuan: Mengubah semua jurnal yang menggunakan COA lama ke COA baru
-- Contoh: Ubah semua jurnal yang menggunakan COA ID 10 ke COA ID 5

-- Lihat dulu sebelum update:
SELECT COUNT(*) FROM jurnal_umum WHERE coa_id = 10 AND user_id = 5;

-- Jika yakin, jalankan update:
-- UPDATE jurnal_umum SET coa_id = 5 WHERE coa_id = 10 AND user_id = 5;

-- Setelah itu, delete COA yang lama:
-- DELETE FROM coas WHERE id = 10;

-- ============================================================================
-- QUERY 14: VERIFIKASI SETELAH PERBAIKAN
-- ============================================================================
-- Tujuan: Memverifikasi bahwa neraca saldo sudah seimbang setelah perbaikan

-- Cek total debit vs kredit
SELECT 
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum
WHERE user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
  AND tanggal BETWEEN '2026-05-01' AND '2026-05-31';  -- GANTI DENGAN PERIODE YANG INGIN DICEK

-- Cek apakah ada jurnal yang tidak seimbang
SELECT COUNT(*) as jumlah_jurnal_tidak_seimbang
FROM (
    SELECT 
        tipe_referensi,
        referensi,
        SUM(debit) as total_debit,
        SUM(kredit) as total_kredit
    FROM jurnal_umum
    WHERE user_id = 5  -- GANTI DENGAN USER_ID YANG SEDANG LOGIN
    GROUP BY tipe_referensi, referensi
    HAVING ABS(SUM(debit) - SUM(kredit)) > 0.01
) as subquery;

-- ============================================================================
-- CATATAN PENTING
-- ============================================================================
-- 1. Selalu backup database sebelum menjalankan query UPDATE atau DELETE
-- 2. Jalankan query SELECT dulu untuk verifikasi sebelum UPDATE/DELETE
-- 3. Ganti {user_id}, {bulan}, {tahun} dengan nilai yang sesuai
-- 4. Jika ada pertanyaan, hubungi developer
-- ============================================================================
