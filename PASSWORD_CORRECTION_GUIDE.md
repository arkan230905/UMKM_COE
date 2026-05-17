# Password Correction Guide

## Masalah
Password yang ditampilkan di Master Data tidak sesuai dengan password yang digunakan saat registrasi.

## Penyebab
Saat update data pelanggan yang sudah ada, command `update:pelanggan-plain-password` menggunakan password default `password123` untuk semua pelanggan, padahal password asli mereka berbeda.

## Solusi

### Untuk Memperbaiki Password Pelanggan yang Sudah Ada

Gunakan command:
```bash
php artisan set:pelanggan-password {email} {password}
```

**Contoh:**
```bash
php artisan set:pelanggan-password pelanggan123@gmail.com pelanggan123
```

**Parameter:**
- `{email}` - Email pelanggan
- `{password}` - Password asli yang digunakan saat registrasi

**Validasi:**
- Command akan memverifikasi bahwa password yang Anda masukkan sesuai dengan hash yang tersimpan
- Jika password tidak sesuai, command akan menolak dan menampilkan error
- Jika password sesuai, plain_password akan diupdate

### Untuk Verifikasi Password

Gunakan command:
```bash
php artisan check:pelanggan-password
```

Output akan menunjukkan:
- Plain password yang tersimpan
- Hash password yang tersimpan
- Status: ✓ (sesuai) atau ✗ (tidak sesuai)

## Proses Registrasi Baru

Mulai sekarang, saat pelanggan baru mendaftar:
1. Password akan di-hash dan disimpan di kolom `password`
2. Password plain text akan disimpan di kolom `plain_password`
3. Keduanya akan selalu sesuai

## Tips

1. **Jika lupa password pelanggan:**
   - Owner bisa reset password ke default `password123` dari Master Data
   - Atau gunakan command `set:pelanggan-password` jika tahu password aslinya

2. **Untuk keamanan lebih baik:**
   - Jangan tampilkan password di UI
   - Gunakan "Reset Password" flow dengan email verification
   - Biarkan pelanggan set password mereka sendiri

3. **Untuk debugging:**
   - Gunakan `php artisan check:pelanggan-password` untuk verifikasi
   - Gunakan `php artisan set:pelanggan-password` untuk koreksi

---

**Status:** ✅ Fixed
**Date:** 2026-05-17
