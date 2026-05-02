# 📖 PANDUAN IMPORT DATABASE - UNTUK TEMAN

## ⚠️ MASALAH YANG TERJADI

Error: `Table 'users' already exists`

**Penyebab:** Database sudah ada tabel `users`, lalu coba migrate lagi.

---

## ✅ SOLUSI 1: FRESH INSTALL (RECOMMENDED)

Ikuti langkah ini **STEP BY STEP**:

### **Step 1: Hapus Database Lama**

1. Buka **phpMyAdmin**
2. Pilih database `eadt_umkm` (atau nama database Anda)
3. Klik tab **"Operations"**
4. Scroll ke bawah, klik **"Drop the database (DROP)"**
5. Konfirmasi hapus

### **Step 2: Buat Database Baru**

1. Di phpMyAdmin, klik **"New"** di sidebar kiri
2. Nama database: `eadt_umkm`
3. Collation: `utf8mb4_unicode_ci`
4. Klik **"Create"**

### **Step 3: Import File SQL**

1. Pilih database `eadt_umkm` yang baru dibuat
2. Klik tab **"Import"**
3. Klik **"Choose File"**, pilih file SQL dari saya
4. Scroll ke bawah
5. **PENTING:** Centang **"Enable foreign key checks"** (biarkan default)
6. Klik **"Import"**
7. Tunggu sampai selesai

### **Step 4: Setup Laravel**

1. Buka terminal/command prompt
2. Masuk ke folder project:
   ```bash
   cd C:\xampppp\htdocs\UMKM_COE
   ```

3. Copy file `.env.example` jadi `.env`:
   ```bash
   copy .env.example .env
   ```

4. Edit file `.env`, sesuaikan:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=eadt_umkm
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. **JANGAN JALANKAN `php artisan migrate`** karena database sudah lengkap dari import!

### **Step 5: Test Login**

1. Buka browser: `http://localhost/UMKM_COE/public`
2. Login dengan akun yang ada di database

**SELESAI!** ✅

---

## ✅ SOLUSI 2: Skip Migration yang Sudah Ada

Jika tidak mau hapus database, gunakan cara ini:

### **Step 1: Tandai Migration Sudah Jalan**

Jalankan command ini di terminal:

```bash
php artisan migrate:status
```

Akan muncul daftar migration. Yang statusnya "Pending" perlu ditandai sebagai "Ran".

### **Step 2: Insert Manual ke Tabel Migrations**

Buka phpMyAdmin, pilih database `eadt_umkm`, klik tab SQL, jalankan query ini:

```sql
-- Tandai semua migration sebagai sudah jalan
INSERT INTO migrations (migration, batch) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2024_01_01_000001_create_coas_table', 1),
('2024_01_01_000002_create_satuans_table', 1),
('2024_01_01_000003_create_produks_table', 1)
-- ... dan seterusnya untuk semua migration
ON DUPLICATE KEY UPDATE migration=migration;
```

**CATATAN:** Anda perlu list semua file migration dari folder `database/migrations/`

### **Step 3: Verify**

```bash
php artisan migrate:status
```

Semua migration harus status "Ran".

---

## ❌ KESALAHAN UMUM

### **Jangan Lakukan Ini:**

1. ❌ Import SQL lalu `php artisan migrate` → ERROR!
2. ❌ `php artisan migrate` lalu import SQL → DATA HILANG!
3. ❌ Import SQL 2x → DUPLICATE DATA!

### **Yang Benar:**

1. ✅ Hapus database lama
2. ✅ Buat database baru
3. ✅ Import SQL
4. ✅ Setup `.env`
5. ✅ `php artisan key:generate`
6. ✅ **JANGAN** `php artisan migrate` (sudah ada dari import!)

---

## 🔍 TROUBLESHOOTING

### **Error: Foreign Key Constraint**

Jika masih dapat error foreign key saat import:

1. Minta file SQL yang sudah diperbaiki (jalankan script `perbaiki_foreign_key_sebelum_export.php` dulu)
2. Atau, saat import di phpMyAdmin:
   - Klik "Format-specific options"
   - Centang "Disable foreign key checks"

### **Error: Access Denied**

Cek file `.env`:
```
DB_USERNAME=root
DB_PASSWORD=         # Kosongkan jika tidak ada password
```

### **Error: Database Not Found**

Pastikan database sudah dibuat di phpMyAdmin dengan nama yang sama dengan `.env`:
```
DB_DATABASE=eadt_umkm
```

---

## 📞 BANTUAN

Jika masih ada masalah:

1. Screenshot error lengkap
2. Kirim ke saya
3. Sertakan informasi:
   - Langkah apa yang sudah dilakukan
   - Error message lengkap
   - Versi PHP dan MySQL

---

## ✅ CHECKLIST

Sebelum mulai, pastikan:

- [ ] XAMPP sudah running (Apache + MySQL)
- [ ] phpMyAdmin bisa dibuka
- [ ] File SQL sudah diterima
- [ ] Folder project sudah ada
- [ ] Composer sudah terinstall
- [ ] PHP versi 8.1 atau lebih tinggi

---

**INGAT:** Database dari import SQL sudah lengkap dengan semua tabel dan data. **TIDAK PERLU** `php artisan migrate` lagi!

---

*Panduan ini dibuat: 2 Mei 2026*
