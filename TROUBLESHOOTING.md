# 🔧 Troubleshooting - Halaman Pelanggan

## Masalah Umum & Solusi

### 1. ❌ Error: Route [pelanggan.produk.index] not defined

**Penyebab:** LoginController redirect ke route yang salah.

**Solusi:** ✅ SUDAH DIPERBAIKI!
- Route sudah diubah ke `pelanggan.dashboard`
- Clear cache sudah dijalankan
- Silakan login lagi

---

### 2. ❌ Error: Table 'sessions' doesn't exist

**Penyebab:** Session driver diubah ke database tapi table belum dibuat.

**Solusi:** ✅ SUDAH DIPERBAIKI!
- Table sessions sudah dibuat
- Refresh browser Anda

---

### 3. ❌ Popup Midtrans Tidak Muncul

**Penyebab:** 
- Midtrans keys belum diisi
- Ad blocker aktif
- JavaScript error

**Solusi:**
1. Pastikan keys sudah diisi di `.env`:
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-CE6e8FsfQ40FKz5RKG67dIGp
   MIDTRANS_CLIENT_KEY=SB-Mid-client-Q7JEvrDszsD5G3kB
   ```

2. Clear cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. Disable ad blocker

4. Cek browser console (F12) untuk error

5. Coba browser lain (Chrome/Firefox)

---

### 4. ❌ Error 404 di Route Pelanggan

**Penyebab:** Route cache atau config cache

**Solusi:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

### 5. ❌ Produk Tidak Muncul di Dashboard

**Penyebab:** 
- Stok produk = 0
- Belum login sebagai pelanggan

**Solusi:**
1. Cek stok produk:
   ```bash
   php artisan tinker
   >>> DB::table('produks')->get(['id', 'nama_produk', 'stok']);
   ```

2. Update stok jika perlu:
   ```bash
   >>> DB::table('produks')->update(['stok' => 100]);
   ```

3. Pastikan login sebagai pelanggan (role: pelanggan)

---

### 6. ❌ Error "Stok Tidak Mencukupi"

**Penyebab:** Stok produk habis atau kurang

**Solusi:**
1. Cek stok produk di database
2. Kurangi qty di keranjang
3. Atau tambah stok produk

---

### 7. ❌ Redirect Loop Setelah Login

**Penyebab:** Middleware atau session issue

**Solusi:**
```bash
# Clear semua cache
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Clear browser cache
# Ctrl+Shift+Delete (Chrome/Firefox)
```

---

### 8. ❌ Error "Class Midtrans\Config not found"

**Penyebab:** Midtrans package belum terinstall

**Solusi:**
```bash
composer require midtrans/midtrans-php --ignore-platform-reqs
php artisan config:clear
```

---

### 9. ❌ Pembayaran Tidak Update Status

**Penyebab:** 
- Webhook tidak berfungsi
- Midtrans signature invalid

**Solusi:**
1. Untuk testing, update manual:
   ```bash
   php artisan tinker
   >>> $order = App\Models\Order::find(1);
   >>> $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
   ```

2. Cek webhook URL di Midtrans dashboard

3. Pastikan route webhook tidak pakai auth:
   ```php
   Route::post('/midtrans/notification', [MidtransController::class, 'notification']);
   ```

---

### 10. ❌ Error "CSRF Token Mismatch"

**Penyebab:** Session expired atau cache issue

**Solusi:**
1. Refresh halaman (F5)
2. Clear browser cache
3. Clear Laravel cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

---

### 11. ❌ Error 500 Internal Server Error

**Penyebab:** Berbagai kemungkinan

**Solusi:**
1. Cek log Laravel:
   ```
   storage/logs/laravel.log
   ```

2. Enable debug mode di `.env`:
   ```env
   APP_DEBUG=true
   ```

3. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

4. Cek permission folder:
   ```bash
   # Windows (PowerShell as Admin)
   icacls storage /grant Users:F /T
   icacls bootstrap/cache /grant Users:F /T
   ```

---

## 🔍 Cara Debug

### 1. Cek Log Laravel
```
storage/logs/laravel.log
```

### 2. Enable Debug Mode
```env
# Di .env
APP_DEBUG=true
LOG_LEVEL=debug
```

### 3. Cek Browser Console
- Tekan F12
- Tab Console
- Lihat error JavaScript

### 4. Cek Network Request
- Tekan F12
- Tab Network
- Submit form
- Lihat request/response

### 5. Test Route
```bash
php artisan route:list --name=pelanggan
```

### 6. Test Database
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::table('users')->where('role', 'pelanggan')->first();
```

---

## 🆘 Jika Masih Error

### 1. Reset Cache Semua
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

### 2. Restart Server
```bash
# Stop server (Ctrl+C)
# Start lagi
php artisan serve
```

### 3. Clear Browser Cache
- Chrome: Ctrl+Shift+Delete
- Firefox: Ctrl+Shift+Delete
- Pilih "All time"
- Clear

### 4. Coba Browser Lain
- Chrome
- Firefox
- Edge

### 5. Cek Requirements
```bash
php -v  # PHP 8.1+
composer --version
php -m | findstr pdo_mysql
php -m | findstr mbstring
```

---

## 📞 Bantuan Lebih Lanjut

Jika masih ada masalah:

1. **Cek dokumentasi:**
   - `SIAP_DIGUNAKAN.md`
   - `STATUS_HALAMAN_PELANGGAN.md`
   - `HALAMAN_PELANGGAN_100_PERSEN.md`

2. **Cek log:**
   - `storage/logs/laravel.log`
   - Browser console (F12)

3. **Test basic:**
   ```bash
   php artisan route:list
   php artisan config:show
   ```

---

**Dibuat:** 3 Desember 2025  
**Update:** Setelah fix sessions table
