# Cache Clear Instructions

## Problem yang Terjadi
Saat membuat akun baru, muncul error:
```
Duplicate entry '15-Ayam Potong' for key 'bahan_bakus_user_id_nama_bahan_unique'
```

## Penyebab
Code yang lama (yang masih memanggil seeder) ter-cache di PHP OPcache dan Laravel cache. Meskipun code sudah diupdate, versi yang ter-cache masih dijalankan.

## Solusi: Clear All Caches

Jalankan command berikut di terminal (local development):

```bash
php artisan optimize:clear
```

Command ini akan clear semua cache:
- ✅ Config cache
- ✅ Route cache  
- ✅ View cache
- ✅ Event cache
- ✅ Compiled classes
- ✅ Filament cache

## Untuk Production Server

Jika production server menggunakan PHP OPcache, perlu restart PHP-FPM atau web server:

```bash
# Untuk PHP-FPM
sudo systemctl restart php8.2-fpm

# Atau restart Apache/Nginx
sudo systemctl restart apache2
# atau
sudo systemctl restart nginx
```

## Verifikasi

Setelah clear cache, buat akun baru. Seharusnya:
1. ✅ Tidak ada error duplicate entry
2. ✅ Bahan Baku/Pendukung TIDAK otomatis dibuat
3. ✅ User harus membuat Bahan Baku/Pendukung sendiri
4. ✅ Saat user create item, Observer akan auto-assign COA berdasarkan nama item

## Test yang Dilakukan

Test telah dijalankan dan hasilnya:
```
✅ SUCCESS: NO bahan baku/pendukung auto-created!
✅ Users must create their own items, observer will auto-assign COA
```

## Code Changes Summary

File `app/Listeners/CreateDefaultUserData.php`:
- ❌ Removed: Calls to DefaultBahanBakuSeeder
- ❌ Removed: Calls to DefaultBahanPendukungSeeder
- ✅ Kept: DefaultCoaSeeder (users still need default COA)
- ✅ Kept: DefaultSatuanSeeder (users still need default Satuan)

Commit: `b20896a3` - "Remove auto-seeding of bahan baku/pendukung for new users"
