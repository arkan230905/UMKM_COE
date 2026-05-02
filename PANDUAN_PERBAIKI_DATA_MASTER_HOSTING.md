# 🔧 PANDUAN PERBAIKI DATA MASTER DI HOSTING

## 🎯 MASALAH

Di hosting, halaman COA dan Satuan menampilkan **"🔒 Tidak dapat diubah"** untuk semua data.

**Penyebab:**
- Data COA dan Satuan di hosting masih menggunakan `user_id = NULL` (data master lama)
- View mengecek `user_id === null` untuk menampilkan lock icon
- Owner tidak bisa ubah/hapus data karena dianggap data master

**Yang Anda Inginkan:**
- Saat owner daftar → dapat data COA & Satuan sebagai starting point
- Owner bisa **ubah/hapus/tambah** data tersebut
- Perubahan hanya untuk perusahaan owner tersebut
- Data owner lain tidak terpengaruh

---

## ✅ SOLUSI

### **Konsep:**
1. Setiap user harus punya **COPY** data COA & Satuan dengan `user_id` mereka sendiri
2. Data master lama (`user_id = NULL`) tidak diperlukan lagi
3. User baru otomatis dapat copy data saat register (sudah ada di `CreateDefaultUserData` listener)

### **Yang Perlu Dilakukan:**
1. Copy data master ke user yang sudah ada
2. Hapus data master lama (opsional)
3. Deploy ke hosting

---

## 📋 LANGKAH-LANGKAH

### **STEP 1: Copy Data Master ke User yang Sudah Ada**

Di **local** (development), jalankan:

```bash
php copy_master_data_ke_user_existing.php
```

**Output yang diharapkan:**
```
==============================================
COPY DATA MASTER KE USER YANG SUDAH ADA
==============================================

Ditemukan 1 user yang belum punya data COA

==============================================
User: MUHAMMAD ARKAN ABIYYU (ID: 1)
Email: arkan@gmail.com
==============================================

Copying COA...
  ✅ 85 COA berhasil di-copy

Copying Satuan...
  ✅ 15 Satuan berhasil di-copy

==============================================
RINGKASAN
==============================================

Total user diproses: 1
Total COA di-copy: 85
Total Satuan di-copy: 15

🎉 BERHASIL!
```

---

### **STEP 2: Hapus Data Master Lama (OPSIONAL)**

Setelah semua user punya data sendiri, hapus data master lama:

```bash
php hapus_data_master_lama.php
```

**Konfirmasi:**
```
Data Master yang akan dihapus:
- COA: 50
- Satuan: 16

⚠️  PERINGATAN:
Script ini akan menghapus SEMUA data COA dan Satuan
yang memiliki user_id = NULL (data master).

Apakah Anda yakin ingin melanjutkan? (yes/no): yes
```

**Output:**
```
Menghapus COA master...
  ✅ 50 COA dihapus

Menghapus Satuan master...
  ✅ 16 Satuan dihapus

🎉 BERHASIL!
```

---

### **STEP 3: Test di Local**

1. Buka browser: `http://localhost/UMKM_COE/public`
2. Login sebagai owner (arkan@gmail.com)
3. Buka halaman:
   - `/master-data/coa`
   - `/master-data/satuan-dashboard`
4. **Cek:**
   - ❌ Tidak ada tulisan "🔒 Tidak dapat diubah"
   - ✅ Ada tombol Edit dan Hapus
   - ✅ Bisa tambah data baru
   - ✅ Bisa edit data existing
   - ✅ Bisa hapus data

---

### **STEP 4: Export Database**

Setelah test berhasil di local, export database:

```bash
php perbaiki_foreign_key_sebelum_export.php
```

Lalu export dari phpMyAdmin dengan:
- ✅ Centang "Disable foreign key checks"
- ✅ Centang "Add DROP TABLE"
- ✅ Centang "Add IF NOT EXISTS"

---

### **STEP 5: Deploy ke Hosting**

#### **A. Upload File SQL ke Hosting**

1. Login ke cPanel / hosting panel
2. Buka phpMyAdmin
3. **BACKUP database lama dulu!**
4. Drop database lama
5. Buat database baru
6. Import file SQL yang baru

#### **B. Upload Code via Jenkins**

1. Commit & push code ke repository:
   ```bash
   git add .
   git commit -m "Fix: Copy master data to users, remove master data lock"
   git push origin main
   ```

2. Jenkins akan otomatis deploy ke MobaXterm

#### **C. Verify di Hosting**

1. Buka website hosting
2. Login sebagai owner
3. Cek halaman COA dan Satuan
4. **Pastikan:**
   - Tidak ada "🔒 Tidak dapat diubah"
   - Bisa edit/hapus/tambah data

---

## 🔍 TROUBLESHOOTING

### **Problem: Masih Ada "Tidak dapat diubah"**

**Penyebab:** Data di database masih punya `user_id = NULL`

**Solusi:**
1. Cek database di hosting:
   ```sql
   SELECT user_id, COUNT(*) FROM coas GROUP BY user_id;
   SELECT user_id, COUNT(*) FROM satuans GROUP BY user_id;
   ```

2. Jika masih ada `user_id = NULL`, jalankan script di hosting:
   ```bash
   php copy_master_data_ke_user_existing.php
   php hapus_data_master_lama.php
   ```

---

### **Problem: User Baru Tidak Dapat Data COA/Satuan**

**Penyebab:** Event listener tidak jalan

**Solusi:**
1. Cek file `app/Providers/EventServiceProvider.php`:
   ```php
   'App\Events\UserRegistered' => [
       'App\Listeners\CreateDefaultUserData',
   ],
   ```

2. Clear cache:
   ```bash
   php artisan event:clear
   php artisan cache:clear
   php artisan config:clear
   ```

3. Test register user baru

---

### **Problem: Data User A Terlihat oleh User B**

**Penyebab:** Global scope tidak jalan

**Solusi:**
1. Cek model `Coa` dan `Satuan` punya global scope:
   ```php
   static::addGlobalScope('user', function ($builder) {
       if (auth()->check()) {
           $builder->where('user_id', auth()->id());
       }
   });
   ```

2. Test dengan 2 user berbeda

---

## 📊 VERIFIKASI AKHIR

### **Checklist:**

- [ ] Script `copy_master_data_ke_user_existing.php` berhasil dijalankan
- [ ] Script `hapus_data_master_lama.php` berhasil dijalankan (opsional)
- [ ] Test di local: tidak ada "Tidak dapat diubah"
- [ ] Test di local: bisa edit/hapus/tambah data
- [ ] Database di-export dengan benar
- [ ] Code di-push ke repository
- [ ] Jenkins deploy berhasil
- [ ] Test di hosting: tidak ada "Tidak dapat diubah"
- [ ] Test di hosting: bisa edit/hapus/tambah data
- [ ] Test dengan 2 user berbeda: data terisolasi

---

## 🎯 HASIL AKHIR

**Sebelum:**
```
COA (user_id = NULL) → 🔒 Tidak dapat diubah
Satuan (user_id = NULL) → 🔒 Tidak dapat diubah
```

**Sesudah:**
```
COA (user_id = 1) → ✅ Bisa diubah oleh User 1
COA (user_id = 2) → ✅ Bisa diubah oleh User 2
Satuan (user_id = 1) → ✅ Bisa diubah oleh User 1
Satuan (user_id = 2) → ✅ Bisa diubah oleh User 2
```

**Benefit:**
- ✅ Setiap owner punya data COA & Satuan sendiri
- ✅ Owner bisa ubah/hapus/tambah data
- ✅ Perubahan hanya untuk perusahaan owner tersebut
- ✅ Data owner lain tidak terpengaruh
- ✅ User baru otomatis dapat data saat register

---

## 📞 BANTUAN

Jika masih ada masalah:
1. Screenshot halaman COA/Satuan
2. Cek database: `SELECT * FROM coas WHERE user_id IS NULL LIMIT 5;`
3. Cek log Laravel: `storage/logs/laravel.log`

---

*Panduan ini dibuat: 3 Mei 2026*
