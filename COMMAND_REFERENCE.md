# Command Reference - Quick Guide

## 🚀 Langkah Cepat untuk Fix Semua Masalah

### 1️⃣ Upload Script Satuan ke Hosting
```bash
scp insert_satuan_kurang.php simcost@103.134.154.77:/home/simcost/
```

### 2️⃣ SSH ke Hosting
```bash
ssh simcost@103.134.154.77
```

### 3️⃣ Jalankan Semua Perbaikan (Copy-Paste Semua)
```bash
# Copy script ke project folder
sudo cp /home/simcost/insert_satuan_kurang.php /var/www/html/

# Masuk ke project folder
cd /var/www/html

# Pull code terbaru (setelah merge di GitHub)
git pull origin main

# Jalankan migration Jabatan
php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php

# Jalankan migration Aset
php artisan migrate --path=database/migrations/2026_05_03_130000_fix_asets_unique_constraint_multi_tenant.php

# Jalankan script Satuan
php insert_satuan_kurang.php

# Verifikasi Satuan
mysql -u root -p eadt_umkm -e "SELECT user_id, COUNT(*) as total FROM satuans GROUP BY user_id;"

# Verifikasi constraint Jabatan
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';"

# Verifikasi constraint Aset
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM asets WHERE Column_name = 'kode_aset';"
```

---

## 📊 Expected Results

### Satuan Count
```
+---------+-------+
| user_id | total |
+---------+-------+
|    NULL |     4 |
|       1 |    16 |
|       2 |    16 |
|       3 |    16 |
|       4 |    16 |
+---------+-------+
```

### Jabatan Constraint
Harus ada `jabatans_kode_user_unique` dengan 2 columns: `kode_jabatan` dan `user_id`

### Aset Constraint
Harus ada `asets_kode_user_unique` dengan 2 columns: `kode_aset` dan `user_id`

---

## 🔧 Troubleshooting Commands

### Jika migration gagal - Manual Fix Jabatan
```bash
mysql -u root -p eadt_umkm
```
```sql
-- Drop old constraint
ALTER TABLE jabatans DROP INDEX jabatans_kode_jabatan_unique;

-- Add new constraint
ALTER TABLE jabatans ADD UNIQUE KEY jabatans_kode_user_unique (kode_jabatan, user_id);

-- Verify
SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';
```

### Jika migration gagal - Manual Fix Aset
```bash
mysql -u root -p eadt_umkm
```
```sql
-- Drop old constraint
ALTER TABLE asets DROP INDEX asets_kode_aset_unique;

-- Add new constraint
ALTER TABLE asets ADD UNIQUE KEY asets_kode_user_unique (kode_aset, user_id);

-- Verify
SHOW INDEX FROM asets WHERE Column_name = 'kode_aset';
```

### Cek Detail Satuan per User
```bash
mysql -u root -p eadt_umkm -e "SELECT id, kode, nama, user_id FROM satuans WHERE user_id = 2 ORDER BY kode;"
mysql -u root -p eadt_umkm -e "SELECT id, kode, nama, user_id FROM satuans WHERE user_id = 3 ORDER BY kode;"
mysql -u root -p eadt_umkm -e "SELECT id, kode, nama, user_id FROM satuans WHERE user_id = 4 ORDER BY kode;"
```

### Cek Log Laravel
```bash
tail -f /var/www/html/storage/logs/laravel.log
```

---

## ⚠️ SEBELUM JALANKAN DI HOSTING

### Di Local (Windows):
```powershell
# Resolve git conflict dulu
git pull origin main
# atau
git push origin main --force

# Tunggu Jenkins deploy atau manual pull di hosting
```

---

## ✅ Testing Checklist

Setelah semua command dijalankan, test:

1. **Jabatan**: Login sebagai user berbeda, coba tambah jabatan dengan kode sama
2. **Aset**: Login sebagai user berbeda, coba tambah aset dengan kode sama
3. **Satuan**: Buka halaman Satuan, harusnya ada 16 unit dan bisa diedit
4. **COA**: Buka halaman COA, harusnya ada 50 akun dan bisa diedit

---

**Password MySQL**: (tanya user jika lupa)
