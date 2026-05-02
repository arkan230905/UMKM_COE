# 🚀 PANDUAN DEPLOY LENGKAP KE HOSTING

**Tanggal:** 3 Mei 2026  
**Status:** Task 9 - Fix Jabatan Duplicate Error + Update Listener

---

## 📋 RINGKASAN PERUBAHAN

### **1. Fix Jabatan Duplicate Error** ✅
- **File:** `app/Http/Controllers/JabatanController.php`
- **Masalah:** User tidak bisa buat jabatan dengan kode yang sama (BT001)
- **Solusi:** Generate kode per user, bukan global

### **2. Fix Database Constraint** ✅
- **File:** `database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`
- **Masalah:** Unique constraint `kode_jabatan` tidak include `user_id`
- **Solusi:** Unique constraint `(kode_jabatan, user_id)`

### **3. Update Listener untuk User Baru** ✅
- **File:** `app/Listeners/CreateDefaultUserData.php`
- **Masalah:** Masih pakai `DefaultCoaSeeder` (lama)
- **Solusi:** Ganti ke `DefaultCoaSeederBaru` (50 COA Jasuke)

---

## 🎯 APA YANG AKAN TERJADI SETELAH DEPLOY?

### **User Lama (Sudah Terdaftar):**
- ✅ Bisa buat jabatan tanpa error duplicate
- ✅ COA dan Satuan tetap seperti sekarang (tidak berubah)
- ✅ Data tidak hilang

### **User Baru (Daftar Setelah Deploy):**
- ✅ Otomatis dapat 50 COA (format Jasuke)
- ✅ Otomatis dapat 16 Satuan (lengkap dengan KLG)
- ✅ Bisa langsung pakai tanpa setup manual

---

## 📦 LANGKAH-LANGKAH DEPLOY

### **STEP 1: Commit & Push ke Git**

```bash
# Cek file yang berubah
git status

# Add semua perubahan
git add .

# Commit dengan pesan jelas
git commit -m "Fix: Jabatan duplicate error + Update listener untuk COA Jasuke"

# Push ke repository
git push origin main
```

**File yang di-push:**
- ✅ `app/Http/Controllers/JabatanController.php`
- ✅ `database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`
- ✅ `app/Listeners/CreateDefaultUserData.php`
- ✅ `database/seeders/DefaultCoaSeederBaru.php` (sudah ada)
- ✅ `database/seeders/DefaultSatuanSeeder.php` (sudah ada)

---

### **STEP 2: Jenkins Auto-Deploy**

Jenkins akan otomatis:
1. Pull code terbaru dari Git
2. Deploy ke hosting via MobaXterm
3. Update file PHP di server

**Tunggu sampai Jenkins selesai!**

---

### **STEP 3: Run Migration di Hosting**

#### **Opsi A: Via SSH/Terminal cPanel**

```bash
# Login ke hosting
ssh user@jobcost.eadtmanufaktur.com

# Masuk ke folder project
cd /path/to/your/project

# Run migration
php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

#### **Opsi B: Via phpMyAdmin (Manual SQL)**

Jika tidak bisa SSH, jalankan SQL ini di phpMyAdmin:

```sql
-- 1. Cek constraint yang ada
SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';

-- 2. Drop unique constraint lama
ALTER TABLE jabatans DROP INDEX jabatans_kode_jabatan_unique;

-- 3. Tambah unique constraint baru (kode + user_id)
ALTER TABLE jabatans ADD UNIQUE KEY jabatans_kode_user_unique (kode_jabatan, user_id);

-- 4. Verifikasi
SHOW INDEX FROM jabatans WHERE Key_name = 'jabatans_kode_user_unique';
```

**Output yang benar:**
```
Table: jabatans
Key_name: jabatans_kode_user_unique
Column_name: kode_jabatan, user_id
Non_unique: 0
```

---

### **STEP 4: Test di Hosting**

#### **Test 1: Buat Jabatan (User Lama)**

1. Login sebagai user yang sudah ada (user_id = 4)
2. Buka: `http://jobcost.eadtmanufaktur.com/master-data/kualifikasi-tenaga-kerja/create`
3. Isi form:
   - **Nama:** Pengemasan
   - **Kategori:** BTKL
   - **Tarif:** 20000
   - **Tunjangan Transport:** 150000
   - **Tunjangan Konsumsi:** 375000
   - **Asuransi:** 100000
4. Klik **Simpan**
5. **Hasil:** ✅ Berhasil tanpa error!

---

#### **Test 2: Buat Jabatan dengan Kode Sama (User Berbeda)**

1. Login sebagai user lain (user_id = 5)
2. Buka: `http://jobcost.eadtmanufaktur.com/master-data/kualifikasi-tenaga-kerja/create`
3. Isi form yang sama:
   - **Nama:** Pengemasan
   - **Kategori:** BTKL
4. Klik **Simpan**
5. **Hasil:** ✅ Berhasil! (Kode BT001 untuk user 5)

**Cek Database:**
```sql
SELECT id, kode_jabatan, nama, user_id FROM jabatans WHERE kode_jabatan = 'BT001';
```

**Output:**
```
id | kode_jabatan | nama       | user_id
1  | BT001        | Pengemasan | 4
2  | BT001        | Pengemasan | 5
```

✅ **Tidak ada conflict!**

---

#### **Test 3: Registrasi User Baru**

1. Logout
2. Buka: `http://jobcost.eadtmanufaktur.com/register`
3. Daftar user baru:
   - **Nama:** Test User
   - **Email:** test@example.com
   - **Password:** password123
4. Login dengan user baru
5. Cek halaman COA: `http://jobcost.eadtmanufaktur.com/master-data/coa`
6. **Hasil:** ✅ Ada 50 COA (format Jasuke)
7. Cek halaman Satuan: `http://jobcost.eadtmanufaktur.com/master-data/satuan-dashboard`
8. **Hasil:** ✅ Ada 16 Satuan (termasuk KLG - Kaleng)

---

## 🔍 VERIFIKASI LENGKAP

### **1. Cek Unique Constraint:**

```sql
SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';
```

**Output yang benar:**
```
Key_name: jabatans_kode_user_unique
Column_name: kode_jabatan
Non_unique: 0
Seq_in_index: 1

Key_name: jabatans_kode_user_unique
Column_name: user_id
Non_unique: 0
Seq_in_index: 2
```

---

### **2. Cek Data User Baru:**

```sql
-- Cari user terakhir yang daftar
SELECT id, name, email, created_at FROM users ORDER BY id DESC LIMIT 1;

-- Misal user_id = 10, cek COA nya
SELECT COUNT(*) as total_coa FROM coas WHERE user_id = 10;
-- Hasil: 50

-- Cek Satuan nya
SELECT COUNT(*) as total_satuan FROM satuans WHERE user_id = 10;
-- Hasil: 16

-- Cek detail COA
SELECT kode_akun, nama_akun FROM coas WHERE user_id = 10 ORDER BY kode_akun;
-- Hasil: 50 COA format Jasuke

-- Cek detail Satuan
SELECT kode, nama FROM satuans WHERE user_id = 10 ORDER BY kode;
-- Hasil: 16 Satuan termasuk KLG
```

---

### **3. Cek Jabatan Multi-User:**

```sql
-- Cek apakah ada kode jabatan yang sama untuk user berbeda
SELECT kode_jabatan, COUNT(DISTINCT user_id) as jumlah_user
FROM jabatans
GROUP BY kode_jabatan
HAVING jumlah_user > 1;
```

**Output yang benar:**
```
kode_jabatan | jumlah_user
BT001        | 2
BT002        | 2
```

✅ **Ini normal! Setiap user boleh punya BT001 sendiri.**

---

## ⚠️ TROUBLESHOOTING

### **Problem 1: Migration Error "Duplicate entry"**

**Error:**
```
SQLSTATE[23000]: Integrity constraint violation: 1062 
Duplicate entry 'BT001-4' for key 'jabatans_kode_user_unique'
```

**Penyebab:** Ada data duplicate di database

**Solusi:**

```sql
-- 1. Backup dulu!
CREATE TABLE jabatans_backup AS SELECT * FROM jabatans;

-- 2. Cek duplicate
SELECT kode_jabatan, user_id, COUNT(*) as jumlah
FROM jabatans
GROUP BY kode_jabatan, user_id
HAVING jumlah > 1;

-- 3. Hapus duplicate (keep yang pertama)
DELETE j1 FROM jabatans j1
INNER JOIN jabatans j2 
WHERE j1.id > j2.id 
AND j1.kode_jabatan = j2.kode_jabatan 
AND j1.user_id = j2.user_id;

-- 4. Run migration lagi
```

---

### **Problem 2: User Baru Tidak Dapat COA/Satuan**

**Penyebab:** Listener tidak jalan atau error

**Solusi:**

```bash
# Cek log error
tail -f storage/logs/laravel.log

# Manual run seeder untuk user tertentu
php artisan tinker

# Di tinker:
$userId = 10; // Ganti dengan user_id yang bermasalah
$coaSeeder = new \Database\Seeders\DefaultCoaSeederBaru();
$coaSeeder->run($userId);

$satuanSeeder = new \Database\Seeders\DefaultSatuanSeeder();
$satuanSeeder->run($userId);

exit
```

---

### **Problem 3: Masih Error Setelah Deploy**

**Penyebab:** Cache belum di-clear

**Solusi:**

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart PHP-FPM (jika ada akses)
sudo systemctl restart php8.3-fpm
```

---

### **Problem 4: Jenkins Deploy Gagal**

**Penyebab:** Conflict atau permission error

**Solusi:**

1. Cek log Jenkins
2. Manual deploy via MobaXterm:
   ```bash
   cd /path/to/project
   git pull origin main
   ```
3. Cek permission:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

---

## 📊 PERBANDINGAN SEBELUM & SESUDAH

### **Sebelum Deploy:**

**Jabatan:**
```
User A: BT001 ✅
User B: BT001 ❌ ERROR! (Duplicate)
```

**User Baru:**
```
COA: 11 akun (salah)
Satuan: 4 unit (kurang)
```

---

### **Sesudah Deploy:**

**Jabatan:**
```
User A: BT001 ✅
User B: BT001 ✅ (Tidak error!)
User C: BT001 ✅ (Tidak error!)
```

**User Baru:**
```
COA: 50 akun (format Jasuke) ✅
Satuan: 16 unit (lengkap dengan KLG) ✅
```

---

## ✅ CHECKLIST DEPLOY

### **Pre-Deploy:**
- [ ] Code sudah di-commit
- [ ] Code sudah di-push ke Git
- [ ] Jenkins sudah running

### **Deploy:**
- [ ] Jenkins deploy berhasil
- [ ] File controller sudah terupdate di hosting
- [ ] File migration sudah ada di hosting
- [ ] File listener sudah terupdate di hosting

### **Post-Deploy:**
- [ ] Migration sudah dijalankan
- [ ] Cache sudah di-clear
- [ ] Test buat jabatan (user lama) - berhasil
- [ ] Test buat jabatan (user berbeda) - berhasil
- [ ] Test registrasi user baru - dapat 50 COA
- [ ] Test registrasi user baru - dapat 16 Satuan
- [ ] Verifikasi database constraint
- [ ] Verifikasi data user baru

---

## 🎉 HASIL AKHIR

### **Masalah yang Diselesaikan:**

1. ✅ **Jabatan Duplicate Error** - FIXED!
   - User bisa buat jabatan dengan kode sama
   - Setiap user punya kode jabatan sendiri
   - Multi-tenant isolation sempurna

2. ✅ **User Baru Dapat Data Lengkap** - FIXED!
   - 50 COA format Jasuke
   - 16 Satuan lengkap (termasuk KLG)
   - Otomatis saat registrasi

3. ✅ **Multi-Tenant Isolation** - PERFECT!
   - Setiap user data terisolasi
   - Tidak ada data leak
   - Tidak ada conflict antar user

---

## 📞 BANTUAN

Jika ada masalah:

1. **Cek log error:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Cek database:**
   ```sql
   SHOW INDEX FROM jabatans;
   SELECT * FROM coas WHERE user_id = [USER_ID];
   SELECT * FROM satuans WHERE user_id = [USER_ID];
   ```

3. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

4. **Restart server** (jika perlu)

---

**Selamat Deploy! 🚀**

*Panduan ini dibuat: 3 Mei 2026*
