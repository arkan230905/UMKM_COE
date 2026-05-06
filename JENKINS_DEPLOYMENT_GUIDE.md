# Jenkins Deployment Guide

## Status: Code Pushed to GitHub ✅

**Commit**: `2da168c` - "Add Jabatan migration and deployment scripts"  
**Branch**: `main`  
**Pushed**: 3 Mei 2026, 14:45 WIB

---

## 📦 What Was Pushed

### New Files:
1. **`database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`**
   - Migration untuk fix unique constraint Jabatan
   - Mengubah dari `(kode_jabatan)` menjadi `(kode_jabatan, user_id)`

2. **`HASIL_PERBAIKAN.md`**
   - Laporan lengkap hasil deployment
   - Testing checklist
   - Technical details

3. **`check_satuan_count.php`**
   - Script untuk verifikasi jumlah Satuan per user
   - Berguna untuk troubleshooting

4. **`fix_all_hosting.sh`**
   - Automated deployment script
   - Bisa digunakan untuk deployment berikutnya

### Previously Pushed (Already in Hosting):
- `app/Models/Aset.php` - Fix generateKodeAset()
- `database/seeders/DefaultSatuanSeeder.php` - 16 units
- `database/migrations/2026_05_03_130000_fix_asets_unique_constraint_multi_tenant.php`
- `insert_satuan_kurang.php`

---

## 🚀 Jenkins Auto-Deployment

### Expected Jenkins Behavior:

1. **Webhook Trigger**: GitHub push akan trigger Jenkins build
2. **Git Pull**: Jenkins akan pull latest code dari `main` branch
3. **File Sync**: Semua file baru akan di-copy ke `/var/www/html`
4. **Composer**: Jenkins mungkin akan run `composer install` (optional)

### What Jenkins Will Deploy:

```
/var/www/html/
├── database/migrations/
│   └── 2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php ← NEW
├── HASIL_PERBAIKAN.md ← NEW
├── check_satuan_count.php ← NEW
└── fix_all_hosting.sh ← NEW
```

---

## ⚠️ IMPORTANT: Migration Sudah Dijalankan!

**CATATAN PENTING**: Migration Jabatan **SUDAH DIJALANKAN MANUAL** di hosting!

```bash
✅ DONE: php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php
```

**Jadi Jenkins TIDAK PERLU menjalankan migration lagi!**

Jika Jenkins config include `php artisan migrate`, migration ini akan di-skip karena sudah ada di tabel `migrations`.

---

## 🔍 Verifikasi Setelah Jenkins Deploy

### 1. Cek File Sudah Ada di Hosting

SSH ke hosting dan cek:

```bash
ssh simcost@103.134.154.77

# Cek migration file
ls -la /var/www/html/database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php

# Cek dokumentasi
ls -la /var/www/html/HASIL_PERBAIKAN.md

# Cek script
ls -la /var/www/html/check_satuan_count.php
```

**Expected**: Semua file ada dengan timestamp terbaru

---

### 2. Verifikasi Migration Status

```bash
cd /var/www/html
php artisan migrate:status | grep "2026_05_03"
```

**Expected Output**:
```
Ran  2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant
Ran  2026_05_03_130000_fix_asets_unique_constraint_multi_tenant
```

---

### 3. Test Aplikasi

#### Test 1: Jabatan (PALING PENTING!)
1. Login ke: http://jobcost.eadtmanufaktur.com
2. User: Muhammad Arkan Abiyyu (User ID: 4)
3. Buka: Master Data > Kualifikasi Tenaga Kerja
4. Klik "Tambah"
5. Isi form:
   - Nama: Test Jabatan
   - Kategori: BTKL
   - Tunjangan: 0
   - Tunjangan Transport: 100000
   - Tunjangan Konsumsi: 200000
   - Asuransi: 50000
   - Tarif: 15000
   - Gaji Pokok: 0
   - Tarif Per Jam: 15000
6. Klik "Simpan"

**Expected**: ✅ Berhasil tanpa error "Duplicate entry 'BT001'"

#### Test 2: Aset
1. Buka: Master Data > Aset
2. Klik "Tambah Aset"
3. Isi form aset
4. Klik "Simpan"

**Expected**: ✅ Berhasil tanpa error "Duplicate entry 'AST-202605-0001'"

#### Test 3: Satuan
1. Buka: Master Data > Satuan

**Expected**: 
- ✅ Menampilkan 16 Satuan
- ✅ Semua bisa diedit (tidak ada "Tidak dapat diubah")

#### Test 4: COA
1. Buka: Master Data > COA

**Expected**:
- ✅ Menampilkan 50 COA (format Jasuke)
- ✅ Semua bisa diedit

---

## 🔧 Troubleshooting

### Jika File Tidak Muncul di Hosting

**Kemungkinan 1**: Jenkins belum trigger
```bash
# Cek Jenkins dashboard
# Lihat apakah ada build baru yang running
```

**Kemungkinan 2**: Jenkins gagal pull
```bash
# SSH ke hosting
cd /var/www/html
sudo git pull origin main
```

**Kemungkinan 3**: Permission issue
```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

---

### Jika Masih Ada Error Duplicate Entry

**Untuk Jabatan**:
```bash
# Cek constraint
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';"

# Expected: jabatans_kode_user_unique dengan 2 columns
```

**Untuk Aset**:
```bash
# Cek constraint
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM asets WHERE Column_name = 'kode_aset';"

# Expected: asets_kode_user_unique dengan 2 columns
```

**Jika constraint salah**, jalankan manual:
```bash
cd /var/www/html
php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php
```

---

## 📊 Monitoring Jenkins

### Jenkins Dashboard
- URL: (tanyakan ke admin Jenkins)
- Cari job untuk project UMKM_COE
- Lihat console output untuk detail deployment

### Expected Jenkins Log:
```
Started by GitHub push by arkan230905
Checking out Revision 2da168c...
Building in workspace /var/lib/jenkins/workspace/UMKM_COE
...
Finished: SUCCESS
```

---

## ✅ Success Criteria

Deployment dianggap berhasil jika:

1. ✅ File migration ada di `/var/www/html/database/migrations/`
2. ✅ Migration status menunjukkan "Ran"
3. ✅ Test tambah Jabatan berhasil tanpa error
4. ✅ Test tambah Aset berhasil tanpa error
5. ✅ Halaman Satuan menampilkan 16 units
6. ✅ Halaman COA menampilkan 50 accounts

---

## 📞 Contact

Jika ada masalah:
1. Cek file `HASIL_PERBAIKAN.md` untuk detail lengkap
2. Jalankan `php check_satuan_count.php` untuk verifikasi Satuan
3. Cek Laravel log: `tail -f /var/www/html/storage/logs/laravel.log`

---

**Last Updated**: 3 Mei 2026, 14:45 WIB  
**Status**: Waiting for Jenkins deployment  
**Next Step**: Monitor Jenkins dan test aplikasi
