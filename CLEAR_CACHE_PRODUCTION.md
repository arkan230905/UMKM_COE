# Clear Cache Production - Tunjangan Issue

## Problem
Tunjangan masih menunjukkan 0 di production, padahal:
- ✓ Backend API bekerja dengan benar
- ✓ Database data benar
- ✓ File sudah ter-update dengan URL yang benar
- ✗ Browser masih cache file lama

## Solution: Clear All Caches

### Step 1: Clear Laravel Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

### Step 2: Clear Browser Cache

Di browser production:
1. Press **Ctrl+Shift+Delete** (Windows) atau **Cmd+Shift+Delete** (Mac)
2. Select "All time"
3. Check:
   - ☑ Cookies and other site data
   - ☑ Cached images and files
4. Click "Clear data"

### Step 3: Hard Refresh Page

1. Go to Tambah Penggajian page
2. Press **Ctrl+F5** (Windows) atau **Cmd+Shift+R** (Mac)
3. Or press **Ctrl+Shift+R** for hard refresh

### Step 4: Test

1. Open DevTools (F12)
2. Go to Console tab
3. Select pegawai from dropdown
4. Check console logs - should show:
   ```
   Data dari KUALIFIKASI: {...}
   Tunjangan Transport: 150000.00
   Tunjangan Konsumsi: 375000.00
   ```

## If Still Not Working

### Check Network Tab

1. Open DevTools (F12)
2. Go to Network tab
3. Select pegawai
4. Look for request to `/transaksi/penggajian/pegawai/3/data`
5. Click on it
6. Check Response tab - should show:
   ```json
   {
     "tunjangan_transport": "150000.00",
     "tunjangan_konsumsi": "375000.00",
     ...
   }
   ```

### Check if File is Deployed

```bash
# Check if file has correct URL
grep "transaksi/penggajian/pegawai" resources/views/transaksi/penggajian/create.blade.php

# Should show:
# fetch(`/transaksi/penggajian/pegawai/${pegawaiId}/data`)
```

### Force Redeploy

If file is not updated:
1. Pull latest code: `git pull origin chindii2`
2. Clear cache: `php artisan cache:clear`
3. Restart web server (if needed)

## Verification Checklist

- [ ] Ran `php artisan cache:clear`
- [ ] Cleared browser cache (Ctrl+Shift+Delete)
- [ ] Hard refreshed page (Ctrl+F5)
- [ ] Checked console logs show correct tunjangan values
- [ ] Checked Network tab shows correct API response
- [ ] Tunjangan Transport shows 150.000 (not 0)
- [ ] Tunjangan Konsumsi shows 375.000 (not 0)

## Expected Result

After clearing cache:
- Tunjangan Transport: **150.000** (not 0)
- Tunjangan Konsumsi: **375.000** (not 0)
- Total Tunjangan: **525.000** (not 0)

## If Still 0

Then it's a different issue - contact support with:
1. Console logs screenshot
2. Network response screenshot
3. Browser type and version
4. URL of production server
