# 🖼️ Fix Foto Produk Tidak Muncul di SSH Server

## 🔍 DIAGNOSA MASALAH

Foto produk tidak muncul karena salah satu dari:
1. **Symlink storage belum dibuat** (`public/storage` → `storage/app/public`)
2. **Permissions folder storage salah** (tidak bisa di-read oleh web server)
3. **File foto tidak ter-upload ke server**
4. **Path foto di database salah**

---

## ✅ SOLUSI LENGKAP (Di SSH/MobaXterm)

### Step 1: SSH ke Server
```bash
ssh user@your-server-ip
cd /path/to/your/laravel/project
```

**Contoh path:**
- `/var/www/html/umkm_coe`
- `/home/username/public_html`
- `/opt/lampp/htdocs/umkm_coe`

---

### Step 2: Cek Current Working Directory
```bash
pwd
# Pastikan Anda berada di root folder Laravel (ada file artisan, .env, dll)
ls -la
# Harus ada folder: app, bootstrap, config, database, public, resources, storage, vendor
```

---

### Step 3: Buat Symlink Storage
```bash
# Hapus symlink lama jika ada (tidak akan menghapus file asli)
rm -f public/storage

# Buat symlink baru
php artisan storage:link

# Expected output:
# The [public/storage] link has been connected to [storage/app/public].
```

**Penjelasan:**
- `public/storage` = folder yang diakses oleh browser
- `storage/app/public` = folder asli tempat file disimpan
- Symlink membuat `public/storage` mengarah ke `storage/app/public`

---

### Step 4: Set Permissions (PENTING!)
```bash
# Set owner untuk storage dan public
sudo chown -R www-data:www-data storage public

# Set permissions untuk storage
sudo chmod -R 775 storage

# Set permissions untuk public/storage (setelah symlink dibuat)
sudo chmod -R 775 public/storage

# Atau jika menggunakan Apache dengan user berbeda:
# sudo chown -R apache:apache storage public
# Atau
# sudo chown -R nginx:nginx storage public
```

**Penjelasan Permissions:**
- `775` = Owner (rwx), Group (rwx), Others (r-x)
- `www-data` = User yang menjalankan web server (Apache/Nginx)
- Pastikan user ini sesuai dengan web server Anda

---

### Step 5: Verifikasi Symlink
```bash
# Cek apakah symlink berhasil dibuat
ls -la public/storage

# Expected output:
# lrwxrwxrwx 1 www-data www-data 26 Jun  8 10:00 public/storage -> /path/to/storage/app/public
```

---

### Step 6: Cek Isi Folder Storage
```bash
# Lihat isi storage/app/public
ls -la storage/app/public/

# Jika ada folder produk:
ls -la storage/app/public/produk/

# Jika kosong, berarti file foto belum ter-upload
```

---

### Step 7: Test Upload Foto Baru (Via Web Interface)

1. Login ke web interface: `http://your-domain.com`
2. Go to: **Master Data** → **Produk**
3. Klik **Edit** pada salah satu produk
4. Upload foto baru
5. Save
6. Refresh halaman daftar produk
7. Foto harus muncul

---

### Step 8: Troubleshooting Lanjutan

#### A. Jika Symlink Gagal:
```bash
# Alternatif 1: Buat symlink manual
ln -s ../storage/app/public public/storage

# Alternatif 2: Gunakan absolute path
ln -s /path/to/your/project/storage/app/public /path/to/your/project/public/storage

# Contoh:
ln -s /var/www/html/umkm_coe/storage/app/public /var/www/html/umkm_coe/public/storage
```

#### B. Jika Permissions Tidak Bisa Diubah:
```bash
# Login sebagai root
sudo su

# Set permissions dengan force
chmod -R 777 storage
chmod -R 777 public/storage

# WARNING: 777 tidak aman untuk production!
# Gunakan hanya untuk testing, lalu kembalikan ke 775
```

#### C. Jika Foto Masih Tidak Muncul:
```bash
# Cek SELinux (Red Hat/CentOS)
getenforce
# Jika "Enforcing", jalankan:
sudo chcon -R -t httpd_sys_rw_content_t storage/
sudo chcon -R -t httpd_sys_rw_content_t public/storage/

# Atau disable SELinux sementara (tidak disarankan)
sudo setenforce 0
```

#### D. Cek Web Server Error Logs:
```bash
# Apache
sudo tail -f /var/log/apache2/error.log
# Atau
sudo tail -f /var/log/httpd/error_log

# Nginx
sudo tail -f /var/log/nginx/error.log
```

---

## 🧪 TESTING DI BROWSER

### Test 1: Akses Foto Langsung
Buka browser dan akses:
```
http://your-domain.com/storage/produk/nama-file.jpg
```

**Expected:**
- ✅ Foto muncul = Symlink berhasil
- ❌ 404 Not Found = Symlink belum dibuat / salah
- ❌ 403 Forbidden = Permissions salah

---

### Test 2: Cek Console Browser
1. Buka halaman Daftar Produk
2. Tekan F12 (Developer Tools)
3. Go to **Console** tab
4. Cek apakah ada error:
   - `GET http://domain.com/storage/produk/xxx.jpg 404` = File tidak ada
   - `GET http://domain.com/storage/produk/xxx.jpg 403` = Permissions salah

---

### Test 3: Inspect Element
1. Klik kanan pada foto produk yang tidak muncul
2. Pilih **Inspect Element**
3. Cek attribute `src`:
   ```html
   <img src="http://domain.com/storage/produk/xxx.jpg" ...>
   ```
4. Copy URL, buka di tab baru
5. Lihat error yang muncul

---

## 📊 VERIFIKASI DATABASE

Cek path foto di database:
```bash
php artisan tinker

# Cek 1 produk
$produk = \App\Models\Produk::first();
echo $produk->foto;
# Expected: produk/xyz123.jpg (tanpa 'storage/' di awal)

# Cek semua produk dengan foto
\App\Models\Produk::whereNotNull('foto')->get(['id', 'nama_produk', 'foto']);

exit
```

**Path yang Benar:**
- ✅ `produk/xyz123.jpg`
- ✅ `produk/subfolder/xyz123.jpg`
- ❌ `storage/produk/xyz123.jpg` (salah!)
- ❌ `/storage/produk/xyz123.jpg` (salah!)

---

## 🔄 MIGRASI FOTO DARI LOCAL KE SERVER

Jika foto ada di local tapi tidak ada di server:

### Option 1: Upload Manual via SCP/SFTP
```bash
# Dari komputer local (CMD/PowerShell)
scp -r C:\UMKM_COE\storage\app\public\produk user@server:/path/to/project/storage/app/public/

# Atau gunakan FileZilla/WinSCP:
# Local: C:\UMKM_COE\storage\app\public\produk\*
# Remote: /path/to/project/storage/app/public/produk/
```

### Option 2: Upload via MobaXterm
1. Open MobaXterm
2. Connect to SSH
3. Left panel shows local files
4. Right panel shows server files
5. Navigate to `storage/app/public/produk` on server
6. Drag & drop files from local to server

### Option 3: Compress and Upload
```bash
# Di local (Git Bash / WSL)
cd C:\UMKM_COE
tar -czf produk-photos.tar.gz storage/app/public/produk/

# Upload ke server via SCP
scp produk-photos.tar.gz user@server:/tmp/

# Di server (SSH)
cd /path/to/project
tar -xzf /tmp/produk-photos.tar.gz
rm /tmp/produk-photos.tar.gz

# Set permissions
chmod -R 775 storage/app/public/produk
chown -R www-data:www-data storage/app/public/produk
```

---

## ⚙️ CONFIGURASI .ENV (Optional)

Pastikan `.env` di server benar:
```env
APP_URL=http://your-domain.com
# ATAU
APP_URL=https://your-domain.com

FILESYSTEM_DISK=public
```

Setelah ubah .env:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🎯 CHECKLIST LENGKAP

Setelah semua step di atas, cek:

- [ ] `php artisan storage:link` berhasil (no error)
- [ ] `ls -la public/storage` menunjukkan symlink
- [ ] `ls -la storage/app/public/produk` menunjukkan file foto
- [ ] Permissions storage: `775` atau `777`
- [ ] Owner storage: `www-data` atau sesuai web server
- [ ] Browser bisa akses `http://domain.com/storage/produk/test.jpg`
- [ ] Halaman daftar produk menampilkan foto
- [ ] Upload foto baru berhasil dan langsung muncul
- [ ] F12 Console tidak ada error 404 atau 403

---

## 🆘 JIKA MASIH GAGAL

### Last Resort: Full Reset Storage
```bash
# BACKUP dulu!
cp -r storage/app/public storage_backup_$(date +%Y%m%d)

# Hapus symlink lama
rm -rf public/storage

# Buat ulang symlink
php artisan storage:link

# Set permissions ekstrim (hanya untuk testing!)
chmod -R 777 storage
chmod -R 777 public/storage

# Test upload via web interface
# Jika berhasil, kembalikan permissions ke 775
chmod -R 775 storage
chmod -R 775 public/storage
chown -R www-data:www-data storage public
```

---

## 📞 PERINTAH LENGKAP COPY-PASTE

Untuk kemudahan, ini full script yang bisa langsung di copy-paste di SSH:

```bash
# 1. Pastikan di root folder Laravel
cd /path/to/your/project  # GANTI dengan path Anda!

# 2. Buat symlink
rm -f public/storage
php artisan storage:link

# 3. Set permissions
sudo chown -R www-data:www-data storage public
sudo chmod -R 775 storage
sudo chmod -R 775 public/storage

# 4. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Verifikasi
ls -la public/storage
ls -la storage/app/public/produk

# 6. Test di browser
echo "Sekarang buka browser dan test upload foto!"
```

---

## ✨ SUCCESS INDICATORS

Anda berhasil jika:

1. ✅ Upload foto produk baru → Foto langsung muncul
2. ✅ Halaman daftar produk menampilkan semua foto
3. ✅ Akses `http://domain.com/storage/produk/test.jpg` → Foto muncul
4. ✅ F12 Console → Tidak ada error 404/403
5. ✅ `ls -la public/storage` → Menunjukkan symlink ke `storage/app/public`

---

## 📚 DOKUMENTASI LARAVEL

- Storage: https://laravel.com/docs/10.x/filesystem
- Symlink: https://laravel.com/docs/10.x/filesystem#the-public-disk
