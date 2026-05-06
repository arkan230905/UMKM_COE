# 🔍 CARA CEK ERROR 500 DI HOSTING

**Error:** HTTP ERROR 500 - "Halaman ini tidak berfungsi"  
**URL:** http://jobcost.eadtmanufaktur.com/master-data/coa

---

## 🎯 KEMUNGKINAN PENYEBAB

### **1. Class DefaultCoaSeederBaru Tidak Ditemukan** ⚠️ (PALING MUNGKIN)
File `database/seeders/DefaultCoaSeederBaru.php` mungkin tidak ter-deploy atau tidak ter-autoload.

### **2. Cache Belum Di-Clear**
Laravel masih pakai cache lama.

### **3. Autoload Belum Di-Update**
Composer autoload belum di-regenerate.

### **4. Permission Error**
File tidak bisa dibaca oleh web server.

---

## 🔍 LANGKAH CEK ERROR

### **STEP 1: Cek Log Error di Hosting**

#### **Via SSH/Terminal:**
```bash
# Login ke hosting
ssh user@jobcost.eadtmanufaktur.com

# Masuk ke folder project
cd /path/to/your/project

# Lihat log error (20 baris terakhir)
tail -20 storage/logs/laravel.log

# Atau lihat log real-time
tail -f storage/logs/laravel.log
```

#### **Via cPanel File Manager:**
1. Login cPanel
2. Buka File Manager
3. Masuk ke folder: `storage/logs/`
4. Download file: `laravel.log`
5. Buka dengan text editor
6. Lihat error paling bawah

---

### **STEP 2: Cek File DefaultCoaSeederBaru Ada atau Tidak**

#### **Via SSH:**
```bash
# Cek file ada atau tidak
ls -la database/seeders/DefaultCoaSeederBaru.php

# Kalau ada, output:
# -rw-r--r-- 1 user user 12345 May 3 12:00 DefaultCoaSeederBaru.php

# Kalau tidak ada, output:
# ls: cannot access 'database/seeders/DefaultCoaSeederBaru.php': No such file or directory
```

#### **Via cPanel File Manager:**
1. Login cPanel
2. Buka File Manager
3. Masuk ke folder: `database/seeders/`
4. Cari file: `DefaultCoaSeederBaru.php`
5. Kalau tidak ada → FILE TIDAK TER-DEPLOY!

---

## 🔧 SOLUSI BERDASARKAN PENYEBAB

### **SOLUSI 1: File DefaultCoaSeederBaru Tidak Ada** ⚠️

**Penyebab:** File tidak ter-deploy oleh Jenkins

**Solusi:**

#### **A. Cek Git Repository:**
```bash
# Di local, cek file ada di Git atau tidak
git status
git ls-files | grep DefaultCoaSeederBaru

# Kalau tidak ada, add file:
git add database/seeders/DefaultCoaSeederBaru.php
git commit -m "Add: DefaultCoaSeederBaru seeder"
git push origin main
```

#### **B. Manual Upload via cPanel:**
1. Login cPanel
2. Buka File Manager
3. Masuk ke folder: `database/seeders/`
4. Upload file `DefaultCoaSeederBaru.php` dari local
5. Set permission: 644

#### **C. Manual Copy via SSH:**
```bash
# Copy dari local ke hosting via SCP
scp database/seeders/DefaultCoaSeederBaru.php user@jobcost.eadtmanufaktur.com:/path/to/project/database/seeders/
```

---

### **SOLUSI 2: Autoload Belum Di-Update**

**Penyebab:** Composer autoload belum tahu ada class baru

**Solusi:**

```bash
# Via SSH
cd /path/to/your/project

# Regenerate autoload
composer dump-autoload

# Atau kalau tidak ada composer:
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```

---

### **SOLUSI 3: Cache Belum Di-Clear**

**Penyebab:** Laravel masih pakai cache lama

**Solusi:**

```bash
# Via SSH
cd /path/to/your/project

# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

---

### **SOLUSI 4: Permission Error**

**Penyebab:** File tidak bisa dibaca oleh web server

**Solusi:**

```bash
# Via SSH
cd /path/to/your/project

# Set permission
chmod 644 database/seeders/DefaultCoaSeederBaru.php
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🚨 SOLUSI CEPAT (TANPA CEK LOG)

Kalau tidak bisa cek log, coba solusi ini berurutan:

### **1. Clear Cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### **2. Regenerate Autoload:**
```bash
composer dump-autoload
```

### **3. Cek File Ada:**
```bash
ls -la database/seeders/DefaultCoaSeederBaru.php
```

### **4. Kalau File Tidak Ada, Upload Manual:**
- Via cPanel File Manager
- Upload dari local ke `database/seeders/`

### **5. Clear Cache Lagi:**
```bash
php artisan cache:clear
php artisan config:clear
```

### **6. Test Lagi:**
- Buka: http://jobcost.eadtmanufaktur.com/master-data/coa
- Seharusnya sudah bisa

---

## 🔍 CEK DETAIL ERROR

Kalau masih error, kirim output dari command ini:

```bash
# 1. Cek log error
tail -20 storage/logs/laravel.log

# 2. Cek file ada
ls -la database/seeders/DefaultCoaSeederBaru.php

# 3. Cek autoload
composer dump-autoload -o

# 4. Cek permission
ls -la storage/logs/
```

---

## 📋 CHECKLIST TROUBLESHOOTING

- [ ] Cek log error: `tail -20 storage/logs/laravel.log`
- [ ] Cek file ada: `ls -la database/seeders/DefaultCoaSeederBaru.php`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Regenerate autoload: `composer dump-autoload`
- [ ] Set permission: `chmod 644 database/seeders/DefaultCoaSeederBaru.php`
- [ ] Test lagi: Buka halaman COA

---

## 🎯 KEMUNGKINAN BESAR MASALAHNYA

Berdasarkan error 500 setelah deploy, kemungkinan besar:

**File `DefaultCoaSeederBaru.php` tidak ter-deploy atau tidak ter-autoload.**

**Solusi tercepat:**
1. Cek file ada atau tidak: `ls -la database/seeders/DefaultCoaSeederBaru.php`
2. Kalau tidak ada, upload manual via cPanel
3. Regenerate autoload: `composer dump-autoload`
4. Clear cache: `php artisan cache:clear`
5. Test lagi

---

**Kirim output log error ke saya agar saya bisa bantu lebih spesifik!**

*Panduan dibuat: 3 Mei 2026*
