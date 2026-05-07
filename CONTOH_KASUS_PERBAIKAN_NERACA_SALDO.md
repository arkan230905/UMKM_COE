# CONTOH KASUS PERBAIKAN NERACA SALDO

## 📌 KASUS: Neraca Saldo Mei 2026 Tidak Seimbang Rp 11.012.400

---

## 🔍 STEP 1: JALANKAN DEBUG COMMAND

```bash
php artisan debug:neraca-saldo 5 --bulan=5 --tahun=2026
```

### Output Debug:

```
=== DEBUG NERACA SALDO ===
User ID: 5
Periode: 2026-05-01 s/d 2026-05-31

📋 DEBUG 1: Jurnal yang Tidak Seimbang
─────────────────────────────────────────
❌ Ditemukan 2 jurnal yang tidak seimbang:

  Tipe: penjualan | Ref: 1
  Debit: Rp 15.000.000,00
  Kredit: Rp 14.500.000,00
  ⚠️  Selisih: Rp 500.000,00
  Jumlah Baris: 3

  Tipe: pembelian | Ref: 5
  Debit: Rp 8.000.000,00
  Kredit: Rp 7.000.000,00
  ⚠️  Selisih: Rp 1.000.000,00
  Jumlah Baris: 2

📋 DEBUG 2: Duplikasi COA
─────────────────────────────────────────
✅ Tidak ada duplikasi COA

📋 DEBUG 3: Total Debit vs Kredit (Periode)
─────────────────────────────────────────
Total Debit: Rp 21.165.273,00
Total Kredit: Rp 10.152.873,00
❌ Selisih: Rp 11.012.400,00

📋 DEBUG 4: Jurnal Duplikasi
─────────────────────────────────────────
✅ Tidak ada jurnal duplikasi

📋 DEBUG 5: Total Debit/Kredit Per Tipe Referensi
─────────────────────────────────────────
Tipe: penjualan
  Jumlah Jurnal: 5
  Debit: Rp 15.000.000,00
  Kredit: Rp 14.500.000,00
  ⚠️  Selisih: Rp 500.000,00

Tipe: pembelian
  Jumlah Jurnal: 3
  Debit: Rp 8.000.000,00
  Kredit: Rp 7.000.000,00
  ⚠️  Selisih: Rp 1.000.000,00

Tipe: penyusutan
  Jumlah Jurnal: 2
  Debit: Rp 2.000.000,00
  Kredit: Rp 2.000.000,00
  ✅ Selisih: Rp 0,00

Tipe: manual
  Jumlah Jurnal: 1
  Debit: Rp 9.512.400,00
  Kredit: Rp 0,00
  ⚠️  Selisih: Rp 9.512.400,00

📋 DEBUG 6: Top 20 Akun dengan Selisih Terbesar
─────────────────────────────────────────
1101 - Kas
  Normal: debit
  Debit: Rp 5.000.000,00
  Kredit: Rp 3.000.000,00
  ⚠️  Selisih: Rp 2.000.000,00

1201 - Piutang Usaha
  Normal: debit
  Debit: Rp 10.000.000,00
  Kredit: Rp 5.000.000,00
  ⚠️  Selisih: Rp 5.000.000,00

2101 - Utang Usaha
  Normal: kredit
  Debit: Rp 1.000.000,00
  Kredit: Rp 2.000.000,00
  ✅ Selisih: Rp 0,00

4101 - Pendapatan Penjualan
  Normal: kredit
  Debit: Rp 0,00
  Kredit: Rp 14.500.000,00
  ⚠️  Selisih: Rp -14.500.000,00

5101 - Beban Pembelian
  Normal: debit
  Debit: Rp 8.000.000,00
  Kredit: Rp 7.000.000,00
  ⚠️  Selisih: Rp 1.000.000,00
```

---

## 📊 ANALISIS OUTPUT DEBUG

### Temuan:
1. **Penjualan #1:** Tidak seimbang Rp 500.000 (Debit > Kredit)
2. **Pembelian #5:** Tidak seimbang Rp 1.000.000 (Debit > Kredit)
3. **Jurnal Manual:** Tidak seimbang Rp 9.512.400 (Hanya Debit, tidak ada Kredit)
4. **Total Selisih:** Rp 500.000 + Rp 1.000.000 + Rp 9.512.400 = Rp 11.012.400 ✓

---

## 🛠️ STEP 2: PERBAIKI PENJUALAN #1

### Masalah:
- Debit: Rp 15.000.000
- Kredit: Rp 14.500.000
- Selisih: Rp 500.000

### Lihat Detail Jurnal:

```sql
SELECT 
    ju.id,
    ju.tanggal,
    c.kode_akun,
    c.nama_akun,
    ju.debit,
    ju.kredit,
    ju.keterangan
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = 5
  AND ju.tipe_referensi = 'penjualan'
  AND ju.referensi = '1'
ORDER BY ju.id;
```

### Hasil:
```
ID | Tanggal    | Kode | Nama Akun           | Debit      | Kredit     | Keterangan
---|------------|------|---------------------|------------|------------|------------------
1  | 2026-05-05 | 1101 | Kas                 | 15.000.000 | 0          | Penjualan #1
2  | 2026-05-05 | 4101 | Pendapatan Penjualan| 0          | 14.500.000 | Penjualan #1
```

### Masalah Ditemukan:
- Baris 2: Kredit seharusnya Rp 15.000.000, tapi hanya Rp 14.500.000
- Penyebab: Input manual salah atau ada bug di proses penjualan

### Perbaikan:

```sql
-- Lihat dulu
SELECT * FROM jurnal_umum WHERE id = 2;

-- Perbaiki
UPDATE jurnal_umum 
SET kredit = 15000000 
WHERE id = 2;

-- Verifikasi
SELECT 
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum
WHERE tipe_referensi = 'penjualan' AND referensi = '1' AND user_id = 5;
```

### Hasil Setelah Perbaikan:
```
Total Debit: Rp 15.000.000
Total Kredit: Rp 15.000.000
Selisih: Rp 0 ✅
```

---

## 🛠️ STEP 3: PERBAIKI PEMBELIAN #5

### Masalah:
- Debit: Rp 8.000.000
- Kredit: Rp 7.000.000
- Selisih: Rp 1.000.000

### Lihat Detail Jurnal:

```sql
SELECT 
    ju.id,
    ju.tanggal,
    c.kode_akun,
    c.nama_akun,
    ju.debit,
    ju.kredit,
    ju.keterangan
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = 5
  AND ju.tipe_referensi = 'pembelian'
  AND ju.referensi = '5'
ORDER BY ju.id;
```

### Hasil:
```
ID | Tanggal    | Kode | Nama Akun           | Debit      | Kredit     | Keterangan
---|------------|------|---------------------|------------|------------|------------------
3  | 2026-05-10 | 1141 | Pers. Bahan Baku    | 8.000.000  | 0          | Pembelian #5
4  | 2026-05-10 | 2101 | Utang Usaha         | 0          | 7.000.000  | Pembelian #5
```

### Masalah Ditemukan:
- Baris 4: Kredit seharusnya Rp 8.000.000, tapi hanya Rp 7.000.000
- Penyebab: Input manual salah atau ada bug di proses pembelian

### Perbaikan:

```sql
-- Lihat dulu
SELECT * FROM jurnal_umum WHERE id = 4;

-- Perbaiki
UPDATE jurnal_umum 
SET kredit = 8000000 
WHERE id = 4;

-- Verifikasi
SELECT 
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum
WHERE tipe_referensi = 'pembelian' AND referensi = '5' AND user_id = 5;
```

### Hasil Setelah Perbaikan:
```
Total Debit: Rp 8.000.000
Total Kredit: Rp 8.000.000
Selisih: Rp 0 ✅
```

---

## 🛠️ STEP 4: PERBAIKI JURNAL MANUAL

### Masalah:
- Debit: Rp 9.512.400
- Kredit: Rp 0
- Selisih: Rp 9.512.400 (Hanya ada debit, tidak ada kredit!)

### Lihat Detail Jurnal:

```sql
SELECT 
    ju.id,
    ju.tanggal,
    c.kode_akun,
    c.nama_akun,
    ju.debit,
    ju.kredit,
    ju.keterangan
FROM jurnal_umum ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.user_id = 5
  AND ju.tipe_referensi = 'manual'
ORDER BY ju.id;
```

### Hasil:
```
ID | Tanggal    | Kode | Nama Akun           | Debit      | Kredit     | Keterangan
---|------------|------|---------------------|------------|------------|------------------
5  | 2026-05-15 | 1201 | Piutang Usaha       | 9.512.400  | 0          | Jurnal Manual
```

### Masalah Ditemukan:
- Hanya ada 1 baris dengan debit, tidak ada baris kredit
- Jurnal tidak seimbang karena tidak lengkap

### Opsi Perbaikan:

**Opsi 1: Tambah baris kredit yang hilang**
```sql
-- Jika tahu akun kredit mana yang seharusnya
INSERT INTO jurnal_umum (
    user_id, coa_id, tanggal, keterangan, debit, kredit, 
    referensi, tipe_referensi, created_by, created_at, updated_at
) VALUES (
    5, 2, '2026-05-15', 'Jurnal Manual', 0, 9512400, 
    'manual', 'manual', 5, NOW(), NOW()
);
-- Ganti coa_id = 2 dengan ID akun kredit yang sesuai
```

**Opsi 2: Delete jurnal yang tidak lengkap**
```sql
-- Jika jurnal ini salah atau tidak perlu
DELETE FROM jurnal_umum WHERE id = 5;
```

### Asumsi: Jurnal ini seharusnya adalah penyesuaian piutang
```sql
-- Tambah baris kredit untuk akun penyesuaian
INSERT INTO jurnal_umum (
    user_id, coa_id, tanggal, keterangan, debit, kredit, 
    referensi, tipe_referensi, created_by, created_at, updated_at
) VALUES (
    5, 6, '2026-05-15', 'Penyesuaian Piutang', 0, 9512400, 
    'manual', 'manual', 5, NOW(), NOW()
);
-- Asumsi: coa_id = 6 adalah akun penyesuaian
```

### Verifikasi:
```sql
SELECT 
    SUM(debit) as total_debit,
    SUM(kredit) as total_kredit,
    SUM(debit) - SUM(kredit) as selisih
FROM jurnal_umum
WHERE tipe_referensi = 'manual' AND user_id = 5;
```

### Hasil Setelah Perbaikan:
```
Total Debit: Rp 9.512.400
Total Kredit: Rp 9.512.400
Selisih: Rp 0 ✅
```

---

## ✅ STEP 5: VERIFIKASI PERBAIKAN

### Jalankan Debug Command Lagi:

```bash
php artisan debug:neraca-saldo 5 --bulan=5 --tahun=2026
```

### Output Setelah Perbaikan:

```
📋 DEBUG 1: Jurnal yang Tidak Seimbang
─────────────────────────────────────────
✅ Semua jurnal seimbang (tidak ada masalah)

📋 DEBUG 3: Total Debit vs Kredit (Periode)
─────────────────────────────────────────
Total Debit: Rp 20.000.000,00
Total Kredit: Rp 20.000.000,00
✅ Selisih: Rp 0,00

📋 DEBUG 5: Total Debit/Krebit Per Tipe Referensi
─────────────────────────────────────────
Tipe: penjualan
  Jumlah Jurnal: 5
  Debit: Rp 15.000.000,00
  Kredit: Rp 15.000.000,00
  ✅ Selisih: Rp 0,00

Tipe: pembelian
  Jumlah Jurnal: 3
  Debit: Rp 8.000.000,00
  Kredit: Rp 8.000.000,00
  ✅ Selisih: Rp 0,00

Tipe: manual
  Jumlah Jurnal: 2
  Debit: Rp 9.512.400,00
  Kredit: Rp 9.512.400,00
  ✅ Selisih: Rp 0,00
```

---

## 🎉 STEP 6: BUKA HALAMAN NERACA SALDO

### URL:
```
http://localhost:8000/akuntansi/neraca-saldo?bulan=5&tahun=2026
```

### Hasil:
```
✅ NERACA SALDO SEIMBANG

Total Debit: Rp 20.000.000
Total Kredit: Rp 20.000.000
Selisih: Rp 0

Status: Seimbang ✅
```

---

## 📋 RINGKASAN PERBAIKAN

| Masalah | Penyebab | Solusi | Status |
|---------|----------|--------|--------|
| Penjualan #1 tidak seimbang | Kredit salah input | Update kredit dari 14.5M menjadi 15M | ✅ |
| Pembelian #5 tidak seimbang | Kredit salah input | Update kredit dari 7M menjadi 8M | ✅ |
| Jurnal Manual tidak seimbang | Baris kredit hilang | Tambah baris kredit 9.512.400 | ✅ |
| **Total Selisih** | **Rp 11.012.400** | **Semua diperbaiki** | **✅ Rp 0** |

---

## 🎓 PEMBELAJARAN

### Penyebab Ketidakseimbangan:
1. ❌ Input manual salah (penjualan #1, pembelian #5)
2. ❌ Jurnal tidak lengkap (jurnal manual hanya debit)
3. ❌ Tidak ada validasi saat input

### Pencegahan di Masa Depan:
1. ✅ Validasi jurnal saat disimpan (sudah diperbaiki di kode)
2. ✅ Gunakan form yang memaksa debit = kredit
3. ✅ Jangan allow input manual jurnal tanpa validasi
4. ✅ Gunakan debug command secara berkala

---

## 📞 KESIMPULAN

**Waktu Perbaikan:** ~30 menit
**Hasil:** Neraca Saldo Seimbang ✅
**Status:** SELESAI

Neraca saldo sekarang sudah seimbang dan siap untuk laporan keuangan.

