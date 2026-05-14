# 🎯 INSTRUKSI UNTUK OWNER - PERBAIKAN DATABASE SIMACOST

**Tanggal:** 14 Mei 2026  
**Status:** SIAP IMPLEMENTASI  
**Estimasi Waktu:** 15-30 menit

---

## 📌 RINGKASAN MASALAH

Database Anda mengalami inkonsistensi fatal antara nama tabel `coas` dan `accounts`. Ini menyebabkan error "Table not found" dan foreign key constraint failures.

---

## ✅ SOLUSI YANG SUDAH DISIAPKAN

Saya sudah menyiapkan:

1. ✅ **File migrasi baru:** `create_accounts_table.php` (sudah lengkap dengan semua kolom)
2. ✅ **Seeder yang diperbaiki:** `JasukeCoaSeeder.php` (sudah mengarah ke tabel `accounts`)
3. ✅ **Script otomatis:** `fix_coas_to_accounts.php` (untuk mengubah semua referensi)
4. ✅ **Dokumentasi lengkap:** `AUDIT_DATABASE_ACCOUNTS.md`

---

## 🚀 LANGKAH-LANGKAH IMPLEMENTASI

### **LANGKAH 1: BACKUP DATABASE** ⚠️ WAJIB!

```bash
# Via Command Line
php artisan db:backup

# Atau via phpMyAdmin:
# 1. Buka phpMyAdmin
# 2. Pilih database UMKM_COE
# 3. Klik tab "Export"
# 4. Klik "Go" untuk download backup
```

**PENTING:** Jangan lanjut sebelum backup selesai!

---

### **LANGKAH 2: JALANKAN SCRIPT OTOMATIS**

```bash
# Masuk ke folder proyek
cd c:\xampp\htdocs\UMKM_COE

# Jalankan script perbaikan
php fix_coas_to_accounts.php
```

Script ini akan:
- ✅ Mengubah semua referensi `coas` menjadi `accounts`
- ✅ Membuat backup otomatis untuk setiap file yang diubah
- ✅ Menampilkan laporan perubahan

**Output yang diharapkan:**
```
🔧 MEMULAI PROSES STANDARISASI: coas → accounts
============================================================

📁 Scanning: database/migrations
  ✅ create_journal_entries_table.php (2 perubahan)
  ✅ create_journal_lines_table.php (1 perubahan)
  ...

📊 RINGKASAN:
  • Total file di-scan: 250
  • File yang diubah: 45
  • Total perubahan: 120

✅ PROSES SELESAI!
```

---

### **LANGKAH 3: HAPUS FILE MIGRASI LAMA**

```bash
# Hapus file yang menyebabkan konflik
del database\migrations\2025_10_28_161000_create_coas_table.php
```

**Atau manual:**
1. Buka folder `database/migrations`
2. Cari file `2025_10_28_161000_create_coas_table.php`
3. Hapus file tersebut

---

### **LANGKAH 4: RESET DATABASE**

```bash
# HATI-HATI: Ini akan menghapus semua data!
php artisan migrate:fresh
```

**Alternatif (jika ingin lebih aman):**
```bash
# Rollback semua migrasi
php artisan migrate:rollback --step=999

# Jalankan migrasi ulang
php artisan migrate
```

---

### **LANGKAH 5: JALANKAN SEEDER**

```bash
# Isi tabel accounts dengan data COA Jasuke
php artisan db:seed --class=JasukeCoaSeeder
```

**Output yang diharapkan:**
```
✅ JasukeCoaSeeder: Seluruh akun manufaktur berhasil disinkronkan ke tabel 'accounts'.
```

---

### **LANGKAH 6: VERIFIKASI**

```bash
# Masuk ke MySQL
mysql -u root -p

# Pilih database
USE umkm_coe;

# Cek struktur tabel
DESCRIBE accounts;

# Cek data
SELECT kode_akun, nama_akun, tipe_akun, saldo_awal FROM accounts LIMIT 10;

# Cek foreign keys
SHOW CREATE TABLE accounts;
```

**Hasil yang diharapkan:**
- Tabel `accounts` ada dan memiliki 50+ baris data
- Kolom `saldo_awal` ada dengan default 0
- Foreign key `kode_induk` mengarah ke `accounts.kode_akun`

---

## 📋 CHECKLIST VERIFIKASI

Centang setiap langkah setelah selesai:

- [ ] Backup database sudah dibuat
- [ ] Script `fix_coas_to_accounts.php` berhasil dijalankan
- [ ] File `create_coas_table.php` sudah dihapus
- [ ] `php artisan migrate:fresh` berhasil tanpa error
- [ ] `php artisan db:seed --class=JasukeCoaSeeder` berhasil
- [ ] Tabel `accounts` ada dan berisi data
- [ ] Aplikasi bisa diakses tanpa error
- [ ] Bisa membuat transaksi baru (test CRUD)

---

## 🆘 TROUBLESHOOTING

### ❌ Error: "Table 'coas' doesn't exist"

**Penyebab:** Masih ada file yang merujuk ke tabel `coas`

**Solusi:**
```bash
# Cari file yang masih merujuk coas
grep -r "table('coas')" database/
grep -r "on('coas')" database/

# Atau di Windows PowerShell:
Get-ChildItem -Path database -Recurse -Filter *.php | Select-String "coas"
```

Ubah manual semua referensi `coas` menjadi `accounts`.

---

### ❌ Error: "Foreign key constraint fails"

**Penyebab:** Urutan migrasi salah

**Solusi:**
1. Pastikan file `create_accounts_table.php` memiliki timestamp `2025_10_28_160000`
2. File ini harus lebih awal dari file yang mereferensikannya
3. Jalankan ulang: `php artisan migrate:fresh`

---

### ❌ Error: "Duplicate entry for key 'accounts_kode_company_unique'"

**Penyebab:** Ada data duplikat

**Solusi:**
```sql
-- Hapus data duplikat
DELETE t1 FROM accounts t1
INNER JOIN accounts t2 
WHERE t1.id > t2.id 
AND t1.kode_akun = t2.kode_akun 
AND t1.company_id = t2.company_id;
```

---

### ❌ Aplikasi masih error setelah migrasi

**Solusi:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart server
# Jika pakai XAMPP: Stop & Start Apache + MySQL
# Jika pakai artisan serve: Ctrl+C lalu php artisan serve
```

---

## 📊 STRUKTUR TABEL ACCOUNTS (FINAL)

```sql
CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `company_id` bigint unsigned DEFAULT NULL,
  `kode_akun` varchar(20) NOT NULL,
  `nama_akun` varchar(255) NOT NULL,
  `tipe_akun` varchar(255) NOT NULL,
  `kategori_akun` varchar(50) DEFAULT NULL,
  `is_akun_header` tinyint(1) NOT NULL DEFAULT '0',
  `kode_induk` varchar(20) DEFAULT NULL,
  `saldo_normal` enum('debit','kredit') NOT NULL DEFAULT 'debit',
  `saldo_awal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tanggal_saldo_awal` date DEFAULT NULL,
  `posted_saldo_awal` tinyint(1) NOT NULL DEFAULT '0',
  `keterangan` text,
  `nomor_rekening` varchar(255) DEFAULT NULL,
  `atas_nama` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_kode_company_unique` (`kode_akun`,`company_id`),
  KEY `accounts_company_id_index` (`company_id`),
  KEY `accounts_user_id_index` (`user_id`),
  KEY `accounts_kode_akun_index` (`kode_akun`),
  CONSTRAINT `accounts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accounts_kode_induk_foreign` FOREIGN KEY (`kode_induk`) REFERENCES `accounts` (`kode_akun`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 📞 KONTAK & DUKUNGAN

Jika mengalami masalah:

1. **Cek file log:** `storage/logs/laravel.log`
2. **Cek dokumentasi:** `AUDIT_DATABASE_ACCOUNTS.md`
3. **Restore backup:** Jika ada masalah fatal, restore dari backup yang dibuat di Langkah 1

---

## ✅ HASIL AKHIR YANG DIHARAPKAN

Setelah semua langkah selesai:

✅ Tabel `accounts` ada dan berfungsi  
✅ Tidak ada lagi referensi ke tabel `coas`  
✅ Semua foreign key mengarah ke `accounts`  
✅ Saldo awal default 0 (manual input)  
✅ Aplikasi berjalan tanpa error  
✅ Bisa membuat transaksi baru  
✅ Laporan keuangan bisa diakses  

---

## 🎉 SELAMAT!

Database Anda sudah bersih dan terstandarisasi. Nama tabel resmi sekarang adalah **`accounts`**.

**Catatan Penting:**
- Saldo awal **WAJIB** diisi manual oleh user
- Tidak ada logika otomatis yang mengisi `saldo_awal`
- Kolom `saldo_awal` memiliki default 0

---

**Dibuat oleh:** Kiro AI Assistant  
**Tanggal:** 14 Mei 2026  
**Proyek:** SIMACOST (Sistem Manufaktur Proses Costing)
