# Fix: Password Pelanggan Tidak Sesuai (Hash vs Plain Text)

## Masalah
Password yang ditampilkan di Master Data > Pelanggan adalah hash (encrypted), bukan password asli yang digunakan pelanggan untuk login.

Contoh:
- Password asli: `password123`
- Yang ditampilkan: `$2y$12$7zYz17zLyRkUTgcSoFfzd...` (hash)

## Root Cause
Model User hanya menyimpan password yang di-hash di kolom `password`. Tidak ada penyimpanan plain text password untuk keperluan display.

## Solusi
Gunakan kolom `plain_password` yang sudah ada di tabel `users` untuk menyimpan password asli.

## Perubahan yang Dilakukan

### 1. Update `LoginController` (Registrasi Pelanggan)
```php
$user = User::create([
    'password' => bcrypt($validated['password']),
    'plain_password' => $validated['password'], // ← Tambah ini
    // ... field lainnya
]);
```

### 2. Update Model `User`
Tambahkan `plain_password` ke dalam `$fillable`:
```php
protected $fillable = [
    // ... field lainnya
    'plain_password', // ← Tambah ini
];
```

### 3. Update View Master Data
Ganti `$pelanggan->password` dengan `$pelanggan->plain_password`:
```blade
@if($pelanggan->plain_password)
    <span class="password-text">{{ $pelanggan->plain_password }}</span>
@endif
```

### 4. Update Controller Methods
- `update()` - Update `plain_password` saat password diubah
- `resetPassword()` - Update `plain_password` saat password di-reset

### 5. Update Existing Data
Jalankan command untuk update pelanggan yang sudah ada:
```bash
php artisan update:pelanggan-plain-password
```

## Hasil
✅ Password yang ditampilkan sekarang adalah plain text yang sesuai
✅ Owner bisa melihat password asli pelanggan
✅ Owner bisa copy password dengan mudah
✅ Owner bisa hide/show password dengan tombol eye icon

## Fitur Password Display
- **Lihat Password**: Klik tombol eye icon untuk melihat password
- **Sembunyikan Password**: Klik lagi untuk menyembunyikan
- **Copy Password**: Klik tombol copy untuk copy ke clipboard
- **Reset Password**: Owner bisa reset password ke default `password123`

## Security Note
⚠️ Menyimpan plain text password adalah praktik yang tidak ideal untuk security.
Alternatif yang lebih aman:
1. Generate random password saat registrasi
2. Kirim password via email
3. Tidak pernah menampilkan password yang sudah di-set
4. Gunakan "Reset Password" flow dengan email verification

Namun untuk keperluan admin/owner yang perlu melihat password pelanggan, solusi ini sudah cukup.

---

**Status:** ✅ Fixed
**Date:** 2026-05-17
