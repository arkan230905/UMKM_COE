# Instruksi Perbaikan Multi-Tenant

## Status Perbaikan

### ✅ SELESAI
1. **COA** - Semua 4 user sudah punya 50 COA (format Jasuke) yang bisa diedit
2. **Listener** - `CreateDefaultUserData.php` sudah diupdate untuk user baru

### ⚠️ PERLU DIJALANKAN DI HOSTING

## 1. Fix Satuan (Users 2, 3, 4 hanya punya 4 unit, harusnya 16)

### Langkah-langkah:

```bash
# 1. Upload script ke hosting
scp insert_satuan_kurang.php simcost@103.134.154.77:/home/simcost/

# 2. SSH ke hosting
ssh simcost@103.134.154.77

# 3. Copy script ke folder project
sudo cp /home/simcost/insert_satuan_kurang.php /var/www/html/

# 4. Jalankan script
cd /var/www/html
php insert_satuan_kurang.php

# 5. Verifikasi hasil
mysql -u root -p eadt_umkm -e "SELECT user_id, COUNT(*) as total FROM satuans GROUP BY user_id;"
```

**Expected Result:**
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

**16 Satuan yang lengkap:**
1. ONS (Ons)
2. KG (Kilogram)
3. ML (Mililiter)
4. G (Gram)
5. LTR (Liter)
6. PTG (Potong)
7. EKOR (Ekor)
8. SDT (Sendok Teh)
9. SDM (Sendok Makan)
10. PCS (Pieces)
11. BNGKS (Bungkus)
12. CUP (Cup)
13. GL (Galon)
14. TBG (Tabung)
15. SNG (Siung)
16. KLG (Kaleng)

---

## 2. Fix Aset Duplicate Entry Error

### Error yang terjadi:
```
Duplicate entry 'AST-202605-0001' for key 'asets_kode_aset_unique'
```

### Root Cause:
- Unique constraint pada `kode_aset` tidak include `user_id`
- Method `generateKodeAset()` tidak filter by `user_id`

### Perbaikan yang sudah dilakukan:
1. ✅ Update `app/Models/Aset.php` - method `generateKodeAset()` sekarang filter by `user_id`
2. ✅ Buat migration `database/migrations/2026_05_03_130000_fix_asets_unique_constraint_multi_tenant.php`

### Langkah-langkah di Hosting:

```bash
# 1. SSH ke hosting
ssh simcost@103.134.154.77

# 2. Pull code terbaru dari GitHub (setelah merge)
cd /var/www/html
git pull origin main

# 3. Jalankan migration
php artisan migrate --path=database/migrations/2026_05_03_130000_fix_asets_unique_constraint_multi_tenant.php

# 4. Verifikasi constraint
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM asets WHERE Column_name = 'kode_aset';"
```

**Expected Result:**
```
+-------+------------+-----------------------+--------------+-------------+-----------+-------------+----------+--------+------+------------+---------+---------------+---------+
| Table | Non_unique | Key_name              | Seq_in_index | Column_name | Collation | Cardinality | Sub_part | Packed | Null | Index_type | Comment | Index_comment | Ignored |
+-------+------------+-----------------------+--------------+-------------+-----------+-------------+----------+--------+------+------------+---------+---------------+---------+
| asets |          0 | asets_kode_user_unique|            1 | kode_aset   | A         |        NULL |     NULL | NULL   | YES  | BTREE      |         |               | NO      |
| asets |          0 | asets_kode_user_unique|            2 | user_id     | A         |        NULL |     NULL | NULL   | YES  | BTREE      |         |               | NO      |
+-------+------------+-----------------------+--------------+-------------+-----------+-------------+----------+--------+------+------------+---------+---------------+---------+
```

**Test:**
- Coba tambah aset dengan user yang berbeda
- Seharusnya bisa menggunakan kode yang sama (misal: AST-202605-0001) untuk user berbeda

---

## 3. Fix Jabatan Duplicate Entry Error (MASIH PENDING)

### Error yang sama seperti Aset:
```
Duplicate entry 'BT001' for key 'jabatans_kode_jabatan_unique'
```

### Perbaikan yang sudah dilakukan:
1. ✅ Update `app/Http/Controllers/JabatanController.php` line 77-79
2. ✅ Buat migration `database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`

### Langkah-langkah di Hosting:

```bash
# 1. SSH ke hosting
ssh simcost@103.134.154.77

# 2. Pull code terbaru (setelah merge)
cd /var/www/html
git pull origin main

# 3. Jalankan migration
php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php

# 4. Verifikasi constraint
mysql -u root -p eadt_umkm -e "SHOW INDEX FROM jabatans WHERE Column_name = 'kode_jabatan';"
```

**Expected Result:**
```
+---------+------------+-------------------------------+--------------+---------------+-----------+-------------+----------+--------+------+------------+---------+---------------+---------+
| Table   | Non_unique | Key_name                      | Seq_in_index | Column_name   | Collation | Cardinality | Sub_part | Packed | Null | Index_type | Comment | Index_comment | Ignored |
+---------+------------+-------------------------------+--------------+---------------+-----------+-------------+----------+--------+------+------------+---------+---------------+---------+
| jabatans|          0 | jabatans_kode_user_unique     |            1 | kode_jabatan  | A         |        NULL |     NULL | NULL   | YES  | BTREE      |         |               | NO      |
| jabatans|          0 | jabatans_kode_user_unique     |            2 | user_id       | A         |        NULL |     NULL | NULL   | YES  | BTREE      |         |               | NO      |
+---------+------------+-------------------------------+--------------+---------------+-----------+-------------+----------+--------+------+------------+---------+---------------+---------+
```

---

## Ringkasan Perubahan Code

### Files yang diubah:
1. `app/Models/Aset.php` - Add `->where('user_id', auth()->id())` di `generateKodeAset()`
2. `database/seeders/DefaultSatuanSeeder.php` - Add 'KLG' (Kaleng) jadi 16 unit
3. `database/migrations/2026_05_03_130000_fix_asets_unique_constraint_multi_tenant.php` - NEW
4. `insert_satuan_kurang.php` - NEW (script untuk hosting)

### Files yang sudah diubah sebelumnya (sudah di hosting):
1. `app/Http/Controllers/JabatanController.php` - Add `->where('user_id', auth()->id())`
2. `app/Listeners/CreateDefaultUserData.php` - Use `DefaultCoaSeederBaru`
3. `database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`

---

## Checklist Deployment

### Sebelum Deploy:
- [ ] Merge branch `main` dengan remote (resolve conflicts jika ada)
- [ ] Push ke GitHub
- [ ] Tunggu Jenkins deploy (atau manual pull di hosting)

### Di Hosting:
- [ ] Run migration Jabatan: `php artisan migrate --path=database/migrations/2026_05_03_120000_fix_jabatans_unique_constraint_multi_tenant.php`
- [ ] Run migration Aset: `php artisan migrate --path=database/migrations/2026_05_03_130000_fix_asets_unique_constraint_multi_tenant.php`
- [ ] Run script Satuan: `php insert_satuan_kurang.php`
- [ ] Verify Satuan count: `mysql -u root -p eadt_umkm -e "SELECT user_id, COUNT(*) as total FROM satuans GROUP BY user_id;"`

### Testing:
- [ ] Test tambah Jabatan dengan multiple users
- [ ] Test tambah Aset dengan multiple users
- [ ] Test halaman Satuan - harusnya ada 16 unit dan bisa diedit
- [ ] Test halaman COA - harusnya ada 50 akun dan bisa diedit

---

## Catatan Penting

1. **Git Conflict**: Branch local dan remote sudah diverge (1 vs 179 commits). Perlu resolve dulu sebelum push.
2. **Master Data**: Data dengan `user_id = NULL` tidak bisa dihapus karena foreign key constraint dari `bahan_konversi`.
3. **Multi-Tenant**: Semua unique constraint sekarang include `user_id` untuk isolasi data antar user.
4. **New Users**: User baru akan otomatis dapat 50 COA dan 16 Satuan saat registrasi.

---

## Troubleshooting

### Jika masih ada error "Tidak dapat diubah":
- Cek `user_id` di database: `SELECT id, kode, nama, user_id FROM satuans WHERE user_id = X;`
- Pastikan data bukan master data (`user_id IS NOT NULL`)

### Jika masih ada duplicate entry error:
- Cek unique constraint: `SHOW INDEX FROM [table_name];`
- Pastikan constraint include `user_id`
- Cek method generate kode filter by `user_id`

### Jika migration gagal:
- Cek apakah constraint lama masih ada
- Drop manual: `ALTER TABLE [table] DROP INDEX [old_constraint];`
- Add manual: `ALTER TABLE [table] ADD UNIQUE KEY [new_constraint] (kode, user_id);`
