# Deployment SSH dengan Jenkins

## Overview
Sistem ini menggunakan **Jenkins CI/CD** untuk otomatis deploy dari GitHub ke SSH server.

**Flow:**
```
Local Changes → Git Commit → Git Push → GitHub → Jenkins Webhook → SSH Server Deploy
```

## Cara Deploy Perubahan ke SSH

### 1. Pastikan Semua File Sudah Diupdate di Local

File-file yang sudah diperbaiki:
- ✅ `database/seeders/CoaSeeder.php` - COA Ayam Goreng Bundo (84 COA)
- ✅ `database/seeders/DatabaseSeeder.php` - Memanggil CoaSeeder baru
- ✅ `app/Console/Commands/RegenerateProductionJournals.php` - Command regenerate journal
- ✅ `app/Http/Controllers/ProduksiController.php` - Fix BOP breakdown
- ✅ `app/Http/Controllers/LaporanController.php` - Fix production stock report
- ✅ `app/Http/Controllers/LaporanKasBankController.php` - Fix period_id -> coa_period_id
- ✅ `resources/views/transaksi/produksi/show.blade.php` - Tampilan detail produksi
- ✅ `resources/views/laporan/stok/index.blade.php` - Hide bahan pendukung

### 2. Commit dan Push ke GitHub

**Otomatis** (menggunakan script):
```cmd
git-push-fixes.bat
```

**Manual**:
```bash
# Stage files
git add database/seeders/CoaSeeder.php
git add database/seeders/DatabaseSeeder.php
git add app/Console/Commands/RegenerateProductionJournals.php
git add app/Http/Controllers/ProduksiController.php
git add app/Http/Controllers/LaporanController.php
git add app/Http/Controllers/LaporanKasBankController.php
git add resources/views/transaksi/produksi/show.blade.php
git add resources/views/laporan/stok/index.blade.php

# Commit
git commit -m "Fix: Update COA Seeder and Production System"

# Push to GitHub
git push origin main
```

### 3. Jenkins Otomatis Deploy

Setelah push ke GitHub:
1. **GitHub webhook** trigger Jenkins
2. **Jenkins** pull code terbaru dari GitHub
3. **Jenkins** deploy ke SSH server
4. **SSH server** update code otomatis

### 4. Verifikasi di SSH Server

SSH ke server dan cek:
```bash
# Masuk ke directory project
cd /path/to/project

# Cek apakah file sudah terupdate
cat database/seeders/DatabaseSeeder.php

# Harusnya memanggil CoaSeeder::class, bukan JasukeCoaSeeder

# Jalankan seeder di SSH (jika perlu)
php artisan db:seed --class=CoaSeeder --force

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Troubleshooting

### Seeder Lama Masih Jalan di SSH

**Penyebab**: File `DatabaseSeeder.php` belum terupdate di SSH

**Solusi**:
1. Pastikan sudah commit dan push `DatabaseSeeder.php` ke GitHub
2. Tunggu Jenkins selesai deploy
3. SSH ke server dan verify:
   ```bash
   git log --oneline -5
   # Cek apakah commit terbaru sudah ada
   
   git pull origin main
   # Force pull jika perlu
   ```

### Jenkins Tidak Trigger Otomatis

**Penyebab**: Webhook GitHub belum setup atau error

**Solusi**:
1. Cek webhook di GitHub: Settings > Webhooks
2. Pastikan Jenkins URL sudah benar
3. Test webhook manually di GitHub
4. Cek Jenkins logs untuk error

### File Tidak Terupdate di SSH

**Penyebab**: Jenkins config tidak include file tersebut

**Solusi**:
1. Cek Jenkins job configuration
2. Pastikan build step include `git pull`
3. Pastikan file tidak di-ignore (.gitignore)

## Important Notes

- ⚠️ **Jangan edit file langsung di SSH server** - Akan tertimpa saat deploy
- ✅ **Selalu edit di local, lalu push ke GitHub** - Biar Jenkins yang deploy
- ✅ **Test di local dulu** - Pastikan tidak ada error sebelum push
- ✅ **Backup database SSH** sebelum run seeder baru

## Rollback

Jika perlu rollback ke versi sebelumnya:

```bash
# Di SSH server
git log --oneline -10
# Cari commit yang ingin di-rollback

git checkout <commit-hash>
# Atau
git revert <commit-hash>

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Seeder yang Benar

**OLD (Tidak dipakai lagi):**
- ❌ `CoaTemplateSeeder.php`
- ❌ `JasukeCoaSeeder.php`
- ❌ `CoaAyamSeeder.php`

**NEW (Yang digunakan):**
- ✅ `CoaSeeder.php` - COA Ayam Goreng Bundo (84 COA standar)

**DatabaseSeeder.php:**
```php
$this->call([
    UserSeeder::class,
    CompanySeeder::class,
    CoaSeeder::class, // COA untuk Ayam Goreng Bundo
]);
```
