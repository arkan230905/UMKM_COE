# Setup Guide - Setelah Clone Repository

## Error yang Terjadi
```
SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'eadt_umkm.komponen_bops' doesn't exist
```

**Penyebab:** Migration belum dijalankan dengan lengkap atau ada migration yang terlewat.

---

## ✅ Langkah Setup yang Benar

### 1. Clone Repository
```bash
git clone <repository-url>
cd <project-folder>
```

### 2. Install Dependencies
```bash
composer install
npm install  # jika ada frontend assets
```

### 3. Setup Environment
```bash
# Copy .env.example ke .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eadt_umkm
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Buat Database
```sql
CREATE DATABASE eadt_umkm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. ⚠️ PENTING: Jalankan SEMUA Migration
```bash
# Jalankan semua migration
php artisan migrate

# Jika ada error, coba fresh migration
php artisan migrate:fresh

# Atau rollback dan migrate ulang
php artisan migrate:rollback
php artisan migrate
```

### 7. Jalankan Seeder (Opsional tapi Direkomendasikan)
```bash
# Seed data awal
php artisan db:seed

# Atau seed specific seeder
php artisan db:seed --class=RequiredProductionCoasSeeder
```

### 8. Verifikasi Tabel Sudah Ada
```bash
php artisan tinker
```

Kemudian di tinker:
```php
// Check if komponen_bops table exists
Schema::hasTable('komponen_bops');  // Should return true

// Check columns
Schema::getColumnListing('komponen_bops');

// Exit tinker
exit
```

Atau langsung di MySQL:
```sql
USE eadt_umkm;
SHOW TABLES;
DESCRIBE komponen_bops;
```

### 9. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 10. Jalankan Server
```bash
php artisan serve
```

---

## 🔍 Troubleshooting

### Error: Table 'komponen_bops' doesn't exist

**Solusi 1: Jalankan Migration Spesifik**
```bash
# Check migration status
php artisan migrate:status

# Run specific migration if needed
php artisan migrate --path=/database/migrations/2025_12_09_000002_create_komponen_bops_table.php
php artisan migrate --path=/database/migrations/2026_05_05_060012_add_user_id_to_komponen_bops_table.php
```

**Solusi 2: Fresh Migration (HATI-HATI: Akan hapus semua data)**
```bash
php artisan migrate:fresh
```

**Solusi 3: Manual Create Table**
Jika migration tidak jalan, buat tabel manual:
```sql
CREATE TABLE `komponen_bops` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `kode_komponen` varchar(20) NOT NULL COMMENT 'Kode unik komponen (BOP-001)',
  `nama_komponen` varchar(100) NOT NULL COMMENT 'Nama komponen (Listrik, Gas, Penyusutan Mesin)',
  `satuan` varchar(20) NOT NULL COMMENT 'Satuan (kWh, m³, jam)',
  `tarif_per_satuan` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tarif per satuan',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `komponen_bops_kode_komponen_unique` (`kode_komponen`),
  KEY `komponen_bops_user_id_index` (`user_id`),
  CONSTRAINT `komponen_bops_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📋 Checklist Tabel yang Harus Ada

Setelah migration, pastikan tabel-tabel ini ada:

### Core Tables
- [ ] users
- [ ] roles
- [ ] role_user

### Master Data
- [ ] produks
- [ ] bahan_bakus
- [ ] bahan_pendukungs
- [ ] satuans
- [ ] coas
- [ ] vendors
- [ ] kategoris

### Production Tables
- [ ] **komponen_bops** ⚠️ (Yang error)
- [ ] proses_produksis
- [ ] bop_proses
- [ ] biaya_bahan_baku
- [ ] produksis
- [ ] produksi_details

### Accounting Tables
- [ ] jurnal_umum
- [ ] pembelians
- [ ] penjualans

### HPP Tables
- [ ] harga_pokok_produksi_biaya_bahan_baku
- [ ] harga_pokok_produksi_btkl
- [ ] harga_pokok_produksi_bop

---

## 🔧 Verifikasi Setup Berhasil

### 1. Check Database Connection
```bash
php artisan tinker
```
```php
DB::connection()->getPdo();  // Should not throw error
exit
```

### 2. Check All Tables
```bash
php artisan tinker
```
```php
$tables = DB::select('SHOW TABLES');
count($tables);  // Should return > 30 tables
exit
```

### 3. Check komponen_bops Specifically
```bash
php artisan tinker
```
```php
DB::table('komponen_bops')->count();  // Should return 0 or more
exit
```

### 4. Test Access BTKL Page
Buka browser: `http://127.0.0.1:8000/master-data/btkl/create`

Jika tidak error, setup berhasil! ✅

---

## 📝 Catatan Penting

### Untuk Development Team:

1. **Selalu jalankan migration setelah pull/clone**
   ```bash
   git pull
   php artisan migrate
   ```

2. **Jika ada migration baru, inform team**
   - Buat announcement di group
   - Update README.md
   - Dokumentasikan di CHANGELOG.md

3. **Gunakan migration:status untuk check**
   ```bash
   php artisan migrate:status
   ```
   Pastikan semua migration status "Ran"

4. **Jangan commit .env file**
   - .env sudah di .gitignore
   - Setiap developer setup .env sendiri

5. **Backup database sebelum migrate:fresh**
   ```bash
   mysqldump -u root -p eadt_umkm > backup.sql
   ```

---

## 🚀 Quick Fix untuk Error Saat Ini

Untuk teman Anda yang sedang error, jalankan ini:

```bash
# 1. Check migration status
php artisan migrate:status

# 2. Run pending migrations
php artisan migrate

# 3. If still error, run specific migration
php artisan migrate --path=/database/migrations/2025_12_09_000002_create_komponen_bops_table.php

# 4. Then run the user_id migration
php artisan migrate --path=/database/migrations/2026_05_05_060012_add_user_id_to_komponen_bops_table.php

# 5. Clear cache
php artisan config:clear
php artisan cache:clear

# 6. Refresh page
```

Setelah itu, error seharusnya hilang! ✅

---

## 📞 Jika Masih Error

1. Check error log: `storage/logs/laravel.log`
2. Check migration status: `php artisan migrate:status`
3. Check database: `SHOW TABLES LIKE 'komponen_bops';`
4. Share error message lengkap untuk debugging

---

**Last Updated:** 2026-05-06
**Status:** Ready for deployment
