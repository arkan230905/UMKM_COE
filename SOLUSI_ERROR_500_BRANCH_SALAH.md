# 🔧 SOLUSI ERROR 500 - BRANCH SALAH

**Masalah Ditemukan:** ✅  
Anda push ke branch `arkan`, tapi Jenkins deploy dari branch `main`!

---

## 🎯 MASALAH

```
Current Branch: arkan ✅ (sudah push)
Jenkins Deploy: main ❌ (belum ada perubahan)
```

**Akibat:**
- Code di branch `arkan` sudah benar
- Tapi hosting masih pakai code dari branch `main` (lama)
- Makanya error 500 (file `DefaultCoaSeederBaru` tidak ada di hosting)

---

## ✅ SOLUSI: MERGE KE MAIN

### **STEP 1: Merge Branch Arkan ke Main**

```bash
# 1. Pindah ke branch main
git checkout main

# 2. Pull perubahan terbaru dari main
git pull origin main

# 3. Merge branch arkan ke main
git merge arkan

# 4. Push ke origin main
git push origin main
```

### **STEP 2: Tunggu Jenkins Deploy**

Jenkins akan otomatis detect perubahan di branch `main` dan deploy.

Tunggu 1-2 menit sampai Jenkins selesai.

### **STEP 3: Clear Cache di Hosting**

```bash
# Via SSH
ssh user@jobcost.eadtmanufaktur.com
cd /path/to/your/project

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear

# Regenerate autoload
composer dump-autoload
```

### **STEP 4: Test Lagi**

Buka: http://jobcost.eadtmanufaktur.com/master-data/coa

Seharusnya sudah bisa! ✅

---

## 📋 COMMAND LENGKAP (COPY-PASTE)

```bash
# Di local (Git Bash / Terminal)
git checkout main
git pull origin main
git merge arkan
git push origin main

# Tunggu Jenkins selesai (1-2 menit)

# Di hosting (SSH)
ssh user@jobcost.eadtmanufaktur.com
cd /path/to/your/project
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan optimize:clear
composer dump-autoload

# Test
# Buka: http://jobcost.eadtmanufaktur.com/master-data/coa
```

---

## ⚠️ KALAU ADA CONFLICT SAAT MERGE

Kalau muncul conflict saat `git merge arkan`:

```bash
# 1. Lihat file yang conflict
git status

# 2. Buka file yang conflict dengan text editor
# Cari tanda: <<<<<<< HEAD, =======, >>>>>>> arkan

# 3. Edit file, hapus tanda conflict, pilih code yang benar

# 4. Add file yang sudah di-resolve
git add .

# 5. Commit merge
git commit -m "Merge branch arkan to main"

# 6. Push
git push origin main
```

---

## 🔍 VERIFIKASI

### **Cek Branch Main Sudah Terupdate:**

```bash
# Di local
git checkout main
git log --oneline -1

# Output harus:
# 73aa851 Fix: Jabatan duplicate + Update listener COA Jasuke
```

### **Cek File Ada di Hosting:**

```bash
# Via SSH
ssh user@jobcost.eadtmanufaktur.com
cd /path/to/your/project

# Cek file ada
ls -la database/seeders/DefaultCoaSeederBaru.php

# Output harus:
# -rw-r--r-- 1 user user 12345 May 3 12:00 DefaultCoaSeederBaru.php
```

### **Cek Listener Terupdate:**

```bash
# Via SSH
cat app/Listeners/CreateDefaultUserData.php | grep DefaultCoaSeederBaru

# Output harus:
# use Database\Seeders\DefaultCoaSeederBaru;
# $coaSeeder = new DefaultCoaSeederBaru();
```

---

## 🎉 HASIL AKHIR

Setelah merge ke main dan Jenkins deploy:

- ✅ File `DefaultCoaSeederBaru.php` ada di hosting
- ✅ Listener pakai `DefaultCoaSeederBaru`
- ✅ Halaman COA bisa dibuka
- ✅ User baru dapat 50 COA + 16 Satuan

---

## 📝 CATATAN PENTING

**Untuk ke depannya:**

1. **Selalu merge ke main sebelum deploy:**
   ```bash
   git checkout main
   git merge arkan
   git push origin main
   ```

2. **Atau push langsung ke main:**
   ```bash
   git checkout main
   git add .
   git commit -m "Your message"
   git push origin main
   ```

3. **Cek Jenkins deploy dari branch mana:**
   - Buka Jenkins
   - Lihat konfigurasi
   - Pastikan deploy dari branch `main`

---

**SEKARANG MERGE KE MAIN DAN DEPLOY LAGI! 🚀**

*Solusi dibuat: 3 Mei 2026*
