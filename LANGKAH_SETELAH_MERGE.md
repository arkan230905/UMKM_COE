# ✅ LANGKAH SETELAH MERGE KE MAIN

**Status:** Sudah merged ke main 5 menit yang lalu ✅  
**Next:** Tunggu Jenkins → Clear Cache → Test

---

## 📋 LANGKAH-LANGKAH

### **STEP 1: Tunggu Jenkins Selesai Deploy** ⏳

Jenkins butuh waktu 1-2 menit untuk deploy.

**Cara cek Jenkins:**
1. Buka: http://103.134.154.77:8080/job/Update-Web-Simcost/
2. Lihat build terakhir
3. Tunggu sampai status: ✅ Success (warna hijau)

**Kalau sudah success, lanjut ke STEP 2.**

---

### **STEP 2: Clear Cache di Hosting** 🧹

#### **Opsi A: Via SSH (Otomatis dengan Script)**

```bash
# Login SSH
ssh user@jobcost.eadtmanufaktur.com

# Masuk ke folder project
cd /path/to/your/project

# Upload script (atau copy-paste manual)
# File: clear_cache_hosting.sh

# Jalankan script
bash clear_cache_hosting.sh
```

#### **Opsi B: Via SSH (Manual)**

```bash
# Login SSH
ssh user@jobcost.eadtmanufaktur.com

# Masuk ke folder project
cd /path/to/your/project

# Clear cache satu per satu
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Regenerate autoload
composer dump-autoload -o

# Cek file ada
ls -la database/seeders/DefaultCoaSeederBaru.php

# Cek listener
grep "DefaultCoaSeederBaru" app/Listeners/CreateDefaultUserData.php
```

#### **Opsi C: Via cPanel Terminal**

1. Login cPanel
2. Klik "Terminal"
3. Jalankan command:
   ```bash
   cd public_html/your-project
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan optimize:clear
   composer dump-autoload -o
   ```

---

### **STEP 3: Test di Hosting** 🧪

#### **Test 1: Halaman COA**

1. Buka: http://jobcost.eadtmanufaktur.com/master-data/coa
2. **Expected:** ✅ Halaman bisa dibuka (tidak error 500)
3. **Kalau masih error 500:** Lanjut ke Troubleshooting

#### **Test 2: Buat Jabatan**

1. Login ke hosting
2. Buka: http://jobcost.eadtmanufaktur.com/master-data/kualifikasi-tenaga-kerja/create
3. Isi form:
   - Nama: Pengemasan
   - Kategori: BTKL
   - Tarif: 20000
4. Klik Simpan
5. **Expected:** ✅ Berhasil tanpa error

#### **Test 3: Registrasi User Baru**

1. Logout
2. Buka: http://jobcost.eadtmanufaktur.com/register
3. Daftar user baru
4. Login
5. Cek COA: **Expected:** ✅ Ada 50 COA
6. Cek Satuan: **Expected:** ✅ Ada 16 Satuan

---

## 🔍 VERIFIKASI

### **Cek File Ada di Hosting:**

```bash
# Via SSH
ls -la database/seeders/DefaultCoaSeederBaru.php

# Output yang benar:
# -rw-r--r-- 1 user user 12345 May 3 12:00 DefaultCoaSeederBaru.php
```

### **Cek Listener Terupdate:**

```bash
# Via SSH
cat app/Listeners/CreateDefaultUserData.php | grep DefaultCoaSeederBaru

# Output yang benar:
# use Database\Seeders\DefaultCoaSeederBaru;
# $coaSeeder = new DefaultCoaSeederBaru();
```

### **Cek Log Error (Kalau Masih Error):**

```bash
# Via SSH
tail -20 storage/logs/laravel.log

# Kirim output ke saya kalau masih error
```

---

## ⚠️ TROUBLESHOOTING

### **Problem 1: Masih Error 500 Setelah Clear Cache**

**Kemungkinan:** File belum ter-deploy

**Solusi:**

1. Cek Jenkins build terakhir:
   - Buka: http://103.134.154.77:8080/job/Update-Web-Simcost/
   - Klik build terakhir
   - Lihat "Console Output"
   - Cari error

2. Cek file di hosting:
   ```bash
   ls -la database/seeders/DefaultCoaSeederBaru.php
   ```
   
3. Kalau file tidak ada, upload manual:
   - Via cPanel File Manager
   - Upload dari local ke `database/seeders/`

4. Clear cache lagi:
   ```bash
   php artisan cache:clear
   composer dump-autoload -o
   ```

---

### **Problem 2: Jenkins Build Failed**

**Kemungkinan:** Conflict atau error saat deploy

**Solusi:**

1. Cek Console Output di Jenkins
2. Cari error message
3. Fix error di local
4. Commit & push lagi
5. Tunggu Jenkins deploy lagi

---

### **Problem 3: File Ada Tapi Masih Error**

**Kemungkinan:** Autoload belum di-regenerate

**Solusi:**

```bash
# Via SSH
cd /path/to/your/project

# Regenerate autoload dengan optimize
composer dump-autoload -o

# Clear cache lagi
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear

# Restart PHP-FPM (kalau ada akses)
sudo systemctl restart php8.3-fpm
```

---

## 📊 TIMELINE

```
✅ 5 menit lalu: Merged ke main
⏳ Sekarang: Tunggu Jenkins (1-2 menit)
🧹 Nanti: Clear cache di hosting
🧪 Terakhir: Test halaman COA
```

---

## 🎯 CHECKLIST

- [x] Merge branch arkan ke main
- [x] Push ke origin main
- [ ] Tunggu Jenkins selesai deploy
- [ ] Clear cache di hosting
- [ ] Regenerate autoload
- [ ] Test halaman COA
- [ ] Test buat jabatan
- [ ] Test registrasi user baru

---

## 📞 KALAU MASIH ERROR

Kirim ke saya:

1. **Screenshot error** (kalau masih error 500)
2. **Output log error:**
   ```bash
   tail -20 storage/logs/laravel.log
   ```
3. **Output cek file:**
   ```bash
   ls -la database/seeders/DefaultCoaSeederBaru.php
   ```
4. **Output Jenkins Console** (kalau build failed)

---

**SEKARANG TUNGGU JENKINS SELESAI, LALU CLEAR CACHE! 🚀**

*Panduan dibuat: 3 Mei 2026*
