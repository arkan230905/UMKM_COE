# 📖 Manual Deployment Guide - Step by Step

**Untuk user yang tidak familiar dengan command line atau prefer deploy manual via FTP/cPanel**

---

## 🎯 Yang Perlu Di-upload ke Production

### 1. File Migration (Database)
```
📁 database/migrations/
   └── 2026_06_15_000000_force_remove_old_vendor_constraint.php
```

### 2. File Controller
```
📁 app/Http/Controllers/
   ├── VendorController.php
   └── PembelianController.php
```

### 3. File Service
```
📁 app/Services/
   └── PembelianJournalService.php
```

### 4. File Observer
```
📁 app/Observers/
   ├── BahanBakuObserver.php
   └── BahanPendukungObserver.php
```

### 5. File View (PENTING!)
```
📁 resources/views/
   ├── transaksi/pembelian/create.blade.php
   ├── transaksi/pembelian/show.blade.php
   ├── laporan/pembelian/export.blade.php
   └── master-data/kategori-bahan-pendukung/index.blade.php
```

### 6. File Helper
```
📁 app/Helpers/
   └── helpers.php
```

### 7. File Seeder
```
📁 database/seeders/
   └── DefaultCoaSeeder.php
```

---

## 🚀 Langkah Deployment Via FTP/cPanel

### Cara 1: Via FTP (FileZilla, WinSCP, dll)

1. **Buka FTP Client** (FileZilla recommended)
   
2. **Connect ke server production**
   - Host: ftp.yourdomain.com
   - Username: your_ftp_username
   - Password: your_ftp_password
   - Port: 21 (atau 22 untuk SFTP)

3. **Backup files lama** (PENTING!)
   - Download dulu semua file yang akan di-replace
   - Simpan di folder local: `backup_[tanggal]`

4. **Upload file satu per satu**
   - Drag & drop file dari list di atas
   - Pastikan struktur folder sama persis
   - Overwrite file yang sudah ada

5. **Verify upload**
   - Check timestamp file sudah update
   - Check file size sama dengan localhost

### Cara 2: Via cPanel File Manager

1. **Login ke cPanel**
   - URL: https://yourdomain.com/cpanel
   - Login dengan credentials

2. **Buka File Manager**
   - Klik icon "File Manager"
   - Navigate ke folder aplikasi (biasanya `public_html` atau `www`)

3. **Backup files lama**
   - Select file yang akan diganti
   - Klik "Compress" → pilih ZIP
   - Download ZIP backup

4. **Upload files baru**
   - Navigate ke folder yang sesuai
   - Klik "Upload"
   - Select file dari localhost
   - Wait sampai upload complete

5. **Extract jika upload ZIP**
   - Right click ZIP file
   - Click "Extract"

---

## 🗄️ Jalankan Migration Database

### Via cPanel Terminal (Recommended)

1. **Buka Terminal di cPanel**
   - Cari menu "Terminal" di cPanel
   - Click untuk open

2. **Navigate ke folder aplikasi**
   ```bash
   cd public_html
   # atau
   cd www
   # atau sesuai lokasi aplikasi
   ```

3. **Backup database dulu!**
   ```bash
   php artisan db:backup
   # atau via cPanel phpMyAdmin:
   # - Buka phpMyAdmin
   # - Select database
   # - Click tab "Export"
   # - Download SQL file
   ```

4. **Jalankan migration**
   ```bash
   php artisan migrate --force
   ```
   
   Tunggu sampai muncul message:
   ```
   ✓ Successfully dropped old constraint
   ✓ Successfully added new constraint
   ✅ Migration completed!
   ```

### Via SSH (If Available)

1. **Connect via SSH**
   ```bash
   ssh username@yourdomain.com
   ```

2. **Navigate to project**
   ```bash
   cd /path/to/project
   ```

3. **Run migration**
   ```bash
   php artisan migrate --force
   ```

### Via phpMyAdmin (Manual)

Jika tidak ada akses terminal:

1. **Buka phpMyAdmin di cPanel**

2. **Select database aplikasi**

3. **Click tab "SQL"**

4. **Copy paste query ini:**
   ```sql
   -- Check current constraint
   SHOW INDEX FROM vendors WHERE Non_unique = 0 AND Key_name != 'PRIMARY';
   
   -- Drop old constraint
   ALTER TABLE vendors DROP INDEX vendors_user_id_nama_vendor_unique;
   
   -- Add new constraint  
   ALTER TABLE vendors 
   ADD UNIQUE KEY vendors_user_id_nama_vendor_kategori_unique (user_id, nama_vendor, kategori);
   
   -- Verify
   SHOW INDEX FROM vendors WHERE Non_unique = 0 AND Key_name != 'PRIMARY';
   ```

5. **Click "Go" untuk execute**

---

## 🧹 Clear Cache

### Via cPanel Terminal

```bash
cd /path/to/project

# Clear semua cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

# Delete compiled views
rm -rf storage/framework/views/*

# Rebuild
php artisan optimize
```

### Via File Manager (Manual)

1. **Navigate ke folder storage**
   ```
   storage/framework/views/
   ```

2. **Delete semua file .php di folder views**
   - Select all files
   - Delete

3. **Navigate ke folder cache**
   ```
   storage/framework/cache/data/
   ```

4. **Delete semua file cache**
   - Select all files
   - Delete

5. **Clear browser cache**
   - Buka aplikasi di browser
   - Tekan `Ctrl + Shift + Delete`
   - Clear cache & cookies
   - Atau tekan `Ctrl + Shift + R` (hard refresh)

---

## 🔄 Restart PHP/Apache

### Via cPanel (Recommended)

1. **Restart PHP Service**
   - Cari menu "MultiPHP Manager" atau "Select PHP Version"
   - Select PHP version yang sama
   - Click "Apply" atau "Save"
   - Ini akan trigger PHP restart

2. **Restart via "Restart Services"**
   - Beberapa cPanel ada menu "Restart Services"
   - Pilih "Apache" atau "LiteSpeed"
   - Click "Restart"

### Via WHM (If You Have Access)

1. Login WHM
2. Search "Restart Services"
3. Restart "httpd" (Apache)
4. Restart "PHP-FPM"

### Contact Hosting Support

Jika tidak ada akses restart:
- Contact hosting support via ticket/chat
- Minta tolong restart PHP-FPM dan Apache
- Biasanya 5-10 menit

---

## ✅ Testing & Verification

### 1. Test Vendor (PENTING!)

1. **Login ke aplikasi**

2. **Pergi ke Master Data → Vendor**

3. **Test Case 1: Different Kategori (Harus BERHASIL)**
   - Create vendor:
     ```
     Nama: Sukbir Mart
     Kategori: Bahan Baku
     ```
     ✅ SAVE (harus berhasil)
   
   - Create vendor lagi:
     ```
     Nama: Sukbir Mart
     Kategori: Bahan Pendukung
     ```
     ✅ SAVE (harus berhasil)

4. **Test Case 2: Same Kategori (Harus ERROR)**
   - Create vendor:
     ```
     Nama: Sukbir Mart
     Kategori: Bahan Baku
     ```
     ❌ ERROR (harus muncul pesan: "Vendor sudah ada untuk kategori ini")

### 2. Test Pembelian Form

1. **Pergi ke Transaksi → Tambah Pembelian**

2. **Check "Konversi Sub Satuan"**
   - ✅ Harus tertutup (collapsed) by default
   - ✅ Klik header untuk buka/tutup
   - ✅ Isi hanya info (read-only), bukan input
   - ✅ Format: "1 Kilogram = 1000 Gram"

3. **Input data pembelian**
   - Input jumlah bulat (contoh: 10)
   - ✅ Harus tampil "10" bukan "10.00"

### 3. Test PDF Export

1. **Pergi ke Laporan → Laporan Pembelian**

2. **Click "Export PDF"**

3. **Check design:**
   - ✅ Header "LAPORAN PEMBELIAN"
   - ✅ Summary cards (Total Transaksi, Grand Total, dll)
   - ✅ Table dengan background cream
   - ✅ Footer dengan info cetak

### 4. Test Biaya Kirim

1. **Buat transaksi pembelian dengan biaya kirim**

2. **Check jurnal** (Laporan → Jurnal Umum)
   - Filter by nomor pembelian
   - ✅ Biaya kirim harus pakai COA 558 (Beban Transport Pembelian)

---

## 🆘 Troubleshooting

### Problem: Tampilan masih lama

**Solution:**
1. Clear cache via terminal:
   ```bash
   php artisan view:clear
   php artisan cache:clear
   ```

2. Delete compiled views manual:
   - Via File Manager
   - Go to `storage/framework/views/`
   - Delete all `.php` files

3. Clear browser cache:
   - `Ctrl + Shift + Delete`
   - Delete cache & cookies
   - Close & reopen browser

4. Hard refresh:
   - `Ctrl + Shift + R` (Windows)
   - `Cmd + Shift + R` (Mac)

### Problem: Error 500 after upload

**Solution:**
1. Check file permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chmod -R 777 storage/logs
   ```

2. Check logs:
   - Via File Manager: `storage/logs/laravel.log`
   - Cari error message terakhir

3. Clear config:
   ```bash
   php artisan config:clear
   ```

### Problem: Migration failed

**Solution:**
1. Check database connection:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

2. Run SQL manual via phpMyAdmin (lihat section "Via phpMyAdmin")

3. Check constraint name:
   ```sql
   SHOW INDEX FROM vendors;
   ```
   Gunakan nama exact yang muncul

### Problem: Vendor error masih muncul

**Solution:**
1. Verify migration ran:
   ```bash
   php artisan migrate:status
   ```

2. Check constraint di database:
   ```sql
   SHOW INDEX FROM vendors WHERE Key_name LIKE '%unique%';
   ```
   
   Harus muncul: `vendors_user_id_nama_vendor_kategori_unique`

3. Re-run migration:
   ```bash
   php artisan migrate:refresh --path=database/migrations/2026_06_15_000000_force_remove_old_vendor_constraint.php
   ```

---

## 📞 Need Help?

Jika ada masalah:

1. **Check logs:**
   - `storage/logs/laravel.log`
   - Apache error log (via cPanel)

2. **Screenshot error message**

3. **Contact developer dengan info:**
   - Screenshot error
   - Isi error log
   - Step yang sudah dilakukan

---

## ✨ Success Checklist

Deployment berhasil jika:

- [ ] ✅ Semua file ter-upload
- [ ] ✅ Migration berhasil
- [ ] ✅ Vendor bisa dibuat dengan nama sama, kategori beda
- [ ] ✅ Konversi sub satuan tampil collapsed
- [ ] ✅ PDF export design baru
- [ ] ✅ Angka bulat tanpa .00
- [ ] ✅ Biaya kirim pakai COA 558
- [ ] ✅ Tidak ada error di aplikasi

---

**Good luck with deployment! 🚀**
