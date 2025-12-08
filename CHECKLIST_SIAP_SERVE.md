# ✅ CHECKLIST: HALAMAN SIAP DI-SERVE

**Tanggal:** 8 Desember 2025  
**Status:** 🔄 IN PROGRESS

---

## 📋 QUICK CHECKLIST

### FASE 1: PHP Configuration
- [ ] PHP 8.2+ terinstall
- [ ] Extension `intl` diaktifkan
- [ ] Extension `zip` diaktifkan
- [ ] Extension `fileinfo` diaktifkan
- [ ] Extension `mbstring` diaktifkan
- [ ] Extension `pdo_mysql` diaktifkan

**Cara Cek:**
```bash
php -v
php -m | findstr intl
php -m | findstr zip
```

**Cara Aktifkan:**
1. Edit `C:\xampp\php\php.ini`
2. Uncomment (hapus `;`) di depan `extension=intl` dan `extension=zip`
3. Restart Apache

---

### FASE 2: Dependencies
- [ ] Composer terinstall
- [ ] Folder `vendor/` ada dan terisi
- [ ] File `vendor/autoload.php` ada
- [ ] Tidak ada error saat `composer install`

**Cara Install:**
```bash
composer install --no-interaction
```

**Jika error, coba:**
```bash
composer install --ignore-platform-reqs
```

---

### FASE 3: Environment Setup
- [ ] File `.env` ada
- [ ] `APP_KEY` sudah di-generate
- [ ] Database credentials terkonfigurasi
- [ ] `APP_URL` sudah diset

**Cara Setup:**
```bash
copy .env.example .env
php artisan key:generate
```

**Edit `.env`:**
```env
DB_DATABASE=eadt_umkm
DB_USERNAME=root
DB_PASSWORD=
```

---

### FASE 4: Database
- [ ] MySQL/MariaDB berjalan
- [ ] Database `eadt_umkm` sudah dibuat
- [ ] Migrations berhasil dijalankan
- [ ] Tables sudah terbuat
- [ ] (Optional) Seeders sudah dijalankan

**Cara Setup:**
```sql
CREATE DATABASE eadt_umkm;
```

```bash
php artisan migrate
php artisan db:seed
```

---

### FASE 5: File Permissions
- [ ] Folder `storage/` writable
- [ ] Folder `bootstrap/cache/` writable
- [ ] Tidak ada error permission

**Cara Set (Windows):**
```bash
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

---

### FASE 6: Cache & Optimization
- [ ] Cache cleared
- [ ] Config cleared
- [ ] Routes cleared
- [ ] Views cleared

**Cara Clear:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

### FASE 7: Server Test
- [ ] `php artisan serve` berjalan tanpa error
- [ ] Server listening di port 8000
- [ ] Tidak ada error di console

**Cara Test:**
```bash
php artisan serve
```

Expected output:
```
INFO  Server running on [http://127.0.0.1:8000].
```

---

### FASE 8: Browser Test
- [ ] Browser bisa akses `http://127.0.0.1:8000`
- [ ] Homepage muncul tanpa error
- [ ] CSS/JS ter-load dengan benar
- [ ] Halaman login accessible
- [ ] Tidak ada error 500/404

**Cara Test:**
1. Buka browser
2. Akses `http://127.0.0.1:8000`
3. Cek console browser (F12) untuk errors

---

## 🚀 AUTOMATED SETUP

Gunakan script otomatis untuk mempercepat setup:

```powershell
# Jalankan sebagai Administrator
.\setup-server.ps1
```

Script akan otomatis:
- ✅ Cek PHP version & extensions
- ✅ Install dependencies
- ✅ Setup .env
- ✅ Generate APP_KEY
- ✅ Set permissions
- ✅ Clear cache
- ✅ Start server

---

## 📊 STATUS SAAT INI

| Fase | Status | Notes |
|------|--------|-------|
| 1. PHP Config | ⚠️ PENDING | Extensions perlu diaktifkan |
| 2. Dependencies | ⚠️ PENDING | Menunggu extensions |
| 3. Environment | ✅ READY | .env.example tersedia |
| 4. Database | ❓ UNKNOWN | Perlu dicek |
| 5. Permissions | ❓ UNKNOWN | Perlu diset |
| 6. Cache | ❓ UNKNOWN | Perlu di-clear |
| 7. Server | ❌ NOT READY | Menunggu dependencies |
| 8. Browser | ❌ NOT READY | Menunggu server |

---

## 🎯 NEXT STEPS

### Prioritas Tinggi:
1. **Aktifkan PHP Extensions** (intl, zip)
   - Edit php.ini
   - Restart Apache
   - Verifikasi dengan `php -m`

2. **Install Dependencies**
   - Jalankan `composer install`
   - Tunggu sampai selesai (5-10 menit)

3. **Setup Database**
   - Buat database `eadt_umkm`
   - Jalankan migrations

### Prioritas Sedang:
4. Set file permissions
5. Clear cache
6. Test server

### Prioritas Rendah:
7. Run seeders (optional)
8. Optimize untuk production (jika perlu)

---

## 🐛 COMMON ISSUES

### Issue 1: "ext-intl is missing"
**Status:** ⚠️ CURRENT ISSUE  
**Solution:** Aktifkan di php.ini  
**File:** `C:\xampp\php\php.ini`  
**Line:** Uncomment `extension=intl`

### Issue 2: "ext-zip is missing"
**Status:** ⚠️ CURRENT ISSUE  
**Solution:** Aktifkan di php.ini  
**File:** `C:\xampp\php\php.ini`  
**Line:** Uncomment `extension=zip`

### Issue 3: "vendor/autoload.php not found"
**Status:** ⚠️ CURRENT ISSUE  
**Solution:** Jalankan `composer install`  
**Depends on:** Issue 1 & 2 harus diselesaikan dulu

### Issue 4: "No application encryption key"
**Status:** ✅ SOLVABLE  
**Solution:** `php artisan key:generate`

### Issue 5: "Database connection failed"
**Status:** ❓ UNKNOWN  
**Solution:** Cek MySQL berjalan, cek credentials di .env

---

## 📞 HELP & SUPPORT

### Dokumentasi:
- `PANDUAN_SIAP_SERVE.md` - Panduan lengkap step-by-step
- `README_SISTEM.md` - Dokumentasi sistem lengkap
- `SIAP_DIGUNAKAN.md` - Status fitur aplikasi

### Commands Reference:
```bash
# Cek PHP
php -v
php -m

# Composer
composer install
composer diagnose

# Laravel
php artisan about
php artisan migrate:status
php artisan route:list

# Server
php artisan serve
php artisan serve --port=8080
```

---

## ✅ COMPLETION CRITERIA

Sistem dianggap **SIAP DI-SERVE** jika:

1. ✅ Semua checklist di atas terpenuhi
2. ✅ `php artisan serve` berjalan tanpa error
3. ✅ Browser bisa akses homepage
4. ✅ Login page accessible
5. ✅ Tidak ada error 500 di logs
6. ✅ Database connection berhasil
7. ✅ Assets (CSS/JS) ter-load
8. ✅ Routing berfungsi normal

---

## 🎉 SETELAH SIAP

Ketika semua checklist selesai:

1. **Test Fitur Utama:**
   - Login/Logout
   - Dashboard
   - Master Data
   - Transaksi
   - Laporan

2. **Dokumentasi:**
   - Update status di `CHECKLIST_SIAP_SERVE.md`
   - Catat issues yang ditemukan
   - Buat troubleshooting guide jika perlu

3. **Production Ready:**
   - Set `APP_DEBUG=false`
   - Set `APP_ENV=production`
   - Run optimizations
   - Backup database

---

**Last Updated:** 8 Desember 2025  
**Current Status:** ⚠️ WAITING FOR PHP EXTENSIONS ACTIVATION

**Setelah extensions diaktifkan, jalankan `.\setup-server.ps1` untuk automated setup!**
