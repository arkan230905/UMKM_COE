# Instruksi Memperbaiki Jurnal Pembelian dan Penjualan

## Masalah
1. Jurnal pembelian dan penjualan tidak masuk ke database `jurnal_umum`
2. Halaman `/akuntansi/jurnal-umum` hanya menampilkan jurnal produksi
3. Jumlah pegawai di halaman `/master-data/btkl` menampilkan 0

## Solusi yang Sudah Diterapkan

### 1. Fix Jumlah Pegawai BTKL (Commit: 63caf8e)
**File:** `app/Models/Jabatan.php`

**Perubahan:** Menambahkan filter `user_id` pada relasi `pegawais()` untuk memastikan multi-tenant isolation.

**Status:** ✅ Sudah di-push dan akan auto-deploy via Jenkins

---

### 2. Fix Foreign Key Constraint COA Persediaan (Commit: 1c6b60b)
**File:** `app/Services/JournalService.php`

**Perubahan:** Mengubah update `coa_persediaan_id` dari menggunakan `id` menjadi `kode_akun` untuk menghindari foreign key constraint error.

**Status:** ✅ Sudah di-push dan akan auto-deploy via Jenkins

---

### 3. Support Indonesian COA Terms (Commit: a180e1c)
**File:** `app/Services/JournalValidationService.php`

**Perubahan:** 
- Menambahkan method `getTipeAkunVariants()` untuk mapping English/Indonesian terms
- Mendukung pencarian COA dengan tipe_akun: Pendapatan, Kewajiban, Aset, Biaya/Beban
- Pencarian nama COA menggunakan partial match (contoh: "Penjualan" akan match "Penjualan - Jasuke")

**Status:** ✅ Sudah di-push dan akan auto-deploy via Jenkins

---

### 4. Improved Error Handling & Logging (Commit: 53db023)
**Files:** 
- `app/Http/Controllers/PembelianController.php`
- `app/Services/PembelianJournalService.php`
- `app/Services/JournalService.php`

**Perubahan:**
- Menambahkan warning message yang visible untuk user jika jurnal gagal dibuat
- Menambahkan detailed logging untuk debugging (user_id, total, payment_method, dll)
- Menambahkan success logging dengan total debit/credit untuk verifikasi

**Status:** ✅ Sudah di-push dan akan auto-deploy via Jenkins

---

### 5. Debug Route untuk Membuat Jurnal Retroaktif (Commit: 277a256)
**File:** `routes/web.php`

**Route:** `/debug/create-missing-journals`

**Fungsi:** Membuat jurnal untuk semua transaksi pembelian dan penjualan yang belum memiliki jurnal

**Status:** ✅ Sudah di-push dan akan auto-deploy via Jenkins

---

## Langkah-Langkah Setelah Deployment

### Langkah 1: Tunggu Jenkins Auto-Deploy Selesai
Jenkins akan otomatis deploy setelah push ke branch `main`. Biasanya memakan waktu 2-5 menit.

### Langkah 2: Login ke Website
Login dengan akun Anda:
- Email: arkanaja@gmail.com
- URL: http://jobcost.eadtmanufaktur.com/

### Langkah 3: Jalankan Script Pembuatan Jurnal Retroaktif
Buka URL berikut di browser:
```
http://jobcost.eadtmanufaktur.com/debug/create-missing-journals
```

Script ini akan:
1. Mencari semua transaksi pembelian yang belum memiliki jurnal
2. Mencari semua transaksi penjualan yang belum memiliki jurnal
3. Membuat jurnal untuk setiap transaksi
4. Menampilkan summary: berapa yang berhasil, gagal, dan di-skip
5. Menampilkan jumlah total jurnal per tipe

### Langkah 4: Cek Hasil di Jurnal Umum
Setelah script selesai, buka halaman Jurnal Umum:
```
http://jobcost.eadtmanufaktur.com/akuntansi/jurnal-umum
```

Anda seharusnya melihat:
- Jurnal Pembelian (tipe_referensi: pembelian)
- Jurnal Penjualan (tipe_referensi: sale)
- Jurnal Produksi (tipe_referensi: produksi_*)

### Langkah 5: Cek Jumlah Pegawai BTKL
Buka halaman BTKL:
```
http://jobcost.eadtmanufaktur.com/master-data/btkl
```

Kolom "Jumlah Pegawai" seharusnya sudah menampilkan jumlah yang benar, bukan 0.

---

## Troubleshooting

### Jika Jurnal Masih Tidak Muncul

1. **Cek apakah ada error di script:**
   - Script `/debug/create-missing-journals` akan menampilkan error message jika ada
   - Catat error message dan kirimkan ke developer

2. **Kemungkinan penyebab:**
   - COA yang dibutuhkan belum dibuat
   - Bahan baku/pendukung belum memiliki `coa_persediaan_id`
   - Produk belum memiliki `coa_persediaan_id`

3. **Solusi:**
   - Pastikan semua COA sudah dibuat di Master Data > COA
   - Pastikan setiap bahan baku memiliki COA Persediaan
   - Pastikan setiap produk memiliki COA Persediaan

### Jika Jumlah Pegawai Masih 0

1. **Cek apakah pegawai sudah di-assign ke jabatan BTKL:**
   - Buka Master Data > Pegawai
   - Pastikan setiap pegawai memiliki jabatan yang kategorinya "BTKL"

2. **Cek apakah jabatan sudah memiliki kategori BTKL:**
   - Buka Master Data > Jabatan
   - Pastikan jabatan yang digunakan untuk BTKL memiliki kategori "btkl"

---

## Untuk Transaksi Baru

Setelah fix ini di-deploy, semua transaksi pembelian dan penjualan yang baru akan otomatis membuat jurnal.

**Jika jurnal tidak dibuat:**
- User akan melihat warning message yang jelas
- Error akan tercatat di log dengan detail lengkap
- Transaksi tetap tersimpan, hanya jurnalnya yang tidak dibuat

**Cara mengecek log:**
- SSH ke server
- Buka file: `storage/logs/laravel.log`
- Cari keyword: "Failed to create journal" atau "Journal validation failed"

---

## Summary Commits

| Commit | Deskripsi | Status |
|--------|-----------|--------|
| 63caf8e | Fix jumlah pegawai BTKL | ✅ Pushed |
| 1c6b60b | Fix foreign key constraint COA | ✅ Pushed |
| a180e1c | Support Indonesian COA terms | ✅ Pushed |
| 53db023 | Improved error handling & logging | ✅ Pushed |
| 277a256 | Debug route untuk jurnal retroaktif | ✅ Pushed |

Semua perubahan sudah di-push ke branch `main` dan akan otomatis di-deploy oleh Jenkins.

---

## Kontak Developer

Jika masih ada masalah setelah mengikuti langkah-langkah di atas, silakan hubungi developer dengan informasi:
1. Screenshot error message (jika ada)
2. URL halaman yang bermasalah
3. Langkah-langkah yang sudah dilakukan
