# 🚀 PANDUAN LENGKAP DEPLOY KE HOSTING

## ⚠️ MASALAH YANG DITEMUKAN DI HOSTING

Saat mencoba deploy, ditemukan masalah:
```
PHP Fatal error: Failed opening required '/var/www/html/vendor/autoload.php'
```

**Artinya:** Folder `vendor` (Composer dependencies) belum diinstall di hosting!

---

## ✅ SOLUSI: DEPLOY DENGAN COMPOSER INSTALL

### **CARA TERMUDAH: Copy-Paste Command Ini**

Buka PowerShell dan jalankan command ini (SATU BARIS):

```powershell
ssh simcost@103.134.154.77 "cd /var/www/html; sudo rm -f COMMAND_IDCLOUDHOST.txt DEPLOYMENT_SUCCESS_GOOD_DESIGN.md DEPLOY_IDCLOUDHOST.txt DEPLOY_SEKARANG.txt LANGKAH_DEPLOY_HOSTING.md MULAI_DISINI.txt README_DEPLOYMENT.md deploy-idcloudhost.sh; sudo git stash; sudo git pull origin main; sudo composer install --no-dev --optimize-autoloader; sudo php artisan config:clear; sudo php artisan cache:clear; sudo php artisan view:clear; sudo php artisan route:clear; sudo php artisan config:cache; sudo php artisan route:cache; sudo chmod -R 755 storage bootstrap/cache; sudo chown -R www-data:www-data storage bootstrap/cache; echo 'DEPLOYMENT COMPLETED!'"
```

**Masukkan password SSH Anda ketika diminta.**

---

## 📋 ATAU: STEP-BY-STEP MANUAL (LEBIH AMAN)

### **Step 1: Login ke Hosting**
```bash
ssh simcost@103.134.154.77
```
Masukkan password Anda.

### **Step 2: Masuk ke Folder Project**
```bash
cd /var/www/html
```

### **Step 3: Hapus File Temporary**
```bash
sudo rm -f COMMAND_IDCLOUDHOST.txt DEPLOYMENT_SUCCESS_GOOD_DESIGN.md DEPLOY_IDCLOUDHOST.txt DEPLOY_SEKARANG.txt LANGKAH_DEPLOY_HOSTING.md MULAI_DISINI.txt README_DEPLOYMENT.md deploy-idcloudhost.sh
```

### **Step 4: Simpan Perubahan Lokal**
```bash
sudo git stash
```

### **Step 5: Pull Perubahan Terbaru**
```bash
sudo git pull origin main
```

Anda akan melihat:
```
From https://github.com/arkan230905/UMKM_COE
 * branch            main       -> FETCH_HEAD
Already up to date.
```

### **Step 6: Install Composer Dependencies (PENTING!)**
```bash
sudo composer install --no-dev --optimize-autoloader
```

**Ini akan memakan waktu 2-5 menit.** Tunggu sampai selesai!

Anda akan melihat output seperti:
```
Loading composer repositories with package information
Installing dependencies from lock file
Package operations: X installs, Y updates, Z removals
  - Installing vendor/package (version)
...
Generating optimized autoload files
```

### **Step 7: Clear Laravel Cache**
```bash
sudo php artisan config:clear
sudo php artisan cache:clear
sudo php artisan view:clear
sudo php artisan route:clear
```

### **Step 8: Optimize Aplikasi**
```bash
sudo php artisan config:cache
sudo php artisan route:cache
```

### **Step 9: Fix Permissions**
```bash
sudo chmod -R 755 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### **Step 10: Keluar dari SSH**
```bash
exit
```

---

## 🧪 TESTING SETELAH DEPLOY

### **1. Test Dashboard (Desain Baru)**
Buka: https://jobcost.eadtmanufaktur.com/dashboard

**Yang harus terlihat:**
- ✅ Sidebar warna coklat (#8A6B48)
- ✅ Card-card dengan shadow
- ✅ Statistik dengan icon
- ✅ Tidak ada error 500

### **2. Test Halaman Akuntansi**

Test semua halaman ini:

| No | Halaman | URL |
|----|---------|-----|
| 1 | Harga Pokok Produksi | /bom |
| 2 | Laporan Penggajian | /laporan/penggajian |
| 3 | Laporan Pembayaran Beban | /laporan/pembayaran-beban |
| 4 | Laporan Pelunasan Utang | /laporan/pelunasan-utang |
| 5 | Laporan Kas dan Bank | /laporan/kas-bank |
| 6 | Jurnal Umum | /akuntansi/jurnal-umum |
| 7 | Buku Besar | /akuntansi/buku-besar |
| 8 | Neraca Saldo | /akuntansi/neraca-saldo |
| 9 | Laporan Posisi Keuangan | /akuntansi/laporan-posisi-keuangan |
| 10 | Laba Rugi | /akuntansi/laba-rugi |

**Pastikan:**
- ✅ Tidak ada error 500
- ✅ Data yang muncul hanya data user yang login
- ✅ Halaman loading dengan cepat

---

## ❓ TROUBLESHOOTING

### **Error: "vendor/autoload.php not found"**
**Solusi:** Jalankan `sudo composer install` di hosting

### **Error: "bootstrap/cache not found"**
**Solusi:** 
```bash
sudo mkdir -p bootstrap/cache
sudo chmod -R 755 bootstrap/cache
```

### **Error 500 setelah deploy**
**Solusi:** Cek log error
```bash
sudo tail -50 storage/logs/laravel.log
```

### **Halaman masih tampil lama**
**Solusi:** Restart PHP-FPM
```bash
sudo systemctl restart php8.1-fpm
```

### **Git pull conflict**
**Solusi:** Reset ke GitHub
```bash
sudo git reset --hard origin/main
```

---

## 📊 SUMMARY PERUBAHAN

### **Commits yang Sudah Di-Push:**
1. `783c418` - CRITICAL FIX: Add multi-tenant isolation to ALL AkuntansiController methods
2. `90c8f9e` - CRITICAL FIX: Add multi-tenant isolation to BomController and LaporanController
3. `730c613` - Add modern dashboard design with brown sidebar

### **Files yang Diubah:**
- `app/Http/Controllers/AkuntansiController.php` - Added user_id filters
- `app/Http/Controllers/BomController.php` - Added user_id filter
- `app/Http/Controllers/LaporanController.php` - Added user_id filters
- `app/Http/Controllers/DashboardController.php` - Multi-tenant security
- `resources/views/dashboard.blade.php` - Modern design
- `resources/views/layouts/app.blade.php` - Modern layout
- `resources/views/layouts/sidebar.blade.php` - Brown sidebar
- `public/css/modern-dashboard.css` - Modern styles

### **Security Improvements:**
- ✅ All queries now filter by `user_id`
- ✅ Users can only see their own data
- ✅ Multi-tenant isolation complete

---

## ✅ CHECKLIST DEPLOYMENT

- [x] Semua controller sudah diperbaiki
- [x] Semua perubahan sudah di-commit
- [x] Semua perubahan sudah di-push ke GitHub
- [ ] **Login ke hosting via SSH**
- [ ] **Pull perubahan dari GitHub**
- [ ] **Install Composer dependencies** ← PENTING!
- [ ] **Clear Laravel cache**
- [ ] **Test semua halaman**

---

## 🎉 SETELAH BERHASIL

Anda akan memiliki:
1. ✅ Dashboard dengan desain modern
2. ✅ Semua halaman akuntansi berfungsi normal
3. ✅ Multi-tenant security aktif
4. ✅ Setiap user hanya melihat data mereka sendiri

**Sistem Anda sudah aman dan modern! 🚀**

---

## 📞 BUTUH BANTUAN?

Jika ada masalah, cek file-file ini:
- `MULAI_DARI_SINI.md` - Quick start guide
- `INSTRUKSI_DEPLOY_SEKARANG.md` - Detailed instructions
- `Deploy-ToHosting-Fixed.ps1` - PowerShell script (sudah include composer install)

---

**PENTING:** Jangan lupa jalankan `sudo composer install` di hosting!
