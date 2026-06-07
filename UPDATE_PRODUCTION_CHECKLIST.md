# Production Update Checklist

## Issue: Preview Jurnal berbeda antara Local dan Production

### Root Cause
Production server masih menggunakan kode lama atau cached version dari `create.blade.php`

### Langkah-Langkah Update Production

#### 1. SSH ke Server Production
```bash
ssh user@jobcost.eadtmanufaktur.com
cd /path/to/application
```

#### 2. Backup Current State (PENTING!)
```bash
# Backup database
php artisan backup:run

# Atau manual backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

#### 3. Pull Latest Code
```bash
# Check current branch
git branch

# Pull latest changes
git pull origin nayla

# Verify changes pulled
git log -1
```

#### 4. Clear All Caches
```bash
# Method 1: Using script
chmod +x clear_cache_production.sh
./clear_cache_production.sh

# Method 2: Manual commands
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan clear-compiled

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 5. Update Composer Dependencies (if needed)
```bash
composer install --optimize-autoloader --no-dev
```

#### 6. Run Migrations (if any new migrations)
```bash
php artisan migrate --force
```

#### 7. Set Correct Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 8. Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
# atau
sudo service php8.1-fpm restart

# Restart web server
sudo systemctl restart nginx
# atau
sudo systemctl restart apache2
```

#### 9. Browser Cache Clear
Di browser production:
- Tekan `Ctrl + Shift + R` (hard refresh)
- Atau `Ctrl + F5`
- Atau buka Developer Tools → Network → Disable Cache

#### 10. Verify Changes

##### A. Check File Version
```bash
# Check last modified date of create.blade.php
ls -lh resources/views/transaksi/pembelian/create.blade.php

# Check git commit
git log -1 --oneline resources/views/transaksi/pembelian/create.blade.php
```

##### B. Test Preview Jurnal
1. Login ke aplikasi production
2. Buka halaman Tambah Pembelian
3. Tambahkan item (contoh: Ayam Potong)
4. Scroll ke bawah ke "Preview Jurnal Akuntansi"
5. Verify:
   - ✅ Kolom "Keterangan" harus menampilkan nama bahan (contoh: "Ayam Potong")
   - ✅ Kolom "Akun" harus menampilkan badge COA dan nama COA (contoh: "1141 Pers. Bahan Baku Ayam Potong")
   - ❌ BUKAN "Persediaan Barang" di kolom keterangan

##### C. Check Logs
```bash
tail -f storage/logs/laravel.log
```

Look for:
- `Loaded COA relations for journal creation`
- `Bahan Baku COA Check`

#### 11. Run Diagnostic Command
```bash
php artisan diagnose:pembelian-coa --user-id=3
```

Expected output:
```
=== DIAGNOSTIC REPORT: Pembelian COA Mapping ===
User ID: 3

1. BAHAN BAKU COA MAPPING:
  ✅ OK [ID: 1] Ayam Potong - COA: 1141 (Pers. Bahan Baku Ayam Potong) - Active
  ✅ OK [ID: 2] Ayam Kampung - COA: 1142 (Pers. Bahan Baku Ayam Kampung) - Active
  ...
```

### Troubleshooting

#### Issue: Changes not appearing after pull
**Solution:**
```bash
# Force pull
git fetch --all
git reset --hard origin/nayla

# Clear all caches again
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

#### Issue: Permission denied errors
**Solution:**
```bash
# Fix permissions
sudo chown -R www-data:www-data /path/to/application
sudo chmod -R 755 storage bootstrap/cache
```

#### Issue: 500 Internal Server Error
**Solution:**
```bash
# Check error logs
tail -100 storage/logs/laravel.log

# Check web server logs
tail -100 /var/log/nginx/error.log
# atau
tail -100 /var/log/apache2/error.log

# Enable debug mode temporarily
php artisan config:clear
# Set APP_DEBUG=true in .env
# Check error in browser
# Set APP_DEBUG=false back
```

#### Issue: Browser still showing old version
**Solution:**
1. Hard refresh: `Ctrl + Shift + R`
2. Clear browser cache completely
3. Try incognito/private mode
4. Check if browser is caching: Open DevTools → Network → Check "Disable cache"

### Verification Checklist

After all steps completed:
- [ ] Git log shows latest commit
- [ ] Cache cleared (no .php files in `storage/framework/views/`)
- [ ] Permissions correct (storage writable)
- [ ] Services restarted
- [ ] Browser cache cleared
- [ ] Preview Jurnal shows correct format
- [ ] COA mapping diagnostic passes
- [ ] Can create pembelian successfully
- [ ] Journal entries created in database

### Expected Result

**Preview Jurnal should show:**

| Keterangan | Akun | Ref | Debit | Kredit |
|------------|------|-----|-------|--------|
| **Persediaan Barang** | | | | |
| Ayam Potong | 1141 Pers. Bahan Baku Ayam Potong | 1141 | Rp 1.800.000 | |
| **PPN Masukan** | | | | |
| PPN Masukan | 127 PPN Masukan | 127 | Rp 176.000 | |
| **Biaya Kirim** | | | | |
| - | | | | |
| **Pembayaran** | | | | |
| Kas | 112 Kas | 112 | | Rp 1.776.000 |

**NOT like this (OLD VERSION):**

| Keterangan | Akun | Ref | Debit | Kredit |
|------------|------|-----|-------|--------|
| **Persediaan Barang** | | | | |
| Persediaan Barang | 114 Persediaan Bahan Baku | ... | | |

### Contact

If issues persist after following all steps:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server logs
3. Verify git commit matches local
4. Contact dev team with:
   - Git log output
   - Laravel log excerpt
   - Screenshot of preview jurnal
   - Output of diagnostic command

---
**Last Updated:** 2026-06-07
**Version:** 1.0.0
