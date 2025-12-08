# 🚀 PANDUAN MEMASTIKAN HALAMAN SIAP DI-SERVE

**Status Saat Ini:** ⚠️ PERLU KONFIGURASI  
**Tanggal:** 8 Desember 2025

---

## ❌ MASALAH YANG DITEMUKAN

### 1. PHP Extensions Belum Diaktifkan
```
❌ ext-intl (Required by Filament)
❌ ext-zip (Required for Composer)
```

### 2. Dependencies Belum Terinstall
```
❌ Folder vendor/ tidak ada
❌ Composer dependencies belum diinstall
```

---

## ✅ SOLUSI LANGKAH DEMI LANGKAH

### LANGKAH 1: Aktifkan PHP Extensions

#### Untuk XAMPP (Windows):

1. **Buka file php.ini**
   ```
   Lokasi: C:\xampp\php\php.ini
   ```

2. **Cari dan uncomment (hapus tanda ;) baris berikut:**
   ```ini
   ;extension=intl
   ;extension=zip
   ;extension=fileinfo
   ;extension=mbstring
   ```
   
   **Menjadi:**
   ```ini
   extension=intl
   extension=zip
   extension=fileinfo
   extension=mbstring
   ```

3. **Simpan file php.ini**

4. **Restart Apache** (jika sedang berjalan)
   - Buka XAMPP Control Panel
   - Stop Apache
   - Start Apache lagi

5. **Verifikasi extensions sudah aktif:**
   ```bash
   php -m | findstr intl
   php -m | findstr zip
   ```
   
   Jika berhasil, akan muncul:
   ```
   intl
   zip
   ```

---

### LANGKAH 2: Install Dependencies

Setelah extensions diaktifkan, jalankan:

```bash
composer install --no-interaction
```

**Catatan:** Proses ini akan memakan waktu 5-10 menit tergantung koneksi internet.

**Jika masih error**, coba:
```bash
composer install --ignore-platform-reqs --no-interaction
```

---

### LANGKAH 3: Setup Environment

1. **Copy .env.example ke .env** (jika belum ada):
   ```bash
   copy .env.example .env
   ```

2. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

3. **Edit .env untuk database:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=eadt_umkm
   DB_USERNAME=root
   DB_PASSWORD=
   ```

---

### LANGKAH 4: Setup Database

1. **Buat database** (via phpMyAdmin atau MySQL CLI):
   ```sql
   CREATE DATABASE eadt_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Jalankan migrations:**
   ```bash
   php artisan migrate
   ```

3. **Jalankan seeders** (optional, untuk data awal):
   ```bash
   php artisan db:seed
   ```

---

### LANGKAH 5: Set File Permissions

```bash
# Untuk Windows (jalankan sebagai Administrator)
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

---

### LANGKAH 6: Clear & Optimize Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

### LANGKAH 7: Serve Aplikasi

```bash
php artisan serve
```

Aplikasi akan berjalan di:
```
http://127.0.0.1:8000
```

---

## 🔍 VERIFIKASI SISTEM SIAP

### Checklist Akhir:

- [ ] PHP extensions (intl, zip) sudah aktif
- [ ] Folder vendor/ sudah ada dan terisi
- [ ] File .env sudah ada dan terkonfigurasi
- [ ] Database sudah dibuat dan ter-migrate
- [ ] php artisan serve berjalan tanpa error
- [ ] Browser bisa akses http://127.0.0.1:8000
- [ ] Halaman login muncul tanpa error

---

## 🐛 TROUBLESHOOTING

### Error: "ext-intl is missing"
**Solusi:** Aktifkan extension=intl di php.ini (Langkah 1)

### Error: "ext-zip is missing"
**Solusi:** Aktifkan extension=zip di php.ini (Langkah 1)

### Error: "vendor/autoload.php not found"
**Solusi:** Jalankan `composer install` (Langkah 2)

### Error: "No application encryption key"
**Solusi:** Jalankan `php artisan key:generate` (Langkah 3)

### Error: "Database connection failed"
**Solusi:** 
1. Pastikan MySQL/MariaDB berjalan
2. Cek credentials di .env
3. Pastikan database sudah dibuat

### Error: "Permission denied" saat menulis file
**Solusi:** Set permissions untuk storage dan bootstrap/cache (Langkah 5)

---

## 📊 STATUS SISTEM

| Komponen | Status | Action Required |
|----------|--------|-----------------|
| PHP Version | ✅ 8.2.12 | None |
| ext-intl | ❌ Disabled | Enable in php.ini |
| ext-zip | ❌ Disabled | Enable in php.ini |
| Composer | ✅ Installed | None |
| Dependencies | ❌ Not installed | Run composer install |
| .env | ✅ Example exists | Copy & configure |
| Database | ❓ Unknown | Create & migrate |
| Permissions | ❓ Unknown | Set permissions |

---

## 🎯 QUICK START (Setelah Extensions Diaktifkan)

```bash
# 1. Install dependencies
composer install

# 2. Setup environment
copy .env.example .env
php artisan key:generate

# 3. Setup database (pastikan MySQL berjalan)
# Buat database 'eadt_umkm' via phpMyAdmin

# 4. Migrate database
php artisan migrate

# 5. Clear cache
php artisan cache:clear
php artisan config:clear

# 6. Serve aplikasi
php artisan serve
```

Buka browser: http://127.0.0.1:8000

---

## 📞 BANTUAN LEBIH LANJUT

Jika masih mengalami masalah:

1. Cek file `storage/logs/laravel.log` untuk error details
2. Jalankan `php artisan about` untuk melihat environment info
3. Jalankan `composer diagnose` untuk cek Composer issues

---

## 📝 CATATAN PENTING

### Untuk Development:
- Gunakan `php artisan serve` untuk testing lokal
- Debug mode: `APP_DEBUG=true` di .env
- Log level: `LOG_LEVEL=debug` di .env

### Untuk Production:
- Gunakan web server (Apache/Nginx)
- Debug mode: `APP_DEBUG=false` di .env
- Jalankan optimizations:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

---

**Dibuat:** 8 Desember 2025  
**Status:** ⚠️ MENUNGGU KONFIGURASI PHP EXTENSIONS

**Setelah mengikuti panduan ini, sistem akan siap di-serve!** 🚀
