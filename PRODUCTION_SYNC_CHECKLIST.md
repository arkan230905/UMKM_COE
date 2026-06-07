# Production Sync Checklist - Transaksi Pembelian

## Tujuan
Memastikan source code di production server sudah sama dengan local terbaru, khususnya untuk fitur transaksi pembelian.

## Status Saat Ini
- ✅ Local: Fitur pembelian berjalan normal
- ❌ Production: Preview jurnal masih menampilkan COA generik
- ❓ Unknown: Apakah kode sudah ter-sync?

---

## Metode Verifikasi

### Metode 1: Automated Verification (Recommended)

1. **Di Local:**
   ```bash
   php verify_production_sync.php generate
   ```
   Ini akan membuat file `checksums_local.json`

2. **Upload ke Production:**
   - Upload `checksums_local.json` ke root directory production
   - Upload `verify_production_sync.php` ke root directory production

3. **Di Production:**
   ```bash
   php verify_production_sync.php verify
   ```

4. **Interpretasi Hasil:**
   - ✓ All files matching = Production sudah sync
   - ⚠ Different files = Ada file yang berbeda, perlu update
   - ❌ Missing files = Ada file yang belum ada di production

### Metode 2: Manual Verification

Cek file-file berikut secara manual:

#### A. File Controller (Paling Penting)
- [ ] `app/Http/Controllers/PembelianController.php`
  - Cek method `create()` line 214-333
  - Cek method `store()` line 620-1110
  - Pastikan TIDAK ada eager loading di line 1063-1095

- [ ] `app/Http/Controllers/BahanBakuController.php`
- [ ] `app/Http/Controllers/VendorController.php`

#### B. File View (Kritis untuk Preview Jurnal)
- [ ] `resources/views/transaksi/pembelian/create.blade.php`
  - **Line 1086-1092:** Query COA untuk Bahan Baku
    ```php
    $coa = $bb->coa_persediaan_id ? \App\Models\Coa::where('kode_akun', $bb->coa_persediaan_id)->where('user_id', auth()->id())->first() : null;
    ```
  - **Line 1096-1104:** Query COA untuk Bahan Pendukung
    ```php
    $coa = $bp->coa_persediaan_id ? \App\Models\Coa::where('kode_akun', $bp->coa_persediaan_id)->where('user_id', auth()->id())->first() : null;
    ```
  - **Line 1349-1356:** Preview jurnal menggunakan `coaNama`

#### C. File Model
- [ ] `app/Models/Pembelian.php`
- [ ] `app/Models/BahanBaku.php` - Harus punya relasi `coaPersediaan`
- [ ] `app/Models/BahanPendukung.php` - Harus punya relasi `coaPersediaan`

#### D. File Service
- [ ] `app/Services/PembelianJournalService.php`
- [ ] `app/Services/StockService.php`

#### E. File Observer
- [ ] `app/Observers/PembelianObserver.php`
- [ ] `app/Observers/BahanPendukungObserver.php`

---

## Checklist Deployment

### Pre-Deployment

- [ ] Backup database production
- [ ] Backup file production (minimal controllers & views)
- [ ] Test fitur di local masih berjalan normal
- [ ] Commit semua perubahan ke Git
- [ ] Push ke branch `nayla`

### Deployment Steps

- [ ] **Step 1:** SSH ke server production
  ```bash
  ssh user@production-server
  cd /path/to/project
  ```

- [ ] **Step 2:** Cek branch saat ini
  ```bash
  git branch
  git status
  ```

- [ ] **Step 3:** Stash perubahan local di production (jika ada)
  ```bash
  git stash
  ```

- [ ] **Step 4:** Pull kode terbaru
  ```bash
  git pull origin nayla
  ```

- [ ] **Step 5:** Cek file-file penting sudah terupdate
  ```bash
  # Cek timestamp file
  ls -la app/Http/Controllers/PembelianController.php
  ls -la resources/views/transaksi/pembelian/create.blade.php
  
  # Cek isi file (line tertentu)
  sed -n '1086,1092p' resources/views/transaksi/pembelian/create.blade.php
  ```

- [ ] **Step 6:** Clear semua cache
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
  php artisan route:clear
  php artisan optimize:clear
  ```

- [ ] **Step 7:** Update COA mappings
  ```bash
  php deploy_to_production_manual.php
  ```

- [ ] **Step 8:** Fix foreign key (jika diperlukan)
  ```sql
  ALTER TABLE pembelians DROP FOREIGN KEY pembelians_vendor_id_foreign;
  ALTER TABLE pembelians ADD CONSTRAINT pembelians_vendor_id_foreign 
  FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE;
  ```

- [ ] **Step 9:** Restart services (jika pakai queue/supervisor)
  ```bash
  php artisan queue:restart
  sudo supervisorctl restart all
  ```

- [ ] **Step 10:** Test file integrity
  ```bash
  php verify_production_sync.php verify
  ```

### Post-Deployment Testing

- [ ] **Test 1:** Buka halaman pembelian
  - URL: `https://your-domain.com/transaksi/pembelian/create`
  - Halaman harus load tanpa error

- [ ] **Test 2:** Preview Jurnal - Bahan Baku
  - Pilih vendor: "Tel-Mart"
  - Pilih kategori: "Bahan Baku"
  - Pilih item: "Ayam Potong"
  - Isi: Jumlah 10 Kg, Harga @80.000
  - Cek preview jurnal:
    - ✅ Keterangan: "Pers. Bahan Baku Ayam Potong"
    - ✅ Akun: Badge "1141" + "Pers. Bahan Baku Ayam Potong"
    - ❌ BUKAN: "Persediaan Bahan Baku" (generik)

- [ ] **Test 3:** Preview Jurnal - Bahan Pendukung
  - Tambah item baru
  - Pilih kategori: "Bahan Pendukung"
  - Pilih item: "Tepung Terigu"
  - Isi: Jumlah 5 Kg, Harga @15.000
  - Cek preview jurnal:
    - ✅ Keterangan: "Pers. Bahan Pendukung Tepung Terigu"
    - ✅ Akun: Badge "1152" + "Pers. Bahan Pendukung Tepung Terigu"
    - ❌ BUKAN: "Persediaan Bahan Pendukung" (generik)

- [ ] **Test 4:** Simpan Transaksi
  - Klik tombol "Simpan"
  - ✅ Harus berhasil tanpa error
  - ✅ Muncul notifikasi sukses
  - ✅ Data tersimpan di database
  - ✅ Jurnal terbuat dengan benar

- [ ] **Test 5:** Cek Database
  ```sql
  -- Cek COA mapping bahan baku
  SELECT id, nama_bahan, coa_persediaan_id 
  FROM bahan_bakus 
  WHERE nama_bahan IN ('Ayam Potong', 'Ayam Kampung', 'Bebek');
  
  -- Cek COA mapping bahan pendukung
  SELECT id, nama_bahan, coa_persediaan_id 
  FROM bahan_pendukungs 
  WHERE nama_bahan IN ('Tepung Terigu', 'Minyak Goreng');
  ```

---

## Troubleshooting

### Issue 1: Preview Jurnal Masih Menampilkan COA Generik

**Kemungkinan Penyebab:**
1. Browser cache belum di-clear
2. View cache Laravel belum di-clear
3. File `create.blade.php` belum terupdate
4. COA mapping di database masih NULL

**Solusi:**
```bash
# Di browser
Ctrl + F5 (hard refresh)

# Di server
php artisan view:clear
php artisan config:clear

# Cek file terupdate
cat resources/views/transaksi/pembelian/create.blade.php | grep -A 3 "coa_persediaan_id"

# Update COA mapping
php deploy_to_production_manual.php
```

### Issue 2: Error Foreign Key saat Simpan

**Error:**
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row
```

**Solusi:**
```bash
php deploy_to_production_manual.php
```

### Issue 3: File Tidak Terupdate Setelah Git Pull

**Kemungkinan Penyebab:**
1. Git conflict
2. File permissions
3. Wrong branch

**Solusi:**
```bash
# Cek branch
git branch

# Cek status
git status

# Reset ke latest commit
git fetch origin
git reset --hard origin/nayla

# Clear cache
php artisan view:clear
```

---

## Verification Commands

Jalankan command ini untuk verifikasi:

```bash
# 1. Cek Git status
git log --oneline -5
git status

# 2. Cek file timestamps
stat resources/views/transaksi/pembelian/create.blade.php
stat app/Http/Controllers/PembelianController.php

# 3. Cek database COA mapping
php artisan tinker --execute="
  echo 'Bahan Baku COA Mapping:' . PHP_EOL;
  \App\Models\BahanBaku::whereIn('nama_bahan', ['Ayam Potong', 'Ayam Kampung'])
    ->get(['nama_bahan', 'coa_persediaan_id'])
    ->each(fn(\$bb) => echo \"  {\$bb->nama_bahan}: {\$bb->coa_persediaan_id}\" . PHP_EOL);
"

# 4. Cek cache status
php artisan config:cache
php artisan view:cache

# 5. Verify checksums
php verify_production_sync.php verify
```

---

## Success Criteria

Production dianggap sudah sync jika:

- ✅ `php verify_production_sync.php verify` menunjukkan "ALL FILES ARE IN SYNC"
- ✅ Preview jurnal menampilkan COA spesifik (bukan generik)
- ✅ Transaksi pembelian bisa disimpan tanpa error
- ✅ COA mapping di database sudah terisi
- ✅ Foreign key constraint tidak ada error

---

## Rollback Plan

Jika deployment gagal:

1. **Restore file backup:**
   ```bash
   cp -r /backup/app /path/to/project/
   cp -r /backup/resources /path/to/project/
   ```

2. **Rollback Git:**
   ```bash
   git reset --hard HEAD~1
   ```

3. **Restore database backup:**
   ```bash
   mysql -u user -p database < backup.sql
   ```

4. **Clear cache:**
   ```bash
   php artisan view:clear
   php artisan config:clear
   ```

---

## Contact

Jika ada masalah saat deployment, dokumentasikan:
1. Error message lengkap
2. Screenshot (jika ada)
3. Output dari `php verify_production_sync.php verify`
4. Git log: `git log --oneline -10`
