# Hotfix: Perusahaan user_id Column Error

## Problem
Error pada halaman `/tentang-perusahaan/detail`:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'user_id' in 'WHERE'
```

## Root Cause
- Kode mencoba filter data `perusahaan` berdasarkan `user_id` untuk multi-tenant support
- Kolom `user_id` belum ada di tabel `perusahaan` di database hosting
- Migration belum dijalankan di hosting

## Solution Applied

### 1. Temporary Fix (Immediate)
Menghapus filter `user_id` dari semua query Perusahaan:

**Files Modified:**
- `app/Http/Controllers/PerusahaanController.php`
  - `index()`: Ubah `Perusahaan::where('user_id', auth()->id())->first()` → `Perusahaan::first()`
  - `edit()`: Ubah `Perusahaan::where('user_id', auth()->id())->first()` → `Perusahaan::first()`
  - `update()`: Ubah `Perusahaan::where('user_id', auth()->id())->first()` → `Perusahaan::first()`
  - `updateCompanyField()`: Ubah `Perusahaan::where('user_id', auth()->id())->first()` → `Perusahaan::first()`

- `app/Http/Controllers/PenjualanController.php`
  - `struk()`: Ubah `Perusahaan::where('user_id', auth()->id())->first()` → `Perusahaan::first()`

### 2. Permanent Fix (Later)
Ketika siap untuk multi-tenant:
1. Jalankan migration: `php artisan migrate`
2. Migration akan menambahkan kolom `user_id` ke tabel `perusahaan`
3. Update kembali semua query untuk menggunakan filter `user_id`

## Files Created
- `database/migrations/2026_05_07_090000_add_user_id_to_perusahaan_table.php` - Migration untuk menambah kolom user_id
- `clear-cache.php` - Script untuk clear cache setelah deployment

## Deployment Steps

### Manual Deployment
```bash
# 1. Pull latest code
git pull origin ghitha

# 2. Clear all caches
php clear-cache.php

# 3. Or manually run:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### Jenkins Deployment
Jenkins akan otomatis:
1. Pull kode terbaru
2. Menjalankan `composer install`
3. Clear cache (jika ada hook)
4. Deploy ke hosting

## Verification
Setelah deployment, akses:
- http://jobcost.eadtmanufaktur.com/tentang-perusahaan/detail

Halaman seharusnya bisa diakses tanpa error.

## Future: Multi-Tenant Implementation
Ketika siap untuk full multi-tenant support:

1. Jalankan migration di hosting:
   ```bash
   php artisan migrate
   ```

2. Update queries kembali ke:
   ```php
   $perusahaan = Perusahaan::where('user_id', auth()->id())->first();
   ```

3. Setiap user akan hanya bisa melihat data perusahaan mereka sendiri

## Notes
- Saat ini sistem masih single-company (satu perusahaan untuk semua user)
- Temporary fix memungkinkan sistem berjalan sambil menunggu migration
- Tidak ada data yang hilang atau rusak
