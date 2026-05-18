# Solusi: Redirect dari URL Lama ke URL Baru

## Masalah

Ketika mengakses `/pelanggan/dashboard`, muncul error:
```
Missing required parameter for [Route: pelanggan.dashboard] [URI: {perusahaan_slug}/pelanggan/dashboard] [Missing parameter: perusahaan_slug].
```

Ini terjadi karena route `/pelanggan/dashboard` sudah tidak ada - sekarang harus menggunakan `/pt-arkan-trans-jaya/pelanggan/dashboard`.

## Solusi

Saya telah membuat fallback routes yang otomatis redirect dari URL lama ke URL baru.

### File yang Dibuat

1. **app/Http/Controllers/Pelanggan/FallbackController.php**
   - Controller untuk handle redirect dari URL lama ke URL baru
   - Otomatis menggunakan perusahaan pertama di database

2. **routes/pelanggan-fallback.php**
   - File routes untuk fallback (belum di-include di web.php)

### Cara Menggunakan

#### Option 1: Include di routes/web.php (Recommended)

Tambahkan baris ini di `routes/web.php` SEBELUM multi-tenant routes (sebelum baris 1894):

```php
// Include fallback routes
require base_path('routes/pelanggan-fallback.php');
```

Atau copy-paste fallback routes langsung ke web.php sebelum multi-tenant routes.

#### Option 2: Manual Update routes/web.php

Jika disk space penuh, Anda bisa manual menambahkan di routes/web.php:

```php
// Sebelum multi-tenant routes, tambahkan:
Route::prefix('pelanggan')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'dashboard']);
    Route::get('/login', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'login']);
    Route::get('/cart', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'cart']);
    Route::get('/{path?}', [\App\Http\Controllers\Pelanggan\FallbackController::class, 'catchAll'])->where('path', '.*');
});
```

### Cara Kerja

1. User mengakses `/pelanggan/dashboard`
2. Fallback route menangkap request
3. Ambil perusahaan pertama dari database
4. Generate slug dari perusahaan
5. Redirect ke `/pt-arkan-trans-jaya/pelanggan/dashboard`

### Contoh

**Sebelum:**
```
http://localhost:8000/pelanggan/dashboard
→ ERROR: Missing parameter perusahaan_slug
```

**Sesudah:**
```
http://localhost:8000/pelanggan/dashboard
→ Redirect ke: http://localhost:8000/pt-arkan-trans-jaya/pelanggan/dashboard
→ ✅ Dashboard muncul
```

### URL yang Didukung

Fallback routes mendukung:
- `/pelanggan/dashboard` → `/pt-arkan-trans-jaya/pelanggan/dashboard`
- `/pelanggan/login` → `/pt-arkan-trans-jaya/pelanggan/login`
- `/pelanggan/cart` → `/pt-arkan-trans-jaya/pelanggan/cart`
- `/pelanggan/orders` → `/pt-arkan-trans-jaya/pelanggan/orders`
- `/pelanggan/favorites` → `/pt-arkan-trans-jaya/pelanggan/favorites`
- `/pelanggan/checkout` → `/pt-arkan-trans-jaya/pelanggan/checkout`
- Dan semua URL pelanggan lainnya

### Keuntungan

✅ Backward compatible - URL lama masih berfungsi
✅ Otomatis redirect ke perusahaan pertama
✅ Tidak perlu update semua links di templates
✅ User tidak perlu tahu tentang perusahaan slug

### Catatan

- Fallback routes menggunakan perusahaan PERTAMA di database
- Jika ada multiple perusahaan, user akan selalu redirect ke perusahaan pertama
- Untuk akses perusahaan spesifik, gunakan URL dengan slug: `/pt-arkan-trans-jaya/pelanggan/dashboard`

### Next Steps

1. Tambahkan fallback routes ke `routes/web.php`
2. Test akses `/pelanggan/dashboard`
3. Verifikasi redirect berfungsi
4. Update templates untuk menggunakan helper functions (optional)

### Troubleshooting

**Masalah: Redirect tidak bekerja**
- Pastikan FallbackController.php sudah ada di `app/Http/Controllers/Pelanggan/`
- Pastikan fallback routes ditambahkan SEBELUM multi-tenant routes di web.php

**Masalah: Redirect ke perusahaan yang salah**
- Fallback routes menggunakan perusahaan pertama di database
- Untuk akses perusahaan spesifik, gunakan URL dengan slug

**Masalah: Disk space penuh**
- Jalankan: `php artisan cache:clear`
- Jalankan: `php artisan config:clear`
- Jalankan: `php artisan view:clear`
