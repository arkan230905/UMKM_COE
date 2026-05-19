# 🚀 Cara Deploy ke Production Server

## ⚠️ PENTING: Backup Database Dulu!

Sebelum deploy, **WAJIB backup database** via phpMyAdmin:
1. Login phpMyAdmin
2. Pilih database `eadt_umkm`
3. Tab "Export"
4. Klik "Go"
5. Save file backup

---

## 📋 Pilihan Metode Deploy

### ✅ Metode 1: Via SSH (Paling Mudah - RECOMMENDED)

#### Step 1: Connect ke Server
```bash
ssh username@jobcost.eadtmanufaktur.com
```
Ganti `username` dengan username SSH Anda.

#### Step 2: Masuk ke Folder Laravel
```bash
cd /home/username/public_html
# atau
cd /home/username/domains/jobcost.eadtmanufaktur.com/public_html
```

Cek apakah sudah benar:
```bash
ls -la artisan
```
Harus ada file `artisan`.

#### Step 3: Pull Code Terbaru
```bash
git fetch origin
git pull origin nayla
```

#### Step 4: Cek Migration Files
```bash
ls -la database/migrations/2026_05_19*.php
```
Harus ada 8 files.

#### Step 5: Run Migration
```bash
php artisan migrate --force
```

Tunggu sampai selesai. Output harus menunjukkan:
```
INFO  Running migrations.

2026_05_19_000001_rename_coa_period_id... DONE
2026_05_19_000002_add_bank_id... DONE
...
```

#### Step 6: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### Step 7: Verifikasi
```bash
php artisan tinker --execute="echo json_encode(Schema::getColumnListing('pembelians'));"
```

Pastikan output ada: `bank_id`, `subtotal`, `total_harga`, `status`

#### Step 8: Test
Buka browser: `http://jobcost.eadtmanufaktur.com/transaksi/pembelian/create`

---

### ✅ Metode 2: Via cPanel Terminal

#### Step 1: Login cPanel
Buka: `https://jobcost.eadtmanufaktur.com:2083`

#### Step 2: Buka Terminal
Cari menu "Terminal" di cPanel.

#### Step 3-8: Sama seperti Metode 1
Jalankan command yang sama seperti di Metode 1.

---

### ✅ Metode 3: Via Script Otomatis

#### Jika Pakai Linux/Mac:
```bash
# Upload file deploy_to_production.sh ke server
# Lalu jalankan:
chmod +x deploy_to_production.sh
./deploy_to_production.sh
```

#### Jika Pakai Windows PowerShell:
```powershell
# Di server yang support PowerShell:
.\deploy_to_production.ps1
```

---

### ✅ Metode 4: Manual via phpMyAdmin (Jika Tidak Ada SSH)

#### Step 1: Upload Migration Files
Via FTP/FileZilla, upload 8 files migration ke folder:
```
/public_html/database/migrations/
```

Files yang harus diupload:
- `2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances.php`
- `2026_05_19_000002_add_bank_id_to_pembelians_table.php`
- `2026_05_19_000003_add_missing_financial_columns_to_pembelians_table.php`
- `2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians.php`
- `2026_05_19_000005_force_add_financial_columns_to_pembelians.php`
- `2026_05_19_000006_add_missing_columns_to_stock_movements.php`
- `2026_05_19_000007_fix_coa_pelunasan_foreign_key_in_pelunasan_utangs.php`
- `2026_05_19_000008_fix_all_accounts_foreign_keys_to_coas.php`

#### Step 2: Login phpMyAdmin
Buka phpMyAdmin dari cPanel.

#### Step 3: Pilih Database
Klik database `eadt_umkm` di sidebar kiri.

#### Step 4: Buka Tab SQL
Klik tab "SQL" di bagian atas.

#### Step 5: Copy Paste SQL
Buka file `manual_migration.sql` dari repository, copy semua isinya, paste ke textarea SQL.

#### Step 6: Execute
Klik tombol "Go" di kanan bawah.

#### Step 7: Cek Hasil
Tunggu sampai selesai. Jika sukses, akan ada pesan "Query OK".

#### Step 8: Verifikasi
Klik tabel `pembelians` di sidebar, lalu tab "Structure".
Pastikan ada kolom: `bank_id`, `subtotal`, `total_harga`, `status`, dll.

Klik tabel `pelunasan_utangs`, tab "Structure", tab "Relation view".
Pastikan foreign key `coa_pelunasan_id` dan `akun_kas_id` merujuk ke tabel `coas` (bukan `accounts`).

#### Step 9: Update Migration Records
Jalankan SQL ini untuk update migration records:
```sql
-- Cek batch terakhir
SELECT MAX(batch) as last_batch FROM migrations;

-- Ganti angka 7 dengan last_batch + 1
INSERT INTO migrations (migration, batch) VALUES
('2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances', 7),
('2026_05_19_000002_add_bank_id_to_pembelians_table', 7),
('2026_05_19_000003_add_missing_financial_columns_to_pembelians_table', 7),
('2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians', 7),
('2026_05_19_000005_force_add_financial_columns_to_pembelians', 7),
('2026_05_19_000006_add_missing_columns_to_stock_movements', 7),
('2026_05_19_000007_fix_coa_pelunasan_foreign_key_in_pelunasan_utangs', 7),
('2026_05_19_000008_fix_all_accounts_foreign_keys_to_coas', 7);
```

#### Step 10: Clear Cache via File Manager
Hapus folder-folder ini via File Manager cPanel:
- `bootstrap/cache/config.php`
- `bootstrap/cache/routes-v7.php`
- `bootstrap/cache/services.php`

---

## 🔍 Troubleshooting

### Error: "Migration already ran"
```bash
# Cek status
php artisan migrate:status

# Jika sudah ada, berarti migration sudah jalan
# Cek manual di phpMyAdmin apakah kolom sudah ada
```

### Error: "Column already exists"
Ini normal, artinya migration sudah pernah jalan. Skip saja.

### Error: "Permission denied"
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Error: "Foreign key constraint fails"
Berarti migration belum jalan. Pastikan:
1. File migration sudah ada di server
2. Migration sudah di-run dengan `php artisan migrate --force`
3. Foreign key sudah berubah dari `accounts` ke `coas`

Cek foreign key:
```sql
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'eadt_umkm'
    AND TABLE_NAME = 'pelunasan_utangs'
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```

Harus menunjukkan `REFERENCED_TABLE_NAME = coas` (bukan `accounts`).

---

## ✅ Checklist Setelah Deploy

- [ ] Backup database sudah dibuat
- [ ] Code sudah di-pull dari GitHub
- [ ] Migration sudah jalan tanpa error
- [ ] Cache sudah di-clear
- [ ] Tabel `pembelians` punya kolom `bank_id`, `subtotal`, `total_harga`, `status`
- [ ] Tabel `stock_movements` punya kolom `keterangan`, `manual_conversion_data`
- [ ] Tabel `coa_period_balances` punya kolom `period_id` (bukan `coa_period_id`)
- [ ] Foreign key `pelunasan_utangs.coa_pelunasan_id` → `coas` (bukan `accounts`)
- [ ] Foreign key `pelunasan_utangs.akun_kas_id` → `coas` (bukan `accounts`)
- [ ] Foreign key `pembelians.bank_id` → `coas` (bukan `accounts`)
- [ ] Halaman pembelian bisa dibuka tanpa error
- [ ] Bisa create pembelian baru tanpa error
- [ ] Bisa create pelunasan utang tanpa error

---

## 📞 Jika Masih Error

1. Screenshot error message lengkap
2. Cek file log: `storage/logs/laravel.log`
3. Cek struktur database via phpMyAdmin
4. Kirim screenshot ke developer

---

**Status:** ✅ Ready to deploy
**Branch:** nayla  
**Commit:** 5b178d82
**Date:** 19 Mei 2026
