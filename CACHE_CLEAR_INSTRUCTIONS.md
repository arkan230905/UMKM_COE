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

### Test 1: Verifikasi NO Auto-seeding
```
✅ SUCCESS: NO bahan baku/pendukung auto-created!
✅ Users must create their own items, observer will auto-assign COA
```

### Test 2: Verifikasi Observer Auto-assigns COA
```
1️⃣ Test: Creating 'Ayam Potong' bahan baku...
   ✅ PASS: Correctly assigned to COA 1141 (Pers. Bahan Baku ayam potong)

2️⃣ Test: Creating 'Ayam Kampung' bahan baku...
   ✅ PASS: Correctly assigned to COA 1142 (Pers. Bahan Baku ayam kampung)

3️⃣ Test: Creating 'Kemasan' bahan pendukung...
   ✅ PASS: Correctly assigned to COA 1157 (Pers. Bahan Pendukung Kemasan)

✅ All tests completed successfully!
✅ Observer is working correctly - auto-assigns COA based on item name
```

## Observer COA Mapping Logic

**Bahan Baku:**
- Ayam Potong → 1141 (Pers. Bahan Baku ayam potong)
- Ayam Kampung → 1142 (Pers. Bahan Baku ayam kampung)
- Bebek → 1143 (Pers. Bahan Baku Bebek)
- Ayam (lainnya) → 1144 (Pers. Bahan Baku Ayam lainnya)
- Default → 114 (Persediaan Bahan Baku)

**Bahan Pendukung:**
- Air → 1150 (Pers. Bahan Pendukung Air)
- Minyak → 1151 (Pers. Bahan Pendukung Minyak)
- Tepung Terigu → 1152 (Pers. Bahan Pendukung Tepung Terigu)
- Maizena → 1153 (Pers. Bahan Pendukung Tepung Maizena)
- Lada → 1154 (Pers. Bahan Pendukung Lada)
- Kaldu → 1155 (Pers. Bahan Pendukung Kaldu)
- Bawang Putih → 1156 (Pers. Bahan Pendukung Bawang Putih)
- Kemasan → 1157 (Pers. Bahan Pendukung Kemasan)
- Default → 115 (Persediaan Bahan Pendukung)

## Code Changes Summary

File `app/Listeners/CreateDefaultUserData.php`:
- ❌ Removed: Calls to DefaultBahanBakuSeeder
- ❌ Removed: Calls to DefaultBahanPendukungSeeder
- ✅ Kept: DefaultCoaSeeder (users still need default COA)
- ✅ Kept: DefaultSatuanSeeder (users still need default Satuan)

Commit: `b20896a3` - "Remove auto-seeding of bahan baku/pendukung for new users"
