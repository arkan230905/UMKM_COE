# 🚀 Production Deployment Checklist - Complete Guide

**Tanggal**: 22 Juni 2026  
**Status**: Ready to Deploy  
**Estimasi Waktu**: 15-20 menit

---

## 📋 Overview Perubahan

Ada beberapa perubahan yang sudah berhasil di localhost tapi belum di production:

1. ✅ **Vendor Constraint Fix** - Membolehkan vendor nama sama dengan kategori berbeda
2. ✅ **Konversi Sub Satuan Redesign** - Accordion collapse (default tertutup)
3. ✅ **Laporan Pembelian PDF** - Design baru dengan summary cards
4. ✅ **Biaya Kirim COA** - Menggunakan COA 558 (Beban Transport Pembelian)
5. ✅ **Number Formatting** - Hilangkan .00 untuk angka bulat
6. ✅ **UI Improvements** - Removed duplicate columns and fields
7. ✅ **Vendor Phone Validation** - Only numeric characters allowed
8. ✅ **Universal Required Field Validation** - Alert for empty required fields (*)
9. ✅ **Hide Status Retur Column** - Hidden from transaksi pembelian table
10. ✅ **Hide Delete Action** - Hidden from transaksi pembelian table

---

## 🎯 Pre-Deployment Checklist

### 1. Backup Database
```bash
# WAJIB dilakukan sebelum deployment apapun!
cd /path/to/production
mysqldump -u [username] -p [database_name] > backup_pre_deployment_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh backup_pre_deployment_*.sql
```

### 2. Backup Files
```bash
# Backup critical files
tar -czf backup_views_$(date +%Y%m%d_%H%M%S).tar.gz resources/views/
tar -czf backup_migrations_$(date +%Y%m%d_%H%M%S).tar.gz database/migrations/
```

### 3. Check Git Status
```bash
# Di localhost, pastikan semua changes sudah committed
git status
git log --oneline -10

# Pastikan branch main/master sudah update
git push origin main
```

---

## 📦 File Deployment

### Files yang HARUS di-upload ke production:

#### 1. Migration Files
```
database/migrations/2026_06_15_000000_force_remove_old_vendor_constraint.php
```

#### 2. Controller Files
```
app/Http/Controllers/VendorController.php
app/Http/Controllers/PembelianController.php
```

#### 3. Service Files
```
app/Services/PembelianJournalService.php
```

#### 4. Observer Files
```
app/Observers/BahanBakuObserver.php
app/Observers/BahanPendukungObserver.php
```

#### 5. View Files (PENTING!)
```
resources/views/transaksi/pembelian/create.blade.php
resources/views/transaksi/pembelian/show.blade.php
resources/views/transaksi/pembelian/index.blade.php
resources/views/transaksi/pembelian/partials/pembelian-content.blade.php
resources/views/laporan/pembelian/index.blade.php
resources/views/laporan/pembelian/export.blade.php
resources/views/master-data/kategori-bahan-pendukung/index.blade.php
resources/views/master-data/vendor/create.blade.php
resources/views/master-data/vendor/edit.blade.php
resources/views/master-data/bahan-baku/create.blade.php
resources/views/master-data/bahan-pendukung/create.blade.php
resources/views/akuntansi/laporan_posisi_keuangan.blade.php
```

#### 6. Helper Files
```
app/Helpers/helpers.php
```

#### 7. Service Files
```
app/Services/NeracaService.php
```

---

## 🚀 Deployment Steps

### Step 1: Upload Files to Production

#### Via FTP/SFTP:
```
1. Connect to production server
2. Upload semua files di list "Files yang HARUS di-upload"
3. Pastikan file permissions correct (644 untuk files, 755 untuk folders)
4. Verify uploaded files timestamp
```

#### Via Git (Recommended):
```bash
# Di production server
cd /path/to/production

# Backup current state
git stash

# Pull latest changes
git pull origin main

# Check what changed
git log --oneline -5
git diff HEAD~1 HEAD --name-only
```

### Step 2: Put Site in Maintenance Mode
```bash
cd /path/to/production
php artisan down --message="Sistem sedang maintenance, akan kembali dalam 10 menit"
```

### Step 3: Run Database Migration
```bash
# Run migration for vendor constraint fix
php artisan migrate --force

# Expected output:
# Running migrations.
# Checking vendors table constraints...
# Found OLD constraint: vendors_user_id_nama_vendor_unique
# Dropping OLD constraint: vendors_user_id_nama_vendor_unique
# ✓ Successfully dropped old constraint
# Adding NEW constraint: (user_id, nama_vendor, kategori)
# ✓ Successfully added new constraint
```

### Step 4: Clear ALL Caches
```bash
# Clear configuration cache
php artisan config:clear

# Clear application cache
php artisan cache:clear

# Clear view cache (PENTING untuk blade files!)
php artisan view:clear

# Clear route cache
php artisan route:clear

# Clear compiled files
php artisan optimize:clear

# Delete compiled views manually
rm -rf storage/framework/views/*

# Rebuild optimizations
php artisan optimize
```

### Step 5: Fix File Permissions
```bash
# Ensure proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs
chmod -R 777 storage/framework/views
chown -R www-data:www-data storage bootstrap/cache
```

### Step 6: Restart Services
```bash
# Restart PHP-FPM (choose based on your PHP version)
sudo systemctl restart php8.2-fpm
# OR
sudo systemctl restart php8.1-fpm
# OR
sudo systemctl restart php-fpm

# Restart Apache
sudo systemctl restart apache2
# OR
sudo systemctl restart httpd

# Restart Nginx (if using)
sudo systemctl restart nginx

# Check service status
sudo systemctl status php8.2-fpm
sudo systemctl status apache2
```

### Step 7: Bring Site Back Online
```bash
php artisan up
```

---

## ✅ Verification Steps

### 1. Verify Database Constraint

```bash
# Via artisan tinker
php artisan tinker
```

```php
// Check vendors constraint
DB::select("SHOW INDEX FROM vendors WHERE Key_name LIKE '%unique%'");

// Should show: vendors_user_id_nama_vendor_kategori_unique
// With columns: user_id, nama_vendor, kategori
exit
```

### 2. Test Vendor Creation

**Test Case 1: Same name, different kategori (Should WORK)**
```
1. Login ke aplikasi
2. Pergi ke Master Data > Vendor
3. Buat vendor: "Sukbir Mart" | Kategori: Bahan Baku ✅
4. Buat vendor: "Sukbir Mart" | Kategori: Bahan Pendukung ✅
5. Harus berhasil tanpa error!
```

**Test Case 2: Same name, same kategori (Should FAIL)**
```
1. Buat vendor: "Sukbir Mart" | Kategori: Bahan Baku
2. Buat vendor lagi: "Sukbir Mart" | Kategori: Bahan Baku ❌
3. Harus muncul error validation!
```

### 3. Test Pembelian Form

**Test Konversi Sub Satuan:**
```
1. Pergi ke Transaksi > Tambah Pembelian
2. Check "Konversi Sub Satuan" section:
   - ✅ Harus tertutup (collapsed) by default
   - ✅ Klik untuk expand/collapse
   - ✅ Hanya show info, bukan input fields
   - ✅ Format: "1 Kilogram = 1000 Gram", "Harga per Gram = Rp 52"
```

**Test Number Formatting:**
```
1. Input pembelian dengan jumlah bulat (10 Kg)
2. Check detail:
   - ✅ Harus tampil "10" bukan "10.00"
   - ✅ Angka desimal tetap tampil (10.5 tetap "10.5")
```

### 4. Test Laporan PDF Export

```
1. Pergi ke Laporan > Laporan Pembelian
2. Klik "Export PDF"
3. Verify:
   - ✅ Header dengan title "LAPORAN PEMBELIAN"
   - ✅ Summary cards (Total Transaksi, Grand Total, Terbayar, Sisa Utang)
   - ✅ Table dengan cream background
   - ✅ Footer dengan info cetak
   - ✅ Icons tampil dengan baik (no emoji issues)
```

### 5. Test Biaya Kirim COA

```
1. Buat transaksi pembelian dengan biaya kirim
2. Check jurnal (via database or jurnal umum):
   - ✅ Biaya kirim menggunakan COA 558 (Beban Transport Pembelian)
   - ✅ Bukan COA 5111
```

### 6. Check Browser Hard Refresh

```
1. Open aplikasi di browser
2. Tekan Ctrl + Shift + R (hard refresh)
3. Clear browser cache if needed
4. Verify tampilan sudah update
```

### 7. Test Vendor Phone Validation

```
1. Pergi ke Master Data > Vendor > Tambah
2. Test No. Telepon field:
   - ✅ Input "123abc" - auto remove "abc", alert muncul
   - ✅ Paste "08123456789xyz" - only numbers extracted
   - ✅ Submit dengan non-numeric - validation error
```

### 8. Test Required Field Validation

```
1. Pergi ke Master Data > Vendor > Tambah
2. Klik Simpan tanpa isi form:
   - ✅ Alert muncul dengan list field yang kosong
   - ✅ Focus ke field pertama yang kosong
   - ✅ Field kosong ditandai dengan red border
3. Test di form lain (Bahan Baku, Bahan Pendukung):
   - ✅ Validation sama untuk semua required fields
```

### 9. Test COA 310 Hidden in Laporan Posisi Keuangan

```
1. Pergi ke Laporan > Laporan Posisi Keuangan
2. Check bagian Modal Usaha:
   - ✅ COA 310 (Modal Usaha) tidak muncul
   - ✅ COA 311 dan lainnya masih muncul normal
```

### 10. Test Retur Pembelian Hidden

```
1. Pergi ke Transaksi > Pembelian
2. Check UI:
   - ✅ Tab "Retur Pembelian" di index - HIDDEN
   - ✅ Button "Retur" di kolom aksi table - HIDDEN
   - ✅ Kolom "Status Retur" di table - HIDDEN
   - ✅ Button "Retur" di detail page - HIDDEN
3. Pergi ke Laporan > Laporan Pembelian:
   - ✅ Tab "Laporan Retur Pembelian" - HIDDEN
```

### 11. Test Delete Action Hidden

```
1. Pergi ke Transaksi > Pembelian
2. Check kolom aksi:
   - ✅ Button "Hapus" tidak muncul
   - ✅ Button lain (Detail, Edit, Jurnal, Cetak) masih tampil
   - ✅ Layout grid tetap rapi
```

---

## 🔍 Troubleshooting

### Issue 1: Tampilan masih lama setelah deployment

**Solution:**
```bash
# Clear all caches again
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# Delete compiled views manually
rm -rf storage/framework/views/*

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Clear browser cache
Ctrl + Shift + Delete (di browser)
```

### Issue 2: Migration error "Can't DROP INDEX"

**Solution:**
```bash
# Check actual constraint name
mysql -u [user] -p [database]
SHOW INDEX FROM vendors WHERE Non_unique = 0;

# Use exact name shown
ALTER TABLE vendors DROP INDEX [exact_name_shown];
```

### Issue 3: Permission denied on storage

**Solution:**
```bash
chmod -R 777 storage/
chown -R www-data:www-data storage/
```

### Issue 4: 500 Error after deployment

**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Common fixes
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

### Issue 5: PDF Export tidak muncul summary cards

**Solution:**
```bash
# Verify file uploaded
cat resources/views/laporan/pembelian/export.blade.php | head -20

# Clear view cache
php artisan view:clear
rm -rf storage/framework/views/*
```

---

## 📝 Post-Deployment Checklist

- [ ] ✅ Database backup created
- [ ] ✅ Files backup created
- [ ] ✅ All files uploaded to production
- [ ] ✅ Migration ran successfully
- [ ] ✅ All caches cleared
- [ ] ✅ Services restarted
- [ ] ✅ Vendor constraint verified in database
- [ ] ✅ Test vendor creation (same name, different kategori) - WORKS
- [ ] ✅ Test vendor creation (same name, same kategori) - FAILS correctly
- [ ] ✅ Vendor phone validation works (only numeric)
- [ ] ✅ Required field validation works (alert with list)
- [ ] ✅ Pembelian form shows collapsed accordion
- [ ] ✅ Number formatting correct (no .00 for whole numbers)
- [ ] ✅ PDF export shows new design
- [ ] ✅ Biaya kirim uses COA 558
- [ ] ✅ COA 310 hidden in Laporan Posisi Keuangan
- [ ] ✅ Retur Pembelian features hidden from UI (tabs, buttons, status column)
- [ ] ✅ Delete action hidden from transaksi pembelian
- [ ] ✅ No errors in logs
- [ ] ✅ Site is live and working

---

## 📞 Rollback Plan (If Something Goes Wrong)

### Full Rollback:
```bash
# 1. Restore database
mysql -u [user] -p [database] < backup_pre_deployment_[timestamp].sql

# 2. Restore files
tar -xzf backup_views_[timestamp].tar.gz
tar -xzf backup_migrations_[timestamp].tar.gz

# 3. Clear caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 4. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart apache2
```

### Partial Rollback (Migration only):
```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Or manual SQL
mysql -u [user] -p [database]
ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_kategori_unique;
ALTER TABLE vendors ADD UNIQUE KEY vendors_user_id_nama_vendor_unique (user_id, nama_vendor);
```

---

## 🎉 Success Indicators

Deployment sukses jika:
1. ✅ Vendor dengan nama sama tapi kategori beda bisa dibuat
2. ✅ Vendor phone validation: only numeric, auto-remove invalid characters
3. ✅ Required field validation: comprehensive alert with list of empty fields
4. ✅ Konversi sub satuan tampil sebagai accordion (collapsed default)
5. ✅ PDF laporan pembelian menampilkan design baru
6. ✅ Biaya kirim menggunakan COA 558
7. ✅ COA 310 tidak muncul di Laporan Posisi Keuangan
8. ✅ Retur Pembelian features hidden (tabs, buttons, status column)
9. ✅ Delete action hidden from transaksi pembelian
10. ✅ Tidak ada error di logs
10. ✅ Angka bulat tidak ada .00
11. ✅ Semua fitur berjalan normal

---

## 📧 Support

Jika ada masalah saat deployment:
1. Check logs: `storage/logs/laravel.log`
2. Check Apache/Nginx error logs
3. Check PHP-FPM logs
4. Rollback jika diperlukan
5. Contact developer untuk troubleshooting

---

**Created**: 2026-06-22  
**Updated**: 2026-06-22  
**Version**: 2.0  
**Priority**: High  
**Risk Level**: Medium (with backup: Low)
