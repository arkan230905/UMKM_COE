# ✅ CHECKLIST DEPLOY CEPAT

**Copy checklist ini dan centang satu per satu!**

---

## 🔧 PRE-DEPLOY

- [ ] Baca `RINGKASAN_SIAP_DEPLOY.txt`
- [ ] Baca `PANDUAN_DEPLOY_LENGKAP_KE_HOSTING.md`
- [ ] Pastikan Jenkins running
- [ ] Pastikan punya akses SSH/phpMyAdmin

---

## 📦 DEPLOY CODE

- [ ] `git add .`
- [ ] `git commit -m "Fix: Jabatan duplicate + Update listener COA Jasuke"`
- [ ] `git push origin main`
- [ ] Tunggu Jenkins selesai deploy (cek status)
- [ ] Verifikasi file terupdate di hosting (via MobaXterm/FTP)

---

## 🗄️ DEPLOY DATABASE

### Opsi A: Via SSH/Terminal

- [ ] Login SSH: `ssh user@jobcost.eadtmanufaktur.com`
- [ ] Masuk folder: `cd /path/to/project`
- [ ] Run migration:
  ```bash
  php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php
  ```
- [ ] Lihat output: "Migrated: 2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant"

### Opsi B: Via phpMyAdmin

- [ ] Login phpMyAdmin
- [ ] Pilih database: `eadt_umkm`
- [ ] Klik tab "SQL"
- [ ] Copy-paste SQL ini:
  ```sql
  ALTER TABLE jabatans DROP INDEX jabatans_kode_jabatan_unique;
  ALTER TABLE jabatans ADD UNIQUE KEY jabatans_kode_user_unique (kode_jabatan, user_id);
  ```
- [ ] Klik "Go"
- [ ] Lihat pesan: "Query OK"

---

## 🧹 CLEAR CACHE

- [ ] `php artisan cache:clear`
- [ ] `php artisan config:clear`
- [ ] `php artisan route:clear`
- [ ] `php artisan view:clear`

---

## 🧪 TEST JABATAN (USER LAMA)

- [ ] Login sebagai user yang sudah ada (user_id = 4)
- [ ] Buka: `http://jobcost.eadtmanufaktur.com/master-data/kualifikasi-tenaga-kerja/create`
- [ ] Isi form:
  - Nama: **Pengemasan**
  - Kategori: **BTKL**
  - Tarif: **20000**
  - Tunjangan Transport: **150000**
  - Tunjangan Konsumsi: **375000**
  - Asuransi: **100000**
- [ ] Klik **Simpan**
- [ ] **HASIL:** ✅ Berhasil tanpa error!
- [ ] Lihat kode jabatan: **BT001**

---

## 🧪 TEST JABATAN (USER BERBEDA)

- [ ] Logout
- [ ] Login sebagai user lain (user_id = 5 atau buat user baru)
- [ ] Buka: `http://jobcost.eadtmanufaktur.com/master-data/kualifikasi-tenaga-kerja/create`
- [ ] Isi form yang sama:
  - Nama: **Pengemasan**
  - Kategori: **BTKL**
- [ ] Klik **Simpan**
- [ ] **HASIL:** ✅ Berhasil! (Kode BT001 untuk user ini)
- [ ] Tidak ada error duplicate!

---

## 🧪 TEST USER BARU (REGISTRASI)

- [ ] Logout
- [ ] Buka: `http://jobcost.eadtmanufaktur.com/register`
- [ ] Daftar user baru:
  - Nama: **Test User**
  - Email: **test@example.com**
  - Password: **password123**
- [ ] Login dengan user baru
- [ ] Buka: `http://jobcost.eadtmanufaktur.com/master-data/coa`
- [ ] **HASIL:** ✅ Ada 50 COA (format Jasuke)
- [ ] Cek ada: Pers. Bahan Baku Jagung, Pers. Bahan Pendukung Susu, dll
- [ ] Buka: `http://jobcost.eadtmanufaktur.com/master-data/satuan-dashboard`
- [ ] **HASIL:** ✅ Ada 16 Satuan
- [ ] Cek ada: KLG (Kaleng), ONS, KG, ML, G, LTR, PTG, EKOR, SDT, SDM, PCS, BNGKS, CUP, GL, TBG, SNG

---

## 🔍 VERIFIKASI DATABASE

### Cek Unique Constraint:

- [ ] Login phpMyAdmin
- [ ] Pilih database: `eadt_umkm`
- [ ] Klik tab "SQL"
- [ ] Run query:
  ```sql
  SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';
  ```
- [ ] **HASIL:** Lihat `Key_name: jabatans_kode_user_unique`

### Cek Data User Baru:

- [ ] Run query:
  ```sql
  SELECT id, name, email FROM users ORDER BY id DESC LIMIT 1;
  ```
- [ ] Catat user_id (misal: 10)
- [ ] Run query:
  ```sql
  SELECT COUNT(*) as total_coa FROM coas WHERE user_id = 10;
  ```
- [ ] **HASIL:** 50
- [ ] Run query:
  ```sql
  SELECT COUNT(*) as total_satuan FROM satuans WHERE user_id = 10;
  ```
- [ ] **HASIL:** 16

### Cek Jabatan Multi-User:

- [ ] Run query:
  ```sql
  SELECT kode_jabatan, COUNT(DISTINCT user_id) as jumlah_user
  FROM jabatans
  GROUP BY kode_jabatan
  HAVING jumlah_user > 1;
  ```
- [ ] **HASIL:** Ada BT001 dengan jumlah_user > 1 (NORMAL!)

---

## 🎉 SELESAI!

- [ ] Semua test berhasil
- [ ] Tidak ada error
- [ ] User bisa buat jabatan
- [ ] User baru dapat data lengkap
- [ ] Multi-tenant isolation sempurna

---

## ⚠️ JIKA ADA MASALAH

### Error saat migration:

- [ ] Cek log: `tail -f storage/logs/laravel.log`
- [ ] Cek duplicate data (lihat PANDUAN_DEPLOY_LENGKAP_KE_HOSTING.md)
- [ ] Backup database dulu sebelum hapus duplicate

### User baru tidak dapat COA/Satuan:

- [ ] Cek log error
- [ ] Manual run seeder (lihat PANDUAN_DEPLOY_LENGKAP_KE_HOSTING.md)
- [ ] Cek listener terdaftar di `app/Providers/EventServiceProvider.php`

### Masih error setelah deploy:

- [ ] Clear cache lagi
- [ ] Restart PHP-FPM (jika ada akses)
- [ ] Cek file controller sudah terupdate di hosting

---

**DEPLOY BERHASIL! 🚀**

*Checklist ini dibuat: 3 Mei 2026*
