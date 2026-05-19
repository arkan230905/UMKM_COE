# Panduan Deploy ke Production Server

## ✅ Yang Sudah Dilakukan di Local
- Semua migration files sudah dibuat dan ditest
- Sudah di-commit dan di-push ke branch `nayla`
- Total 6 migration files yang perlu dijalankan di production

## 📋 Langkah Deploy ke Server Production

### Opsi 1: Via SSH (Paling Mudah & Recommended)

```bash
# 1. SSH ke server
ssh username@jobcost.eadtmanufaktur.com

# 2. Masuk ke folder aplikasi
cd /path/to/laravel/app
# Biasanya: cd public_html atau cd htdocs

# 3. Backup database dulu (PENTING!)
php artisan db:backup
# atau manual via phpMyAdmin: Export database

# 4. Pull perubahan terbaru dari GitHub
git fetch origin
git pull origin nayla

# 5. Cek migration files sudah ada
ls -la database/migrations/2026_05_19*.php
# Harus ada 6 files

# 6. Jalankan migration
php artisan migrate --force

# 7. Verifikasi hasil
php artisan tinker --execute="echo json_encode(Schema::getColumnListing('pembelians'));"
# Pastikan ada: bank_id, subtotal, total_harga, status, dll

# 8. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 9. Test di browser
# Buka: http://jobcost.eadtmanufaktur.com/transaksi/pembelian/create
```

### Opsi 2: Via cPanel File Manager + Terminal

```bash
# 1. Login ke cPanel
# 2. Buka File Manager
# 3. Masuk ke folder aplikasi
# 4. Klik "Git Pull" atau manual download dari GitHub
# 5. Buka Terminal di cPanel
# 6. Jalankan command dari step 5-9 di Opsi 1
```

### Opsi 3: Manual Upload + phpMyAdmin (Jika tidak ada SSH)

```bash
# 1. Download 6 migration files dari GitHub:
#    - 2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances.php
#    - 2026_05_19_000002_add_bank_id_to_pembelians_table.php
#    - 2026_05_19_000003_add_missing_financial_columns_to_pembelians_table.php
#    - 2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians.php
#    - 2026_05_19_000005_force_add_financial_columns_to_pembelians.php
#    - 2026_05_19_000006_add_missing_columns_to_stock_movements.php

# 2. Upload ke folder database/migrations/ via FTP

# 3. Login phpMyAdmin

# 4. Jalankan SQL ini satu per satu:

-- Cek migration yang sudah jalan
SELECT * FROM migrations ORDER BY id DESC LIMIT 10;

-- Insert migration records (ganti batch number sesuai yang terakhir + 1)
INSERT INTO migrations (migration, batch) VALUES
('2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances', 7),
('2026_05_19_000002_add_bank_id_to_pembelians_table', 7),
('2026_05_19_000003_add_missing_financial_columns_to_pembelians_table', 7),
('2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians', 7),
('2026_05_19_000005_force_add_financial_columns_to_pembelians', 7),
('2026_05_19_000006_add_missing_columns_to_stock_movements', 7);

# 5. Jalankan SQL dari file manual_migration.sql
```

## 🔍 Verifikasi Setelah Deploy

### Cek via phpMyAdmin:
1. Buka tabel `pembelians`
2. Tab "Structure"
3. Pastikan ada kolom:
   - ✅ bank_id
   - ✅ subtotal
   - ✅ biaya_kirim
   - ✅ ppn_persen
   - ✅ ppn_nominal
   - ✅ total_harga
   - ✅ terbayar
   - ✅ sisa_pembayaran
   - ✅ status
   - ✅ keterangan

4. Buka tabel `stock_movements`
5. Pastikan ada kolom:
   - ✅ keterangan
   - ✅ manual_conversion_data

6. Buka tabel `coa_period_balances`
7. Pastikan ada kolom:
   - ✅ period_id (bukan coa_period_id)

### Test via Browser:
1. Buka: http://jobcost.eadtmanufaktur.com/transaksi/pembelian/create
2. Seharusnya tidak ada error lagi
3. Coba buat pembelian baru
4. Pastikan bisa save tanpa error

## 🚨 Troubleshooting

### Error: "Migration already ran"
```bash
# Cek status migration
php artisan migrate:status

# Jika sudah ada, skip saja
```

### Error: "Column already exists"
```bash
# Ini normal, migration sudah jalan sebelumnya
# Cek manual di phpMyAdmin apakah kolom sudah ada
```

### Error: "Permission denied"
```bash
# Fix permission
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
```

## 📝 Catatan Penting

1. **BACKUP DATABASE** sebelum menjalankan migration!
2. Jika ada error, jangan panic - database bisa di-restore dari backup
3. Migration ini sudah ditest di local dan aman
4. Setelah deploy, hapus file `deploy_migrations.php` jika ada di server
5. Jangan lupa clear cache setelah migration

## 📞 Support

Jika ada masalah:
1. Screenshot error message lengkap
2. Cek log: `storage/logs/laravel.log`
3. Kirim ke developer untuk bantuan

---

**Status:** ✅ Ready to deploy
**Branch:** nayla
**Commit:** 39956cb3
**Date:** 19 Mei 2026
