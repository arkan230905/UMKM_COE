# Summary Perbaikan Migration untuk Hosting

## 🔧 Masalah yang Diperbaiki

### 1. Kolom `tarif_per_jam` di Tabel `jabatans`
**Masalah:**
- Beberapa migration mencoba menambahkan/rename kolom `tarif_per_jam` di tabel `jabatans`
- Aplikasi menggunakan tarif per PRODUK, bukan per jam
- Menyebabkan error: `Column 'tarif_per_jam' not found`

**Perbaikan:**
- ✅ Dihapus `tarif_per_jam` dari migration `2026_04_20_030001_add_gaji_pokok_to_jabatans_table.php`
- ✅ Dihapus migration `2026_05_12_rename_tarif_jam_to_produk.php` (migration yang salah)
- ✅ Diperbaiki migration `2026_04_01_130000_refactor_pegawai_kategori_system.php`
- ✅ Diperbaiki `JabatanController.php` untuk tidak menggunakan `tarif_per_jam`

### 2. Struktur Tabel yang Benar

#### Tabel `jabatans`
```sql
CREATE TABLE `jabatans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `kategori` enum('btkl','btktl') NOT NULL,
  `kode_jabatan` varchar(10) DEFAULT NULL,
  `gaji_pokok` decimal(15,2) DEFAULT '0.00',
  `tarif` decimal(15,2) DEFAULT '0.00',  -- Tarif per PRODUK
  `tunjangan` decimal(15,2) DEFAULT '0.00',
  `tunjangan_transport` decimal(15,2) DEFAULT '0.00',
  `tunjangan_konsumsi` decimal(15,2) DEFAULT '0.00',
  `asuransi` decimal(15,2) DEFAULT '0.00',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

**PENTING:** Tidak ada kolom `tarif_per_jam` atau `tarif_produk`!

## 📝 File yang Diubah

### Migration Files
1. `database/migrations/2026_04_20_030001_add_gaji_pokok_to_jabatans_table.php`
   - Dihapus: `$table->decimal('tarif_per_jam', 15, 2)`
   - Tetap ada: `gaji_pokok`, `kode_jabatan`

2. `database/migrations/2026_04_01_130000_refactor_pegawai_kategori_system.php`
   - Dihapus: `$table->renameColumn('tarif', 'tarif_per_jam')`
   - Kolom `tarif` tetap dipertahankan

3. `database/migrations/2026_05_12_rename_tarif_jam_to_produk.php`
   - **DIHAPUS** (migration yang salah)

### Controller Files
1. `app/Http/Controllers/JabatanController.php`
   - Method `store()`: Dihapus `$data['tarif_per_jam'] = $data['tarif'];`
   - Method `update()`: Dihapus `$data['tarif_per_jam'] = $data['tarif'];`
   - Method `getByKategori()`: Diubah dari `'tarif_per_jam as tarif'` menjadi `'tarif'`

### View Files
1. `resources/views/master-data/coa/index.blade.php`
   - Tombol "Tambah COA" diperkecil dengan `btn-sm`
   - Dropdown periode diperkecil dengan `form-select-sm`

## ✅ Verifikasi

### Test yang Sudah Dilakukan
1. ✅ Verifikasi semua migration (484 files)
2. ✅ Tidak ada masalah dengan `tarif_per_jam` di tabel `jabatans`
3. ✅ COA seeder berfungsi (51 akun)
4. ✅ Satuan seeder berfungsi (16 satuan)
5. ✅ Tambah jabatan tidak error

### Struktur Database yang Benar
```
jabatans:
  - id
  - nama
  - kategori (btkl/btktl)
  - kode_jabatan
  - gaji_pokok
  - tarif ← PENTING: Ini untuk tarif per PRODUK
  - tunjangan
  - tunjangan_transport
  - tunjangan_konsumsi
  - asuransi
  - user_id
  - timestamps

coas: 51 akun per user
satuans: 16 satuan per user
```

## 🚀 Cara Deploy ke Hosting

### 1. Upload Files
```bash
# Upload semua file ke server
# Pastikan .env sudah dikonfigurasi
```

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Run Migration
```bash
php artisan migrate --force
```

### 4. Setup Data Awal
```bash
php setup_hosting.php
```

Script ini akan:
- ✅ Check database connection
- ✅ Create admin user jika belum ada
- ✅ Create 51 COA untuk setiap user
- ✅ Create 16 Satuan untuk setiap user
- ✅ Verify setup

### 5. Optimize untuk Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 📋 Checklist Hosting

- [ ] Upload files ke server
- [ ] Configure .env untuk production
- [ ] Install composer dependencies
- [ ] Run migration: `php artisan migrate --force`
- [ ] Run setup: `php setup_hosting.php`
- [ ] Optimize: cache config, routes, views
- [ ] Set permissions untuk storage dan cache
- [ ] Test login (admin@example.com / password)
- [ ] Test tambah jabatan (tidak ada error tarif_per_jam)
- [ ] Test fitur utama (COA, Satuan, Bahan Baku, dll)

## 🎯 Status

**✅ SIAP UNTUK HOSTING**

Semua migration sudah diperbaiki dan diverifikasi. Tidak ada lagi masalah dengan `tarif_per_jam` di tabel `jabatans`.

## 📞 Support

Jika ada masalah saat hosting:
1. Check log: `storage/logs/laravel.log`
2. Check database structure: `DESCRIBE jabatans;`
3. Verify COA count: `SELECT COUNT(*) FROM coas WHERE user_id = 1;`
4. Verify Satuan count: `SELECT COUNT(*) FROM satuans WHERE user_id = 1;`

---

**Tanggal:** 13 Mei 2026
**Status:** ✅ VERIFIED & READY
