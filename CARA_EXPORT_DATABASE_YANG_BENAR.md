# 📤 CARA EXPORT DATABASE YANG BENAR

## ✅ LANGKAH 1: Perbaiki Database Dulu

Sebelum export, jalankan script ini untuk membersihkan foreign key yang bermasalah:

```bash
php perbaiki_foreign_key_sebelum_export.php
```

**Output yang benar:**
```
✅ Database berhasil diperbaiki!
Total orphaned records ditemukan: X
Total records diperbaiki: X
```

---

## 📤 LANGKAH 2: Export dari phpMyAdmin

### **A. Buka phpMyAdmin**

1. Buka browser
2. Ketik: `http://localhost/phpmyadmin`
3. Login (biasanya username: `root`, password: kosong)

### **B. Pilih Database**

1. Di sidebar kiri, klik database **`eadt_umkm`**
2. Pastikan Anda melihat daftar tabel (bahan_bakus, bahan_pendukungs, dll)

### **C. Klik Tab "Export"**

1. Di bagian atas, klik tab **"Export"**
2. Anda akan melihat 2 pilihan: "Quick" dan "Custom"

### **D. Pilih "Custom"**

1. Klik radio button **"Custom - display all possible options"**
2. Akan muncul banyak opsi

### **E. Setting Export (PENTING!)**

#### **1. Tables:**
- Biarkan semua tabel tercentang (Select All)

#### **2. Output:**
- ✅ Pilih: **"Save output to a file"**
- Format: **SQL**
- Compression: **None** (atau **gzip** jika file besar)

#### **3. Format-specific options:**

**SANGAT PENTING - Centang ini:**

```
Object creation options:
  ✅ Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER statement
  ✅ Add IF NOT EXISTS (less efficient as indexes will be created one by one)
  ✅ Add CREATE DATABASE / USE statement
  
Data creation options:
  ✅ Complete inserts
  ✅ Extended inserts
  
Database system or older MySQL server to maximize output compatibility with:
  ✅ NONE (default)
```

**PALING PENTING:**

Scroll ke bawah, cari bagian **"Disable foreign key checks"**

```
  ✅ Disable foreign key checks
```

**INI YANG PALING PENTING!** Jika tidak dicentang, teman Anda akan dapat error foreign key!

### **F. Klik "Export"**

1. Scroll ke paling bawah
2. Klik tombol **"Export"**
3. File SQL akan otomatis terdownload
4. Nama file biasanya: `eadt_umkm.sql`

---

## 📋 CHECKLIST SEBELUM KIRIM

Sebelum kirim file SQL ke teman, pastikan:

- [x] Sudah jalankan `perbaiki_foreign_key_sebelum_export.php`
- [x] Export dengan opsi "Custom"
- [x] **Centang "Disable foreign key checks"** ← PENTING!
- [x] Centang "Add DROP TABLE"
- [x] Centang "Add IF NOT EXISTS"
- [x] Centang "Add CREATE DATABASE / USE statement"
- [x] File SQL berhasil terdownload
- [x] Ukuran file masuk akal (tidak 0 KB)

---

## 📨 KIRIM KE TEMAN

### **File yang Perlu Dikirim:**

1. **File SQL** (hasil export)
2. **File `.env.example`** (dari project Laravel)
3. **Panduan:** `PANDUAN_UNTUK_TEMAN_IMPORT_DATABASE.md`

### **Cara Kirim:**

- **Google Drive** (recommended untuk file besar)
- **WeTransfer**
- **Email** (jika file < 25 MB)
- **WhatsApp** (jika file < 100 MB, compress dulu)

---

## 🔍 VERIFIKASI FILE SQL

Sebelum kirim, buka file SQL dengan text editor (Notepad++, VSCode), cek:

### **1. Baris Pertama Harus Ada:**

```sql
SET FOREIGN_KEY_CHECKS=0;
```

atau

```sql
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
```

**Jika tidak ada**, export ulang dengan centang "Disable foreign key checks"!

### **2. Ada CREATE DATABASE:**

```sql
CREATE DATABASE IF NOT EXISTS `eadt_umkm` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eadt_umkm`;
```

### **3. Ada DROP TABLE:**

```sql
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `coas`;
-- dst...
```

### **4. Baris Terakhir Harus Ada:**

```sql
SET FOREIGN_KEY_CHECKS=1;
```

atau

```sql
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
```

---

## ⚠️ TROUBLESHOOTING

### **Problem: File SQL Terlalu Besar**

**Solusi:**
1. Export dengan compression **gzip**
2. File akan jadi `.sql.gz`
3. Teman bisa import langsung file `.gz` di phpMyAdmin

### **Problem: Export Gagal / Timeout**

**Solusi:**
1. Export per tabel (pilih beberapa tabel saja)
2. Atau gunakan command line:

```bash
mysqldump -u root -p eadt_umkm > eadt_umkm.sql
```

### **Problem: Teman Tetap Dapat Error Foreign Key**

**Penyebab:** Lupa centang "Disable foreign key checks"

**Solusi:**
1. Export ulang dengan centang opsi tersebut
2. Atau, teman bisa edit file SQL manual:
   - Tambahkan di baris pertama: `SET FOREIGN_KEY_CHECKS=0;`
   - Tambahkan di baris terakhir: `SET FOREIGN_KEY_CHECKS=1;`

---

## 📞 BANTUAN UNTUK TEMAN

Jika teman Anda masih dapat error, minta dia:

1. Screenshot error lengkap
2. Kirim balik ke Anda
3. Cek apakah:
   - Database sudah dihapus sebelum import?
   - File SQL yang diimport sudah yang terbaru?
   - Tidak jalankan `php artisan migrate` setelah import?

---

## ✅ KESIMPULAN

**Urutan yang BENAR:**

1. ✅ Jalankan `perbaiki_foreign_key_sebelum_export.php`
2. ✅ Export dengan "Disable foreign key checks"
3. ✅ Verifikasi file SQL
4. ✅ Kirim ke teman
5. ✅ Teman hapus database lama
6. ✅ Teman buat database baru
7. ✅ Teman import file SQL
8. ✅ Teman setup `.env`
9. ✅ **JANGAN** `php artisan migrate`!
10. ✅ Test login

**SELESAI!** 🎉

---

*Panduan ini dibuat: 2 Mei 2026*
