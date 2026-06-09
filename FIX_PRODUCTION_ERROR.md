# 🔧 Fix Error Penggajian di Production

## ⚠️ Error yang Terjadi
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'total_jam_kerja' in 'INSERT INTO'
```

Error ini terjadi di **production server** (jobcost.eadtmanufaktur.com) karena database production belum diupdate dengan migration terbaru.

---

## ✅ Cara Memperbaiki (PILIH SALAH SATU)

### **Cara 1: Jalankan Migration (RECOMMENDED)**

Login ke server production via SSH atau terminal, kemudian:

```bash
# Masuk ke folder aplikasi
cd /path/to/aplikasi

# Jalankan migration
php artisan migrate

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### **Cara 2: Menggunakan Artisan Command Khusus**

Saya sudah buatkan command untuk check dan fix otomatis:

```bash
# Check apakah ada kolom yang missing
php artisan check:penggajian-table

# Jika ada kolom missing, fix otomatis:
php artisan check:penggajian-table --fix
```

### **Cara 3: Manual SQL di phpMyAdmin**

Jika tidak bisa akses terminal, buka **phpMyAdmin** di hosting dan jalankan SQL ini:

```sql
-- Check apakah kolom sudah ada
DESCRIBE penggajians;

-- Jika kolom total_jam_kerja tidak ada, jalankan:
ALTER TABLE `penggajians` 
ADD COLUMN `total_jam_kerja` DECIMAL(8, 2) NOT NULL DEFAULT 0.00 
COMMENT 'Total jam kerja (untuk sistem jam-based, 0 untuk produk-based)' 
AFTER `potongan`;
```

---

## 📋 Verifikasi Setelah Fix

1. Buka halaman **Tambah Penggajian** di browser
2. Isi form penggajian dan klik **Simpan**
3. Seharusnya tidak ada error lagi ✅

Atau verifikasi via SQL:

```sql
-- Pastikan kolom total_jam_kerja ada
SHOW COLUMNS FROM penggajians LIKE 'total_jam_kerja';

-- Output yang diharapkan:
-- Field: total_jam_kerja
-- Type: decimal(8,2)
-- Null: NO
-- Default: 0.00
```

---

## 📁 File yang Sudah Diperbaiki

Berikut file yang sudah saya perbaiki di code (sudah ready untuk di-push ke production):

### 1. Migration Baru
- **File:** `database/migrations/2026_06_10_000000_ensure_total_jam_kerja_exists.php`
- **Fungsi:** Memastikan kolom `total_jam_kerja` ada di tabel penggajians
- **Aman:** Bisa dijalankan berkali-kali tanpa error

### 2. Controller
- **File:** `app/Http/Controllers/PenggajianController.php`
- **Fix:** 
  - Sudah include `'total_jam_kerja' => 0` di Penggajian::create()
  - Fixed duplikasi `total_tunjangan`

### 3. Artisan Command
- **File:** `app/Console/Commands/CheckPenggajianTable.php`
- **Fungsi:** Command untuk check struktur tabel dan fix otomatis
- **Usage:** `php artisan check:penggajian-table --fix`

### 4. Documentation
- **File:** `DEPLOYMENT_FIX_PENGGAJIAN.md`
- **Fungsi:** Panduan lengkap deployment

---

## 🚀 Langkah Deploy ke Production

1. **Push code ke repository Git:**
   ```bash
   git add .
   git commit -m "Fix: Missing total_jam_kerja column in penggajians table"
   git push origin main
   ```

2. **Pull di production server:**
   ```bash
   cd /path/to/aplikasi
   git pull origin main
   ```

3. **Jalankan migration:**
   ```bash
   php artisan migrate
   ```

4. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

5. **Test di browser** ✅

---

## 💡 Catatan Penting

- Kolom `total_jam_kerja` digunakan untuk sistem lama (jam-based)
- Sistem baru (produk-based) tetap butuh kolom ini tapi isinya `0`
- Migration aman dijalankan karena ada pengecekan `Schema::hasColumn()`
- Jika sudah ada, migration akan skip tanpa error

---

## ❓ Troubleshooting

### Q: Migration gagal dengan error "Column already exists"
**A:** Tidak masalah! Artinya kolom sudah ada. Jalankan `php artisan migrate:status` untuk check status.

### Q: Masih error setelah migration
**A:** 
1. Clear cache: `php artisan config:clear && php artisan cache:clear`
2. Check apakah benar-benar sudah ada: `php artisan check:penggajian-table`
3. Restart web server (Apache/Nginx)

### Q: Tidak bisa akses terminal di hosting
**A:** Gunakan Cara 3 (Manual SQL di phpMyAdmin)

---

## ✨ Status

- [x] Migration dibuat
- [x] Controller diperbaiki  
- [x] Command helper dibuat
- [x] Documentation dibuat
- [ ] **Deploy ke production** ← Yang perlu Anda lakukan sekarang!

---

## 📞 Support

Jika masih ada error setelah mengikuti panduan ini, hubungi developer dengan info:
- Screenshot error terbaru
- Hasil `php artisan check:penggajian-table`
- Hasil `DESCRIBE penggajians;` dari phpMyAdmin
