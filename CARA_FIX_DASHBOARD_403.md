# Cara Memperbaiki Error 403 di Dashboard

## Masalah
Ketika mengakses `/dashboard`, muncul error **403 Forbidden** karena user tidak memiliki role yang sesuai (admin atau owner).

## Solusi yang Sudah Diimplementasikan

Saya telah memperbaiki sistem dengan:

1. ✅ **Menambahkan role helper methods** di User model
2. ✅ **Membuat migration untuk backfill role** yang hilang
3. ✅ **Memperbaiki RoleMiddleware** dengan error handling yang lebih baik
4. ✅ **Membuat halaman 403 custom** yang user-friendly
5. ✅ **Memperbaiki RegisterController** redirect logic

## Langkah-Langkah untuk Mengakses Dashboard

### ⚠️ PENTING: Jalankan Migration Dulu!

Sebelum registrasi, pastikan semua migration sudah dijalankan:

```bash
php artisan migrate --path=database/migrations/2025_11_19_000000_add_role_to_users_table.php
php artisan migrate --path=database/migrations/2025_11_19_010000_add_kode_to_perusahaan_table.php
php artisan migrate --path=database/migrations/2025_11_19_010100_add_perusahaan_id_to_users_table.php
php artisan migrate --path=database/migrations/2025_12_08_150233_backfill_user_roles.php
```

### Opsi 1: Registrasi User Baru sebagai Owner (RECOMMENDED) ⭐

1. Buka browser dan akses: `http://127.0.0.1:8000/register`

2. Isi form registrasi dengan memilih role **"Owner"**:
   - Name: (nama Anda)
   - Email: (email Anda)
   - Phone: (nomor telepon)
   - Password: (minimal 8 karakter)
   - Confirm Password: (ulangi password)
   - **Role: Pilih "Owner"**
   - Company Name: (nama perusahaan)
   - Company Address: (alamat perusahaan)
   - Company Email: (email perusahaan)
   - Company Phone: (telepon perusahaan)
   - Centang "I agree to terms"

3. Klik **Register**

4. Anda akan otomatis login dan diarahkan ke dashboard

### Opsi 2: Update Role User yang Sudah Ada

Jika Anda sudah punya akun tapi role-nya 'pelanggan', jalankan seeder ini:

```bash
php artisan db:seed --class=UpdateUserRoleSeeder
```

Seeder ini akan:
- Mengupdate user pertama menjadi role 'owner'
- Menampilkan daftar semua user dan role mereka

Setelah itu, logout dan login kembali.

### Opsi 3: Buat User Owner via Tinker

```bash
php artisan tinker
```

Kemudian jalankan:

```php
$user = new App\Models\User();
$user->name = 'Admin Owner';
$user->email = 'owner@example.com';
$user->password = Hash::make('password123');
$user->role = 'owner';
$user->save();
```

Kemudian login dengan:
- Email: `owner@example.com`
- Password: `password123`

## Penjelasan Role

Sistem memiliki 4 role:

1. **owner** - Pemilik perusahaan, akses penuh ke dashboard admin
2. **admin** - Administrator, akses penuh ke dashboard admin
3. **pelanggan** - Customer, akses ke dashboard e-commerce
4. **pegawai_pembelian** - Staff pembelian, akses ke modul pembelian

## Dashboard Berdasarkan Role

- **owner/admin** → `/dashboard` (Dashboard Admin)
- **pelanggan** → `/pelanggan/dashboard` (Dashboard E-commerce)
- **pegawai_pembelian** → `/pegawaipembelianbahanbaku/dashboard` (Dashboard Pembelian)

## Error 403 yang Baru

Sekarang jika Anda mengakses halaman tanpa permission yang sesuai, Anda akan melihat:

- Halaman 403 yang user-friendly
- Informasi role Anda saat ini
- Role yang dibutuhkan untuk halaman tersebut
- Tombol untuk kembali ke dashboard yang sesuai dengan role Anda

## Testing

Untuk memastikan semuanya berfungsi:

1. Buat user dengan role 'owner' (gunakan salah satu opsi di atas)
2. Login dengan user tersebut
3. Akses `http://127.0.0.1:8000/dashboard`
4. Dashboard seharusnya muncul tanpa error 403

## Troubleshooting

### Masih Error 403 setelah login?

1. Pastikan user Anda memiliki role 'owner' atau 'admin':
   ```bash
   php artisan db:seed --class=UpdateUserRoleSeeder
   ```

2. Logout dan login kembali

3. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

### Tidak bisa registrasi?

Pastikan server Laravel berjalan:
```bash
php artisan serve
```

### Lupa password?

Gunakan tinker untuk reset:
```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'your@email.com')->first();
$user->password = Hash::make('newpassword123');
$user->save();
```

## File yang Dimodifikasi

1. `app/Models/User.php` - Ditambahkan role constants dan helper methods
2. `app/Http/Middleware/RoleMiddleware.php` - Improved error handling
3. `app/Http/Controllers/Auth/RegisterController.php` - Fixed redirect logic
4. `resources/views/errors/403.blade.php` - Custom 403 error page
5. `database/migrations/2025_12_08_150233_backfill_user_roles.php` - Backfill migration
6. `database/seeders/UpdateUserRoleSeeder.php` - Seeder untuk update role

## Kesimpulan

Error 403 terjadi karena user tidak memiliki role yang tepat. Solusinya adalah:
1. Registrasi user baru dengan role 'owner', ATAU
2. Update role user yang sudah ada menjadi 'owner' atau 'admin'

Setelah itu, Anda bisa mengakses dashboard tanpa masalah!
