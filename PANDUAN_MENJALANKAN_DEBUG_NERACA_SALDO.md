# PANDUAN MENJALANKAN DEBUG NERACA SALDO

## 📌 OVERVIEW

Anda telah menerima:
1. **Analisis Lengkap** (`ANALISIS_NERACA_SALDO_TIDAK_SEIMBANG.md`) - Penjelasan penyebab dan solusi
2. **Debug Command** (`app/Console/Commands/DebugNeracaSaldo.php`) - Tool untuk menemukan masalah
3. **Perbaikan Kode** - Update di `JournalService.php` dan `TrialBalanceService.php`

---

## 🚀 LANGKAH 1: JALANKAN DEBUG COMMAND

### Syntax:
```bash
php artisan debug:neraca-saldo {user_id} --bulan={bulan} --tahun={tahun}
```

### Contoh untuk User ID 5, Mei 2026:
```bash
php artisan debug:neraca-saldo 5 --bulan=5 --tahun=2026
```

### Output yang Akan Ditampilkan:

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

📋 DEBUG 6: Top 20 Akun dengan Selisih Terbesar
─────────────────────────────────────────
1101 - Kas
  Normal: debit
  Debit: Rp 5.000.000,00
  Kredit: Rp 3.000.000,00
  ⚠️  Selisih: Rp 2.000.000,00

2101 - Utang Usaha
  Normal: kredit
  Debit: Rp 1.000.000,00
  Kredit: Rp 2.000.000,00
  ✅ Selisih: Rp 0,00
```

---

## 🔍 LANGKAH 2: ANALISIS OUTPUT DEBUG

### Interpretasi Setiap Debug:

#### **DEBUG 1: Jurnal yang Tidak Seimbang**
- **Jika ada hasil:** Ada jurnal yang total debit ≠ total kredit
- **Aksi:** Lihat tipe referensi dan nomor referensi mana yang bermasalah
- **Contoh:** Penjualan #1 memiliki selisih Rp 500.000

#### **DEBUG 2: Duplikasi COA**
- **Jika ada hasil:** Ada COA dengan kode_akun yang sama untuk user yang sama
- **Aksi:** Merge atau delete duplikasi
- **Contoh:** Kode 1101 ada 2 record dengan ID 5 dan 10

#### **DEBUG 3: Total Debit vs Kredit**
- **Jika selisih > 0:** Debit lebih besar (seperti kasus Anda)
- **Jika selisih < 0:** Kredit lebih besar
- **Aksi:** Cari akun/jurnal mana yang menyebabkan selisih ini

#### **DEBUG 4: Jurnal Duplikasi**
- **Jika ada hasil:** Ada jurnal yang sama tanggal, akun, debit, kredit
- **Aksi:** Delete salah satu duplikasi
- **Contoh:** Jurnal tanggal 2026-05-05, COA 1101, Debit 1.000.000 ada 2x

#### **DEBUG 5: Per Tipe Referensi**
- **Lihat tipe referensi mana yang tidak seimbang**
- **Aksi:** Fokus pada tipe referensi yang bermasalah
- **Contoh:** Penjualan tidak seimbang, pembelian seimbang

#### **DEBUG 6: Per Akun**
- **Lihat akun mana yang memiliki selisih terbesar**
- **Aksi:** Cek jurnal di akun tersebut
- **Contoh:** Akun 1101 (Kas) memiliki selisih Rp 2.000.000

---

## 🛠️ LANGKAH 3: PERBAIKI DATA BERDASARKAN DEBUG

### Skenario 1: Ada Jurnal yang Tidak Seimbang

**Contoh:** Penjualan #1 memiliki selisih Rp 500.000

**Aksi:**
1. Cari jurnal penjualan #1 di database:
   ```sql
   SELECT * FROM jurnal_umum 
   WHERE tipe_referensi = 'penjualan' 
   AND referensi = '1'
   AND user_id = 5;
   ```

2. Lihat detail jurnal dan perbaiki:
   - Jika ada baris yang salah → Update nilai debit/kredit
   - Jika ada baris duplikasi → Delete baris tersebut
   - Jika ada baris yang tidak seharusnya ada → Delete baris tersebut

3. Verifikasi total debit = total kredit setelah perbaikan

---

### Skenario 2: Ada Duplikasi COA

**Contoh:** Kode 1101 ada 2 record dengan ID 5 dan 10

**Aksi:**
1. Lihat kedua COA:
   ```sql
   SELECT * FROM coas WHERE kode_akun = '1101' AND user_id = 5;
   ```

2. Tentukan COA mana yang benar (biasanya yang lebih baru atau memiliki saldo_awal)

3. Update jurnal yang menggunakan COA yang salah:
   ```sql
   UPDATE jurnal_umum 
   SET coa_id = 5 
   WHERE coa_id = 10 AND user_id = 5;
   ```

4. Delete COA yang salah:
   ```sql
   DELETE FROM coas WHERE id = 10;
   ```

---

### Skenario 3: Ada Jurnal Duplikasi

**Contoh:** Jurnal tanggal 2026-05-05, COA 1101, Debit 1.000.000 ada 2x

**Aksi:**
1. Lihat jurnal duplikasi:
   ```sql
   SELECT * FROM jurnal_umum 
   WHERE tanggal = '2026-05-05' 
   AND coa_id = 1 
   AND debit = 1000000
   AND user_id = 5;
   ```

2. Delete salah satu duplikasi (biasanya yang lebih baru):
   ```sql
   DELETE FROM jurnal_umum WHERE id = 123; -- Ganti 123 dengan ID yang akan dihapus
   ```

---

## ✅ LANGKAH 4: VERIFIKASI PERBAIKAN

### Jalankan Debug Command Lagi:
```bash
php artisan debug:neraca-saldo 5 --bulan=5 --tahun=2026
```

### Cek Hasil:
- **DEBUG 1:** Harus menampilkan "✅ Semua jurnal seimbang"
- **DEBUG 2:** Harus menampilkan "✅ Tidak ada duplikasi COA"
- **DEBUG 3:** Harus menampilkan "✅ Selisih: Rp 0,00"
- **DEBUG 4:** Harus menampilkan "✅ Tidak ada jurnal duplikasi"
- **DEBUG 5:** Semua tipe referensi harus seimbang
- **DEBUG 6:** Semua akun harus seimbang

---

## 📊 LANGKAH 5: BUKA HALAMAN NERACA SALDO

### URL:
```
http://localhost:8000/akuntansi/neraca-saldo?bulan=5&tahun=2026
```

### Cek:
- **Status:** Harus menampilkan "✅ Neraca Saldo Seimbang"
- **Total Debit:** Harus sama dengan Total Kredit
- **Selisih:** Harus Rp 0,00

---

## 🎯 RINGKASAN PROSES

```
1. Jalankan Debug Command
   ↓
2. Analisis Output Debug
   ↓
3. Identifikasi Masalah (Jurnal tidak seimbang / Duplikasi / dll)
   ↓
4. Perbaiki Data di Database
   ↓
5. Jalankan Debug Command Lagi
   ↓
6. Verifikasi Neraca Saldo Seimbang
   ↓
7. Selesai ✅
```

---

## 📝 CONTOH LENGKAP

### Kasus: Neraca Saldo Tidak Seimbang Rp 11.012.400

**Step 1: Jalankan Debug**
```bash
php artisan debug:neraca-saldo 5 --bulan=5 --tahun=2026
```

**Step 2: Analisis Output**
- DEBUG 1 menunjukkan: Penjualan #1 tidak seimbang (Rp 500.000)
- DEBUG 1 menunjukkan: Pembelian #5 tidak seimbang (Rp 1.000.000)
- DEBUG 5 menunjukkan: Penjualan total selisih Rp 500.000, Pembelian total selisih Rp 1.000.000
- Sisanya: Rp 11.012.400 - Rp 500.000 - Rp 1.000.000 = Rp 9.512.400 (dari akun lain)

**Step 3: Perbaiki Penjualan #1**
```sql
-- Lihat jurnal penjualan #1
SELECT * FROM jurnal_umum 
WHERE tipe_referensi = 'penjualan' AND referensi = '1' AND user_id = 5;

-- Hasil: Ada 3 baris
-- Baris 1: Kas (1101) Debit 10.000.000
-- Baris 2: Piutang (1201) Debit 5.000.000
-- Baris 3: Pendapatan (4101) Kredit 14.500.000 (SALAH! Seharusnya 15.000.000)

-- Perbaiki baris 3
UPDATE jurnal_umum SET kredit = 15000000 WHERE id = 123;
```

**Step 4: Perbaiki Pembelian #5**
```sql
-- Lihat jurnal pembelian #5
SELECT * FROM jurnal_umum 
WHERE tipe_referensi = 'pembelian' AND referensi = '5' AND user_id = 5;

-- Hasil: Ada 2 baris
-- Baris 1: Persediaan (1141) Debit 8.000.000
-- Baris 2: Utang (2101) Kredit 7.000.000 (SALAH! Seharusnya 8.000.000)

-- Perbaiki baris 2
UPDATE jurnal_umum SET kredit = 8000000 WHERE id = 124;
```

**Step 5: Cari Akun Lain dengan Selisih Rp 9.512.400**
```bash
php artisan debug:neraca-saldo 5 --bulan=5 --tahun=2026
```

Output DEBUG 6 akan menunjukkan akun mana yang memiliki selisih Rp 9.512.400. Perbaiki akun tersebut dengan cara yang sama.

**Step 6: Verifikasi**
```bash
php artisan debug:neraca-saldo 5 --bulan=5 --tahun=2026
```

Semua debug harus menampilkan ✅ (seimbang).

**Step 7: Buka Halaman Neraca Saldo**
```
http://localhost:8000/akuntansi/neraca-saldo?bulan=5&tahun=2026
```

Status harus menampilkan "✅ Neraca Saldo Seimbang".

---

## 🆘 TROUBLESHOOTING

### Masalah: Debug Command Tidak Ditemukan
**Solusi:**
```bash
php artisan list
```
Cek apakah `debug:neraca-saldo` ada di list. Jika tidak, jalankan:
```bash
php artisan cache:clear
php artisan config:clear
```

### Masalah: Debug Menunjukkan Banyak Jurnal Tidak Seimbang
**Solusi:**
1. Cek apakah ada bug di proses pembuatan jurnal (pembelian, penjualan, dll)
2. Perbaiki bug di controller/service yang membuat jurnal
3. Perbaiki data jurnal yang sudah ada

### Masalah: Setelah Perbaikan, Masih Ada Selisih
**Solusi:**
1. Jalankan debug command lagi untuk menemukan masalah yang tersisa
2. Ulangi proses perbaikan
3. Jika masih tidak ketemu, hubungi developer

---

## 📞 NEXT STEPS

1. ✅ Jalankan debug command
2. ✅ Analisis output
3. ✅ Perbaiki data
4. ✅ Verifikasi
5. ✅ Buka halaman neraca saldo
6. ✅ Pastikan seimbang

Jika masih ada pertanyaan, silakan hubungi developer dengan output debug command.

