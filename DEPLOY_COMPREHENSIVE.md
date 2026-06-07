# Comprehensive Production Deployment Guide

## Problem Statement

**Local Environment:** ✅ Fitur pembelian berjalan sempurna
- Preview jurnal menampilkan COA spesifik (e.g., "Pers. Bahan Baku Ayam Potong")
- Generate jurnal berhasil
- Notifikasi sukses muncul
- Redirect ke halaman Upload Bukti Faktur

**Production Environment:** ❌ Perilaku berbeda
- Preview jurnal menampilkan COA generik ("Persediaan Bahan Baku")
- Kemungkinan jurnal tidak terbuat atau error

## Root Cause Analysis

Berdasarkan investigasi, kemungkinan penyebab:
1. **COA Mapping Tidak Ter-update** - `coa_persediaan_id` masih NULL di production
2. **Source Code Belum Sync** - File di production versi lama
3. **Cache Issues** - View cache atau config cache masih menggunakan kode lama
4. **Foreign Key Constraint** - Masalah dengan foreign key `pembelians_vendor_id_foreign`
5. **Database Structure Different** - Struktur tabel berbeda antara local dan production

---

## Pre-Deployment Checklist

### Step 1: Run Diagnostic Script (MANDATORY)

**Di Local:**
```bash
php diagnose_pembelian_environment.php
```
Ini akan generate file: `diagnostic_report_local_YYYYMMDD_HHMMSS.json`

**Di Production:**
```bash
# Upload script ke production terlebih dahulu
php diagnose_pembelian_environment.php
```
Ini akan generate file: `diagnostic_report_production_YYYYMMDD_HHMMSS.json`

**Compare kedua file JSON untuk menemukan perbedaan!**

### Step 2: Backup Production

```bash
# Backup database
mysqldump -u username -p database_name > backup_before_deploy_$(date +%Y%m%d).sql

# Backup critical files
tar -czf backup_files_$(date +%Y%m%d).tar.gz \
  app/Http/Controllers/PembelianController.php \
  resources/views/transaksi/pembelian/create.blade.php \
  app/Services/PembelianJournalService.php
```

### Step 3: Verify Git Status

**Di Local:**
```bash
git status
git log --oneline -5
```

Pastikan semua perubahan sudah di-commit!

---

## Deployment Steps

### STEP 1: Update Source Code

```bash
# Di Production Server
cd /path/to/project

# Stash local changes (jika ada)
git stash

# Pull latest code
git fetch origin
git pull origin nayla

# Verify files updated
ls -la app/Http/Controllers/PembelianController.php
ls -la resources/views/transaksi/pembelian/create.blade.php
ls -la app/Services/PembelianJournalService.php
```

**Verification:**
```bash
# Check file modification dates
stat app/Http/Controllers/PembelianController.php
stat resources/views/transaksi/pembelian/create.blade.php
```

### STEP 2: Clear All Caches

```bash
# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

# Clear OPcache (if using PHP-FPM)
service php8.1-fpm reload  # Adjust PHP version as needed

# Verify cache cleared
ls -la bootstrap/cache/
ls -la storage/framework/views/
```

### STEP 3: Update Database - COA Mappings

**Option A: Via PHP Script (Recommended)**

Upload `deploy_to_production_manual.php` dan jalankan:
```bash
php deploy_to_production_manual.php
```

**Option B: Via SQL Direct**

```sql
-- Bahan Baku COA Mapping
UPDATE bahan_bakus SET coa_persediaan_id = '1141' WHERE nama_bahan = 'Ayam Potong' AND user_id = 3;
UPDATE bahan_bakus SET coa_persediaan_id = '1142' WHERE nama_bahan = 'Ayam Kampung' AND user_id = 3;
UPDATE bahan_bakus SET coa_persediaan_id = '1143' WHERE nama_bahan = 'Bebek' AND user_id = 3;

-- Bahan Pendukung COA Mapping
UPDATE bahan_pendukungs SET coa_persediaan_id = '1152' WHERE nama_bahan = 'Tepung Terigu' AND user_id = 3;
UPDATE bahan_pendukungs SET coa_persediaan_id = '1153' WHERE nama_bahan = 'Tepung Maizena' AND user_id = 3;
UPDATE bahan_pendukungs SET coa_persediaan_id = '1154' WHERE nama_bahan = 'Lada' AND user_id = 3;
UPDATE bahan_pendukungs SET coa_persediaan_id = '1155' WHERE nama_bahan = 'Bubuk Kaldu Ayam' AND user_id = 3;
UPDATE bahan_pendukungs SET coa_persediaan_id = '1156' WHERE nama_bahan = 'Bubuk Bawang Putih' AND user_id = 3;
UPDATE bahan_pendukungs SET coa_persediaan_id = '1151' WHERE nama_bahan = 'Minyak Goreng' AND user_id = 3;
UPDATE bahan_pendukungs SET coa_persediaan_id = '1150' WHERE nama_bahan = 'Air Galon' AND user_id = 3;
UPDATE bahan_pendukungs SET coa_persediaan_id = '1157' WHERE nama_bahan = 'Kemasan Makanan' AND user_id = 3;

-- Verify updates
SELECT id, nama_bahan, coa_persediaan_id FROM bahan_bakus WHERE user_id = 3 AND nama_bahan IN ('Ayam Potong', 'Ayam Kampung', 'Bebek');
SELECT id, nama_bahan, coa_persediaan_id FROM bahan_pendukungs WHERE user_id = 3 LIMIT 5;
```

### STEP 4: Fix Foreign Key Constraint (If Needed)

```bash
php artisan tinker --execute="
try {
    DB::statement('ALTER TABLE pembelians DROP FOREIGN KEY pembelians_vendor_id_foreign');
    DB::statement('ALTER TABLE pembelians ADD CONSTRAINT pembelians_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE');
    echo 'Foreign key fixed successfully';
} catch (Exception \$e) {
    echo 'Error or already fixed: ' . \$e->getMessage();
}
"
```

### STEP 5: Verify File Integrity

```bash
php verify_production_sync.php verify
```

Expected output: "ALL FILES ARE IN SYNC"

### STEP 6: Restart Services

```bash
# Restart PHP-FPM
sudo service php8.1-fpm restart

# Restart Queue Workers (if using)
php artisan queue:restart

# Restart Supervisor (if using)
sudo supervisorctl restart all

# Clear Nginx/Apache cache (if applicable)
sudo service nginx reload
# or
sudo service apache2 reload
```

---

## Post-Deployment Testing

### Test 1: Check Logs Setup

```bash
# Tail production logs in real-time
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

Keep this running in separate terminal!

### Test 2: Access Pembelian Page

1. Open browser: `https://your-domain.com/transaksi/pembelian/create`
2. Check browser console for JavaScript errors
3. Check network tab for failed requests

### Test 3: Preview Jurnal - Bahan Baku

1. Pilih vendor: "Tel-Mart"
2. Pilih kategori: "Bahan Baku"
3. Pilih item: "Ayam Potong"
4. Isi: Jumlah 10 Kg, Harga @80.000
5. Scroll ke Preview Jurnal Akuntansi

**Expected Result:**
```
Keterangan: Pers. Bahan Baku Ayam Potong
Akun: [1141] Pers. Bahan Baku Ayam Potong
Debit: Rp 800.000
```

**NOT:**
```
Keterangan: Persediaan Bahan Baku  ← WRONG!
```

### Test 4: Preview Jurnal - Bahan Pendukung

1. Tambah item baru
2. Pilih kategori: "Bahan Pendukung"
3. Pilih item: "Tepung Terigu"
4. Isi: Jumlah 5 Kg, Harga @15.000
5. Cek preview jurnal

**Expected Result:**
```
Keterangan: Pers. Bahan Pendukung Tepung Terigu
Akun: [1152] Pers. Bahan Pendukung Tepung Terigu
Debit: Rp 75.000
```

### Test 5: Save Transaction

1. Klik tombol "Simpan"
2. Tunggu redirect
3. Check for success message
4. Verify redirect ke halaman Upload Bukti Faktur

**Watch the logs terminal for:**
```
[PembelianController] ========== CREATING JOURNAL ENTRIES ==========
[PembelianJournal] Processing Bahan Baku
[PembelianJournal] ✓ Bahan Baku COA Found
[PembelianController] ✓ Journal entries created successfully
```

### Test 6: Verify Database

```sql
-- Check latest pembelian
SELECT * FROM pembelians ORDER BY created_at DESC LIMIT 1;

-- Check jurnal_umums for this pembelian
SELECT ju.*, c.kode_akun, c.nama_akun
FROM jurnal_umums ju
JOIN coas c ON ju.coa_id = c.id
WHERE ju.nomor_transaksi LIKE 'PB-%'
ORDER BY ju.created_at DESC
LIMIT 10;
```

---

## Troubleshooting

### Issue 1: Preview Jurnal Still Shows Generic COA

**Symptoms:**
- Preview menampilkan "Persediaan Bahan Baku" bukan "Pers. Bahan Baku Ayam Potong"

**Diagnosis:**
```bash
# Check COA mappings
php artisan tinker --execute="
\App\Models\BahanBaku::where('nama_bahan', 'Ayam Potong')
    ->get(['nama_bahan', 'coa_persediaan_id'])
    ->each(fn(\$bb) => echo \$bb->nama_bahan . ': ' . \$bb->coa_persediaan_id . PHP_EOL);
"
```

**Solutions:**
1. Run Step 3 again (Update COA mappings)
2. Clear browser cache: Ctrl+F5
3. Clear view cache: `php artisan view:clear`
4. Check file: `resources/views/transaksi/pembelian/create.blade.php` line 1086-1104

### Issue 2: Error When Saving - Foreign Key Constraint

**Symptoms:**
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row
```

**Solution:**
Run Step 4 (Fix Foreign Key Constraint)

### Issue 3: Journal Not Created - COA Not Found Error

**Symptoms:**
- Success message: "Data pembelian berhasil disimpan, tetapi jurnal akuntansi belum dapat dibuat"
- Log shows: "COA Persediaan untuk bahan baku 'X' tidak ditemukan"

**Diagnosis:**
Check logs for detailed error:
```bash
grep -A 10 "PembelianJournal" storage/logs/laravel-$(date +%Y-%m-%d).log | tail -50
```

**Solutions:**
1. Verify COA exists:
   ```sql
   SELECT * FROM coas WHERE kode_akun = '1141' AND user_id = 3;
   ```

2. Verify mapping:
   ```sql
   SELECT * FROM bahan_bakus WHERE nama_bahan = 'Ayam Potong' AND user_id = 3;
   ```

3. If COA doesn't exist, run seeder:
   ```bash
   php artisan db:seed --class=DefaultCoaSeeder
   ```

### Issue 4: File Not Updated After Git Pull

**Symptoms:**
- Files have old modification dates
- Code changes not reflected

**Solutions:**
```bash
# Reset to remote
git fetch origin
git reset --hard origin/nayla

# Clear all caches
php artisan view:clear
php artisan config:clear
php artisan cache:clear

# Verify file content
head -100 resources/views/transaksi/pembelian/create.blade.php | grep -A 5 "coa_persediaan_id"
```

### Issue 5: View Cache Persists

**Symptoms:**
- Blade changes not reflected even after `view:clear`

**Solutions:**
```bash
# Delete view cache files manually
rm -rf storage/framework/views/*

# Check opcache (if using PHP-FPM)
sudo service php8.1-fpm reload

# Clear browser cache
# In browser: Ctrl+Shift+Del or use Incognito mode
```

---

## Log Analysis Guide

### Finding Issues in Logs

```bash
# Follow logs in real-time
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Search for pembelian-related logs
grep "PembelianController\|PembelianJournal" storage/logs/laravel-$(date +%Y-%m-%d).log

# Search for errors only
grep "ERROR\|Exception" storage/logs/laravel-$(date +%Y-%m-%d).log

# Search for specific pembelian ID
grep "pembelian_id.*123" storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Understanding Log Patterns

**Successful Flow:**
```
[PembelianController] ========== CREATING JOURNAL ENTRIES ==========
[PembelianController] Detail Item
[PembelianController] Bahan Baku Detail
[PembelianJournal] getCoaForItem START
[PembelianJournal] Processing Bahan Baku
[PembelianJournal] COA Query Result
[PembelianJournal] ✓ Bahan Baku COA Found
[PembelianJournal] getCoaForItem END
[PembelianController] ✓ Journal entries created successfully
```

**Failed Flow (COA Not Mapped):**
```
[PembelianJournal] Processing Bahan Baku
[PembelianJournal] ✗ COA Persediaan ID NULL
[PembelianController] ✗ Failed to create journal entries
```

**Failed Flow (COA Not Found):**
```
[PembelianJournal] COA Query Result → coa_found: false
[PembelianJurnal] ✗ COA Not Found
[PembelianController] ✗ Failed to create journal entries
```

---

## Rollback Procedure

If deployment fails:

### Step 1: Rollback Code
```bash
git reset --hard HEAD~1
# or restore from backup
tar -xzf backup_files_YYYYMMDD.tar.gz
```

### Step 2: Rollback Database
```bash
mysql -u username -p database_name < backup_before_deploy_YYYYMMDD.sql
```

### Step 3: Clear Caches
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Step 4: Restart Services
```bash
sudo service php8.1-fpm restart
sudo service nginx reload
```

---

## Success Criteria

✅ Production deployment is successful when:

1. **Diagnostic Report** shows no critical issues
2. **File Verification** shows all files in sync
3. **COA Mappings** are set for all bahan baku & bahan pendukung
4. **Preview Jurnal** displays specific COA names (not generic)
5. **Save Transaction** succeeds without errors
6. **Journal Created** with correct COA entries
7. **Redirect** to Upload Bukti Faktur page works
8. **Logs** show successful flow pattern

---

## Maintenance Commands

### Regular Health Check
```bash
# Run diagnostic
php diagnose_pembelian_environment.php

# Check logs for errors
grep "ERROR\|CRITICAL" storage/logs/laravel-$(date +%Y-%m-%d).log

# Verify COA mappings
php artisan tinker --execute="
echo 'Unmapped Bahan Baku: ' . \App\Models\BahanBaku::whereNull('coa_persediaan_id')->count() . PHP_EOL;
echo 'Unmapped Bahan Pendukung: ' . \App\Models\BahanPendukung::whereNull('coa_persediaan_id')->count() . PHP_EOL;
"
```

---

## Contact & Support

If issues persist after following this guide:

1. Run `php diagnose_pembelian_environment.php`
2. Collect logs: `tail -1000 storage/logs/laravel-$(date +%Y-%m-%d).log > debug_logs.txt`
3. Export diagnostic reports (local & production)
4. Document exact steps taken
5. Contact developer with all above files
