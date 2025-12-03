# ✅ Fix Redirect Login Pegawai Pembelian

## 🎯 Masalah
Saat login sebagai pegawai pembelian bahan baku, user diarahkan ke `/transaksi/pembelian` (halaman admin) padahal seharusnya ke `/pegawaipembelianbahanbaku/dashboard` (halaman pegawai pembelian).

## 🔧 Solusi yang Diterapkan

### 1. **Redirect Berdasarkan Role Setelah Login**
File: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

```php
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    // Redirect berdasarkan role
    $user = Auth::user();
    
    if ($user->role === 'pegawai_pembelian') {
        return redirect()->intended(route('pegawai-pembelian.dashboard'));
    }
    
    if ($user->role === 'pelanggan') {
        return redirect()->intended(route('pelanggan.dashboard'));
    }

    // Default untuk admin/owner
    return redirect()->intended(route('dashboard', absolute: false));
}
```

### 2. **Middleware Role untuk Setiap Route Group**
File: `routes/web.php`

#### Pegawai Pembelian (hanya role: pegawai_pembelian)
```php
Route::prefix('pegawaipembelianbahanbaku')
    ->name('pegawai-pembelian.')
    ->middleware('role:pegawai_pembelian')
    ->group(function () {
        // Dashboard, Bahan Baku, Vendor, Pembelian, Retur
    });
```

#### Pelanggan (hanya role: pelanggan)
```php
Route::prefix('pelanggan')
    ->name('pelanggan.')
    ->middleware('role:pelanggan')
    ->group(function () {
        // Dashboard, Cart, Checkout, Orders
    });
```

#### Admin & Owner (hanya role: admin, owner)
```php
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('role:admin,owner')
    ->name('dashboard');

// Master Data
Route::prefix('master-data')
    ->name('master-data.')
    ->middleware('role:admin,owner')
    ->group(function () { ... });

// Transaksi
Route::prefix('transaksi')
    ->name('transaksi.')
    ->middleware('role:admin,owner')
    ->group(function () { ... });

// Laporan
Route::prefix('laporan')
    ->name('laporan.')
    ->middleware('role:admin,owner')
    ->group(function () { ... });

// Akuntansi
Route::prefix('akuntansi')
    ->name('akuntansi.')
    ->middleware('role:admin,owner')
    ->group(function () { ... });
```

### 3. **Registrasi Middleware Alias (Laravel 12)**
File: `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

**Catatan**: Laravel 12 menggunakan cara baru untuk registrasi middleware di `bootstrap/app.php`, bukan lagi di `app/Http/Kernel.php`.

## 🎯 Hasil

### Login sebagai Pegawai Pembelian
- ✅ Redirect ke: `http://127.0.0.1:8000/pegawaipembelianbahanbaku/dashboard`
- ✅ Bisa akses: Bahan Baku, Vendor, Pembelian, Retur
- ❌ Tidak bisa akses: Dashboard Admin, Master Data lain, Transaksi Admin, Laporan Admin, Akuntansi

### Login sebagai Pelanggan
- ✅ Redirect ke: `http://127.0.0.1:8000/pelanggan/dashboard`
- ✅ Bisa akses: Katalog Produk, Cart, Checkout, Orders
- ❌ Tidak bisa akses: Dashboard Admin, Master Data, Transaksi Admin, Laporan Admin

### Login sebagai Admin/Owner
- ✅ Redirect ke: `http://127.0.0.1:8000/dashboard`
- ✅ Bisa akses: Semua fitur (Dashboard, Master Data, Transaksi, Laporan, Akuntansi)
- ❌ Tidak bisa akses: Halaman Pegawai Pembelian, Halaman Pelanggan

## 🔐 Keamanan

1. **Middleware Role**: Setiap route group dilindungi dengan middleware yang memeriksa role user
2. **403 Forbidden**: Jika user mencoba akses halaman yang tidak sesuai role-nya, akan mendapat error 403
3. **Redirect Otomatis**: Setelah login, user langsung diarahkan ke dashboard yang sesuai dengan role-nya

## 📊 Flow Login

```
User Login
    ↓
Cek Role
    ↓
┌─────────────────┬──────────────────┬─────────────────┐
│ pegawai_pembelian│    pelanggan     │   admin/owner   │
│        ↓         │        ↓         │        ↓        │
│  /pegawaipembelian│  /pelanggan/    │   /dashboard    │
│  bahanbaku/      │   dashboard      │                 │
│  dashboard       │                  │                 │
└─────────────────┴──────────────────┴─────────────────┘
```

## ✅ Testing

### Test 1: Login sebagai Pegawai Pembelian
1. Login dengan user role `pegawai_pembelian`
2. Harus redirect ke `/pegawaipembelianbahanbaku/dashboard`
3. Coba akses `/dashboard` → Harus error 403
4. Coba akses `/transaksi/pembelian` → Harus error 403

### Test 2: Login sebagai Pelanggan
1. Login dengan user role `pelanggan`
2. Harus redirect ke `/pelanggan/dashboard`
3. Coba akses `/dashboard` → Harus error 403
4. Coba akses `/master-data/produk` → Harus error 403

### Test 3: Login sebagai Admin/Owner
1. Login dengan user role `admin` atau `owner`
2. Harus redirect ke `/dashboard`
3. Coba akses `/pegawaipembelianbahanbaku/dashboard` → Harus error 403
4. Coba akses `/pelanggan/dashboard` → Harus error 403

## 📝 Catatan Penting

1. **Data Terintegrasi**: 
   - Pembelian oleh pegawai pembelian → Masuk ke database pembelian
   - Admin/Owner bisa lihat semua data pembelian di laporan mereka
   - Stok bahan baku yang dibeli pegawai pembelian → Masuk ke stok admin/owner

2. **Tidak Merusak Fitur yang Sudah Ada**:
   - Semua fitur admin/owner tetap berfungsi normal
   - Semua fitur pelanggan tetap berfungsi normal
   - Hanya menambahkan proteksi akses berdasarkan role

3. **Middleware Sudah Ada**:
   - `RoleMiddleware` sudah ada sebelumnya
   - Hanya menambahkan alias `check.role` untuk konsistensi

---

**Status**: ✅ Selesai dan Siap Digunakan!


## 🔧 Troubleshooting

### Error: Target class [role] does not exist

**Penyebab**: Laravel 12 menggunakan cara baru untuk registrasi middleware di `bootstrap/app.php`

**Solusi:**
1. Pastikan middleware sudah terdaftar di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

2. Clear semua cache Laravel:
```bash
php artisan optimize:clear
```

3. Restart development server jika masih error

### Error 403 Forbidden

Jika mendapat error 403 saat mengakses halaman:
- Pastikan user memiliki role yang sesuai di database
- Cek kolom `role` di tabel `users`
- Role yang valid: `admin`, `owner`, `pelanggan`, `pegawai_pembelian`

---

**Last Updated**: December 3, 2025


### Error: Column not found: 1054 Unknown column 'tanggal_pembelian'

**Penyebab**: DashboardController menggunakan nama kolom yang salah

**Solusi**: Ubah `tanggal_pembelian` menjadi `tanggal` di semua query

File: `app/Http/Controllers/PegawaiPembelian/DashboardController.php`

```php
// ❌ SALAH
Pembelian::whereMonth('tanggal_pembelian', date('m'))

// ✅ BENAR
Pembelian::whereMonth('tanggal', date('m'))
```

---

**Last Updated**: December 3, 2025 - 15:30
