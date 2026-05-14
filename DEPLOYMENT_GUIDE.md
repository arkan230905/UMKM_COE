# 🚀 Deployment Guide - Perbaikan Error pr.hpp

## Masalah
Error pada halaman Laporan Laba Rugi:
```
Unknown column 'pr.hpp' in 'SELECT'
```

## Penyebab
- Query menggunakan kolom `pr.hpp` yang tidak ada di tabel `produks`
- Hosting masih menjalankan kode lama (cache)

## Solusi

### Opsi 1: Automatic Deployment (Recommended)
Jika Jenkins sudah ter-setup:
1. Jenkins akan otomatis pull kode terbaru
2. Jalankan script: `php force-deploy.php`
3. Selesai!

### Opsi 2: Manual Deployment via SSH

```bash
# 1. SSH ke hosting
ssh user@jobcost.eadtmanufaktur.com

# 2. Navigate ke project directory
cd /path/to/project

# 3. Pull latest code
git pull origin ghitha

# 4. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 5. Clear PHP opcache
php -r 'opcache_reset();'

# 6. Restart PHP-FPM (if available)
sudo systemctl restart php-fpm
# OR restart Apache
sudo systemctl restart apache2
```

### Opsi 3: Using Force Deploy Script

```bash
# 1. SSH ke hosting
ssh user@jobcost.eadtmanufaktur.com

# 2. Navigate ke project directory
cd /path/to/project

# 3. Run force deploy script
php force-deploy.php
```

### Opsi 4: Manual via cPanel

1. **File Manager:**
   - Navigate to `bootstrap/cache/`
   - Delete all files (except `.gitignore`)

2. **Terminal (if available):**
   ```bash
   rm -rf bootstrap/cache/*
   ```

3. **Restart Services:**
   - Go to cPanel → Select PHP Version
   - Click "Switch to PHP X.X.X" (same version)
   - This will restart PHP

## Verification

After deployment, verify the fix:

1. **Check Code:**
   ```bash
   grep -n "pr.harga_pokok" app/Http/Controllers/AkuntansiController.php
   ```
   Should show the corrected query.

2. **Test in Browser:**
   - Visit: http://jobcost.eadtmanufaktur.com/akuntansi/laba-rugi
   - Should load without error

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## What Was Changed

### File: `app/Http/Controllers/AkuntansiController.php`

**Before (WRONG):**
```php
SUM(pd.jumlah * COALESCE(pr.hpp, pr.harga_bom, 0)) as total_hpp
```

**After (CORRECT):**
```php
SUM(pd.jumlah * COALESCE(pr.harga_pokok, pr.harga_bom, 0)) as total_hpp
```

## Commits

- `08ebb84` - Fix: Change pr.hpp to pr.harga_pokok in labaRugi query

## Troubleshooting

### Error Still Persists?

1. **Check if code was updated:**
   ```bash
   grep "pr.hpp" app/Http/Controllers/AkuntansiController.php
   ```
   If this returns results, code hasn't been updated.

2. **Force pull:**
   ```bash
   git fetch origin
   git reset --hard origin/ghitha
   ```

3. **Clear all caches:**
   ```bash
   rm -rf bootstrap/cache/*
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Restart services:**
   - Restart PHP-FPM
   - Restart Apache/Nginx
   - Restart MySQL (if needed)

### Still Not Working?

1. Check PHP version compatibility
2. Check MySQL version (should support COALESCE)
3. Check file permissions
4. Check error logs: `storage/logs/laravel.log`

## Support

If you need help:
1. Run: `php diagnostic.php` to check system status
2. Check: `storage/logs/laravel.log` for detailed errors
3. Run: `php force-deploy.php` to force deployment
