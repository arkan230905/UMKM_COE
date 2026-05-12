# CLEAR ALL CACHE - PRODUCTION

## Masalah
Tunjangan masih 0 di production, padahal:
- ✓ Database data benar
- ✓ Controller data benar
- ✓ Blade rendering benar
- ✓ API endpoint bekerja
- ✗ Production masih cache file lama

## Solusi: Clear Semua Cache

### Step 1: SSH ke Production Server

```bash
ssh user@jobcost.eadtmanufaktur.com
cd /path/to/simcost
```

### Step 2: Pull Latest Code

```bash
git pull origin chindii2
```

### Step 3: Clear All Laravel Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

### Step 4: Verify File is Updated

```bash
# Check if file has correct URL
grep -n "transaksi/penggajian/pegawai" resources/views/transaksi/penggajian/create.blade.php

# Should show:
# fetch(`/transaksi/penggajian/pegawai/${pegawaiId}/data`)
```

### Step 5: Restart Web Server (if needed)

```bash
# If using Apache
sudo systemctl restart apache2

# If using Nginx
sudo systemctl restart nginx

# If using PHP-FPM
sudo systemctl restart php-fpm
```

### Step 6: Clear Browser Cache

Di browser production:
1. Press **Ctrl+Shift+Delete**
2. Select "All time"
3. Check: ☑ Cookies and other site data, ☑ Cached images and files
4. Click "Clear data"

### Step 7: Hard Refresh Page

1. Go to Tambah Penggajian page
2. Press **Ctrl+F5** (hard refresh)

### Step 8: Test

1. Open DevTools (F12)
2. Go to Console tab
3. Select pegawai
4. Check logs - should show:
   ```
   Tunjangan Transport: 150000.00
   Tunjangan Konsumsi: 375000.00
   ```

## Complete Commands (Copy-Paste)

```bash
# SSH to production
ssh user@jobcost.eadtmanufaktur.com

# Go to project directory
cd /path/to/simcost

# Pull latest code
git pull origin chindii2

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

# Verify file is updated
grep "transaksi/penggajian/pegawai" resources/views/transaksi/penggajian/create.blade.php

# Restart web server (choose one)
sudo systemctl restart apache2  # or nginx or php-fpm
```

## Verification Checklist

- [ ] Pulled latest code from chindii2
- [ ] Ran all cache clear commands
- [ ] Verified file has correct URL
- [ ] Restarted web server
- [ ] Cleared browser cache (Ctrl+Shift+Delete)
- [ ] Hard refreshed page (Ctrl+F5)
- [ ] Checked console logs show correct tunjangan values
- [ ] Tunjangan Transport shows 150.000 (not 0)
- [ ] Tunjangan Konsumsi shows 375.000 (not 0)

## Expected Result

After clearing cache:
- Tunjangan Transport: **150.000** (not 0)
- Tunjangan Konsumsi: **375.000** (not 0)
- Total Tunjangan: **525.000** (not 0)

## If Still 0

Then it's a different issue. Check:

1. **API endpoint error**:
   ```bash
   curl http://jobcost.eadtmanufaktur.com/transaksi/penggajian/pegawai/3/data
   ```
   Should return JSON with tunjangan values

2. **Check logs**:
   ```bash
   tail -100 storage/logs/laravel.log | grep -i tunjangan
   ```

3. **Check if relasi is NULL**:
   ```bash
   php artisan tinker
   >>> $pegawai = App\Models\Pegawai::with('jabatanRelasi')->find(3);
   >>> $pegawai->jabatanRelasi;  # Should not be NULL
   ```

## Support

If still not working after clearing cache, provide:
1. Output of `grep "transaksi/penggajian/pegawai" resources/views/transaksi/penggajian/create.blade.php`
2. Output of `curl http://jobcost.eadtmanufaktur.com/transaksi/penggajian/pegawai/3/data`
3. Console logs screenshot
4. Browser type and version
