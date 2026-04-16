# Troubleshooting COA - User Baru Registrasi

## Masalah: COA Kosong Setelah Registrasi

### Penyebab Umum:

1. **COA Template belum di-seed**
2. **Event tidak ter-trigger**
3. **Unique constraint error**

---

## Solusi Step-by-Step

### 1. Pastikan COA Template Sudah Ada

```bash
php artisan tinker --execute="echo 'COA Template: ' . \App\Models\Coa::whereNull('company_id')->count();"
```

**Hasil yang diharapkan**: `COA Template: 81` (atau lebih)

**Jika 0**, jalankan seeder:
```bash
php artisan db:seed --class=CoaTemplateSeeder
```

---

### 2. Pastikan Migration Unique Constraint Sudah Dijalankan

```bash
php artisan migrate
```

Pastikan migration `2026_04_16_143030_fix_coas_kode_akun_unique_with_company.php` sudah dijalankan.

---

### 3. Copy COA Manual untuk User yang Sudah Terdaftar

Jika user sudah terdaftar sebelum sistem event diimplementasikan, copy COA secara manual:

```bash
php copy_coa_to_company.php
```

Script ini akan:
- Mengambil user terakhir yang registrasi
- Copy COA template ke company user tersebut
- Verifikasi jumlah COA yang berhasil di-copy

---

### 4. Verifikasi Event Terdaftar

```bash
php artisan event:list | Select-String "UserRegistered"
```

**Hasil yang diharapkan**:
```
App\Events\UserRegistered
  App\Listeners\SetupUserData
```

**Jika tidak muncul**, clear cache:
```bash
php artisan event:clear
php artisan cache:clear
composer dump-autoload
```

---

### 5. Test Registrasi User Baru

1. Buka `/register`
2. Isi form registrasi
3. Submit
4. Login
5. Buka Master Data > COA
6. **Harus ada 81+ akun COA**

---

## Error yang Mungkin Terjadi

### Error: "Duplicate entry for key 'coas_kode_akun_unique'"

**Penyebab**: Unique constraint belum di-fix

**Solusi**:
```bash
php artisan migrate --path=database/migrations/2026_04_16_143030_fix_coas_kode_akun_unique_with_company.php
```

---

### Error: "Data truncated for column 'tipe_akun'"

**Penyebab**: Tipe akun menggunakan bahasa Indonesia, tapi enum database menggunakan bahasa Inggris

**Solusi**: Sudah diperbaiki di `CoaTemplateSeeder.php`. Pastikan menggunakan:
- `Asset` (bukan `Aset`)
- `Liability` (bukan `Kewajiban`)
- `Equity` (bukan `Modal`)
- `Revenue` (bukan `Pendapatan`)
- `Expense` (bukan `Biaya`)

---

### Error: "COA template not found"

**Penyebab**: Seeder belum dijalankan

**Solusi**:
```bash
php artisan db:seed --class=CoaTemplateSeeder
```

---

## Cek Log

Jika masih ada masalah, cek log:

```bash
tail -f storage/logs/laravel.log
```

Cari log:
- `Setting up COA for new user` - Event ter-trigger
- `COA setup completed for new user` - COA berhasil di-copy
- `Failed to setup user data` - Ada error

---

## Reset Complete (Development Only)

⚠️ **PERINGATAN**: Ini akan menghapus semua data!

```bash
php artisan migrate:fresh --seed
```

Ini akan:
1. Drop semua tabel
2. Recreate tabel
3. Jalankan semua seeder (termasuk CoaTemplateSeeder)

---

## Verifikasi Manual

### Cek COA Template:
```bash
php artisan tinker
```

```php
// Cek jumlah COA template
\App\Models\Coa::whereNull('company_id')->count();

// Cek COA untuk company tertentu
\App\Models\Coa::where('company_id', 1)->count();

// Lihat sample COA template
\App\Models\Coa::whereNull('company_id')->take(5)->get(['kode_akun', 'nama_akun', 'tipe_akun']);
```

---

## Kontak Support

Jika masalah masih berlanjut, hubungi tim development dengan informasi:
1. Screenshot error
2. Log dari `storage/logs/laravel.log`
3. Hasil dari command verifikasi di atas
