# 🔧 PANDUAN FIX ERROR JABATAN DUPLICATE

## 🎯 MASALAH

Error saat simpan Kualifikasi Tenaga Kerja di hosting:

```
SQLSTATE[23000]: Integrity constraint violation: 1062 
Duplicate entry 'BT001' for key 'jabatans_kode_jabatan_unique'
```

**Penyebab:**
1. Unique constraint pada `kode_jabatan` tidak include `user_id`
2. User A sudah punya BT001, User B coba buat BT001 → ERROR!
3. Sistem generate kode tidak filter per user

---

## ✅ SOLUSI YANG SUDAH DIBUAT

### **1. Fix Controller** ✅

File: `app/Http/Controllers/JabatanController.php`

**Perubahan:**
```php
// SEBELUM (SALAH):
$lastJabatan = Jabatan::where('kode_jabatan', 'like', $prefix . '%')
    ->orderBy('kode_jabatan', 'desc')
    ->first();

// SESUDAH (BENAR):
$lastJabatan = Jabatan::where('kode_jabatan', 'like', $prefix . '%')
    ->where('user_id', auth()->id()) // Filter per user!
    ->orderBy('kode_jabatan', 'desc')
    ->first();
```

**Efek:**
- Setiap user generate kode dari data mereka sendiri
- User A: BT001, BT002, BT003
- User B: BT001, BT002, BT003 (tidak bentrok!)

---

### **2. Fix Database Constraint** ✅

File: `database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`

**Perubahan:**
```php
// SEBELUM (SALAH):
unique('kode_jabatan') // Global unique

// SESUDAH (BENAR):
unique(['kode_jabatan', 'user_id']) // Unique per user
```

**Efek:**
- Kode jabatan unique per user, bukan global
- User A bisa punya BT001
- User B juga bisa punya BT001
- Tidak ada conflict!

---

## 📋 LANGKAH-LANGKAH

### **STEP 1: Update Code di Local**

File sudah diupdate:
- ✅ `app/Http/Controllers/JabatanController.php`
- ✅ `database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`

---

### **STEP 2: Test di Local (Jika Database Bisa Konek)**

```bash
# Run migration
php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php

# Test buat jabatan
# Buka: http://localhost/UMKM_COE/public/master-data/kualifikasi-tenaga-kerja/create
# Isi form dan simpan
```

---

### **STEP 3: Deploy ke Hosting**

#### **A. Push Code ke Git:**

```bash
git add .
git commit -m "Fix: Jabatan unique constraint for multi-tenant"
git push origin main
```

Jenkins akan otomatis deploy.

---

#### **B. Run Migration di Hosting:**

**Via SSH:**

```bash
ssh user@your-hosting.com
cd /path/to/your/project

# Run migration
php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php
```

**Via cPanel Terminal:**

1. Login ke cPanel
2. Buka "Terminal"
3. Jalankan:
   ```bash
   cd public_html/your-project
   php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php
   ```

---

#### **C. Alternatif: Manual via phpMyAdmin**

Jika tidak bisa SSH, jalankan SQL manual:

```sql
-- 1. Drop unique constraint lama
ALTER TABLE jabatans DROP INDEX jabatans_kode_jabatan_unique;

-- 2. Tambah unique constraint baru (kode + user_id)
ALTER TABLE jabatans ADD UNIQUE KEY jabatans_kode_user_unique (kode_jabatan, user_id);
```

---

### **STEP 4: Test di Hosting**

1. Buka: `http://jobcost.eadtmanufaktur.com/master-data/kualifikasi-tenaga-kerja/create`
2. Isi form:
   - Nama: Pengemasan
   - Kategori: BTKL
   - Dll...
3. Klik "Simpan"
4. **Seharusnya berhasil tanpa error!**

---

## 🔍 VERIFIKASI

### **Cek Unique Constraint di Database:**

```sql
SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';
```

**Output yang benar:**
```
Key_name: jabatans_kode_user_unique
Column_name: kode_jabatan
Non_unique: 0
```

Dan juga:
```
Key_name: jabatans_kode_user_unique
Column_name: user_id
Non_unique: 0
```

---

### **Test dengan 2 User:**

**User A:**
1. Login sebagai User A
2. Buat jabatan: Pengemasan (BTKL)
3. Kode otomatis: BT001

**User B:**
1. Login sebagai User B
2. Buat jabatan: Pengemasan (BTKL)
3. Kode otomatis: BT001 (tidak error!)

**Cek Database:**
```sql
SELECT id, kode_jabatan, nama, user_id FROM jabatans WHERE kode_jabatan = 'BT001';
```

**Output:**
```
id | kode_jabatan | nama       | user_id
1  | BT001        | Pengemasan | 1
2  | BT001        | Pengemasan | 2
```

✅ Tidak ada conflict!

---

## ⚠️ TROUBLESHOOTING

### **Problem: Migration Error "Duplicate entry"**

**Penyebab:** Ada data duplicate di database

**Solusi:**

1. Cek data duplicate:
   ```sql
   SELECT kode_jabatan, user_id, COUNT(*) 
   FROM jabatans 
   GROUP BY kode_jabatan, user_id 
   HAVING COUNT(*) > 1;
   ```

2. Hapus duplicate (hati-hati!):
   ```sql
   -- Backup dulu!
   CREATE TABLE jabatans_backup AS SELECT * FROM jabatans;
   
   -- Hapus duplicate, keep yang pertama
   DELETE j1 FROM jabatans j1
   INNER JOIN jabatans j2 
   WHERE j1.id > j2.id 
   AND j1.kode_jabatan = j2.kode_jabatan 
   AND j1.user_id = j2.user_id;
   ```

3. Run migration lagi

---

### **Problem: Masih Error Setelah Migration**

**Penyebab:** Cache belum di-clear

**Solusi:**

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

### **Problem: User Lain Masih Error**

**Penyebab:** Controller belum di-deploy

**Solusi:**

1. Pastikan code sudah di-push ke Git
2. Jenkins sudah deploy
3. Clear cache di hosting

---

## 📊 PERBANDINGAN

### **Sebelum Fix:**

```
User A: BT001 ✅
User B: BT001 ❌ ERROR! (Duplicate)
```

**Unique Constraint:**
```sql
UNIQUE (kode_jabatan)
```

**Generate Kode:**
```php
// Ambil dari semua user
$lastJabatan = Jabatan::where('kode_jabatan', 'like', 'BT%')
    ->orderBy('kode_jabatan', 'desc')
    ->first();
```

---

### **Setelah Fix:**

```
User A: BT001 ✅
User B: BT001 ✅ (Tidak error!)
```

**Unique Constraint:**
```sql
UNIQUE (kode_jabatan, user_id)
```

**Generate Kode:**
```php
// Ambil dari user sendiri saja
$lastJabatan = Jabatan::where('kode_jabatan', 'like', 'BT%')
    ->where('user_id', auth()->id())
    ->orderBy('kode_jabatan', 'desc')
    ->first();
```

---

## ✅ CHECKLIST

- [ ] Code controller sudah diupdate
- [ ] Migration file sudah dibuat
- [ ] Code di-push ke Git
- [ ] Jenkins deploy berhasil
- [ ] Migration dijalankan di hosting
- [ ] Test buat jabatan di hosting
- [ ] Tidak ada error
- [ ] Test dengan 2 user berbeda
- [ ] Keduanya bisa buat BT001

---

## 🎉 HASIL AKHIR

**Sebelum:**
```
❌ Error: Duplicate entry 'BT001'
❌ User B tidak bisa buat jabatan
❌ Kode jabatan bentrok antar user
```

**Sesudah:**
```
✅ Tidak ada error
✅ Setiap user bisa buat jabatan
✅ Kode jabatan unique per user
✅ Multi-tenant isolation sempurna
```

---

*Panduan ini dibuat: 3 Mei 2026*
