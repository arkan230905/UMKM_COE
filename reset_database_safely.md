# Cara Reset Database dengan Aman

## Opsi 1: Manual Drop & Recreate (Paling Aman)

1. Buka phpMyAdmin atau MySQL client
2. Drop database `eadt_umkm`:
   ```sql
   DROP DATABASE IF EXISTS eadt_umkm;
   ```
3. Buat ulang database:
   ```sql
   CREATE DATABASE eadt_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Jalankan migrasi:
   ```bash
   php artisan migrate --seed
   ```

## Opsi 2: Skip Problematic Migration

1. Edit file `.env` dan ubah `DB_DATABASE` ke database baru:
   ```
   DB_DATABASE=eadt_umkm_new
   ```
2. Buat database baru di phpMyAdmin
3. Jalankan:
   ```bash
   php artisan migrate --seed
   ```

## Opsi 3: Force Drop Tablespace (Jika Error Tablespace)

Jika error "Tablespace exists", jalankan di MySQL:

```sql
-- Cek tablespace yang ada
SELECT * FROM INFORMATION_SCHEMA.INNODB_SYS_TABLESPACES WHERE NAME LIKE '%accounts%';

-- Drop tablespace jika ada
DROP TABLESPACE IF EXISTS `eadt_umkm`.`accounts`;

-- Atau drop semua tablespace untuk database ini
SET FOREIGN_KEY_CHECKS=0;
DROP DATABASE IF EXISTS eadt_umkm;
SET FOREIGN_KEY_CHECKS=1;
CREATE DATABASE eadt_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Untuk Test Fix Stok (Tanpa Reset Database)

Anda TIDAK perlu reset database untuk test fix stok. Cukup:

1. Login ke aplikasi web
2. Buka halaman Bahan Baku atau Bahan Pendukung
3. Edit salah satu item
4. Ubah nilai Stok (misalnya dari 36 ke 12)
5. Klik Simpan
6. Refresh halaman
7. Lihat apakah stok berubah menjadi 12

**Fix stok sudah diterapkan di:**
- `app/Models/BahanBaku.php`
- `app/Models/BahanPendukung.php`
- `app/Http/Controllers/BahanBakuController.php`
- `app/Http/Controllers/BahanPendukungController.php`

Tidak perlu migrasi atau reset database untuk fix ini bekerja!
