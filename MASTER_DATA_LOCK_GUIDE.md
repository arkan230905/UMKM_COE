# Panduan Mengunci Data Master untuk Owner Baru

## 📋 Deskripsi

Sistem ini memungkinkan Anda untuk **mengunci data yang saat ini ada di database** sebagai **data master/template** yang akan otomatis di-copy ke setiap owner baru yang mendaftar.

### ✅ Yang Akan Terjadi:
- Data Anda saat ini akan di-export sebagai "master data"
- Setiap owner baru yang register akan mendapatkan **copy lengkap** dari data master Anda
- **TIDAK ADA** data yang ditambah atau dikurangi - semuanya persis sama
- Data master terkunci dan tidak akan berubah kecuali Anda export ulang

### 🔒 Data Yang Akan Di-Lock:

1. **Master Data Global:**
   - Satuan (unit pengukuran)
   - Jabatan (kualifikasi tenaga kerja)

2. **Master Data Perusahaan:**
   - COA (Chart of Accounts / Akun)
   - Jenis Aset & Kategori Aset
   - Supplier
   - Pelanggan
   - Bahan Baku
   - Bahan Pendukung
   - Produk
   - BOP (Biaya Overhead Pabrik)
   - Pegawai

3. **Data Transaksi (Opsional):**
   - Aset
   - Pembelian
   - Penjualan
   - Proses Produksi
   - *Secara default TIDAK di-export, bisa diaktifkan jika diperlukan*

---

## 🚀 Cara Menggunakan

### Langkah 1: Export Data Master Anda

Jalankan command berikut untuk mengunci data Anda saat ini:

```bash
php artisan master:export
```

**Atau jika perusahaan Anda bukan ID 1:**

```bash
php artisan master:export --perusahaan_id=YOUR_COMPANY_ID
```

**Output yang akan muncul:**
```
Exporting master data dari perusahaan ID: 1
Perusahaan: Nama Perusahaan Anda
Exporting table: satuan
  ✓ 15 records exported
Exporting table: jabatan
  ✓ 8 records exported
Exporting table: coa
  ✓ 120 records exported
...

============================================================
✓ Export completed!
Total tables: 12
Total records: 450
Saved to: database/seeders/master_data/master_data_2026_04_17_143022.json
Latest: database/seeders/master_data/master_data_latest.json
============================================================
```

### Langkah 2: Verifikasi File Master Data

Pastikan file berhasil dibuat:

```bash
ls -la database/seeders/master_data/
```

Anda akan melihat:
- `master_data_latest.json` - File yang akan digunakan sistem
- `master_data_YYYY_MM_DD_HHMMSS.json` - Backup dengan timestamp

### Langkah 3: Selesai! 🎉

Sistem sudah siap! Setiap owner baru yang register akan otomatis mendapatkan copy dari data master Anda.

---

## 🔄 Cara Kerja Sistem

### Saat Owner Baru Register:

1. **User mengisi form registrasi** → Membuat akun owner baru
2. **Sistem membuat perusahaan baru** → Perusahaan dengan ID unik
3. **Event `UserRegistered` dipicu** → Otomatis
4. **Listener `SetupUserData` berjalan** → Otomatis
5. **`MasterDataSeeder` meng-copy semua data master** → Otomatis
   - Membaca file `master_data_latest.json`
   - Meng-copy semua data ke perusahaan baru
   - Menjaga relasi antar data (foreign keys)
   - Set `perusahaan_id` ke perusahaan baru
6. **Owner baru login dengan data lengkap** → Siap pakai!

### Flow Diagram:

```
Owner Baru Register
        ↓
Buat User & Perusahaan
        ↓
Trigger Event: UserRegistered
        ↓
Listener: SetupUserData
        ↓
MasterDataSeeder::seedForCompany()
        ↓
Copy Semua Data Master
        ↓
Owner Baru Punya Data Lengkap ✓
```

---

## 🔧 Konfigurasi Lanjutan

### Menambah/Mengurangi Tabel Yang Di-Export

Edit file: `app/Console/Commands/ExportMasterData.php`

Cari bagian `protected $tables`:

```php
protected $tables = [
    // Master data global
    'satuan',
    'jabatan',
    
    // Master data perusahaan
    'coa',
    'jenis_aset',
    'kategori_aset',
    'supplier',
    'pelanggan',
    'bahan_baku',
    'bahan_pendukung',
    'produk',
    'bop',
    'pegawai',
    
    // Tambahkan tabel lain di sini jika diperlukan
    // 'nama_tabel_baru',
];
```

**⚠️ PENTING:**
- Urutan tabel harus sesuai dengan foreign key dependencies
- Tabel parent harus di atas tabel child
- Contoh: `coa` harus di atas `aset` (karena aset punya `coa_id`)

### Meng-Include Data Transaksi

Jika Anda ingin owner baru juga mendapat copy data transaksi (sebagai contoh):

1. Edit `app/Console/Commands/ExportMasterData.php`
2. Uncomment tabel transaksi:

```php
protected $tables = [
    // ... master data ...
    
    // Data transaksi (uncomment jika diperlukan)
    'aset',
    'pembelian',
    'penjualan',
    'proses_produksi',
    'jurnal_umum',
    'stock_movement',
];
```

3. Export ulang: `php artisan master:export`

---

## 🔄 Update Data Master

### Kapan Perlu Update?

Update data master jika:
- Anda menambah produk baru yang ingin jadi default
- Anda menambah COA baru yang ingin jadi default
- Anda mengubah struktur data master
- Anda ingin owner baru dapat data terbaru

### Cara Update:

```bash
# Export ulang data master Anda
php artisan master:export

# File master_data_latest.json akan ter-update otomatis
# Owner baru yang register setelah ini akan dapat data terbaru
```

**⚠️ CATATAN:**
- Owner yang sudah register sebelumnya **TIDAK** akan ter-update
- Hanya owner baru yang register setelah export ulang yang dapat data terbaru

---

## 🧪 Testing

### Test Manual:

1. **Export data master:**
   ```bash
   php artisan master:export
   ```

2. **Buat user test:**
   ```bash
   php artisan test:user-registration
   ```

3. **Login sebagai user test dan cek:**
   - Apakah semua COA ada?
   - Apakah semua produk ada?
   - Apakah semua supplier ada?
   - Apakah semua data lengkap?

### Test Otomatis:

Buat test user baru melalui form registrasi dan verifikasi datanya lengkap.

---

## 📊 Monitoring

### Cek Log:

```bash
tail -f storage/logs/laravel.log
```

Cari log:
- `Setting up master data for new owner`
- `Master data setup completed for new owner`
- `Failed to setup master data` (jika ada error)

### Cek Database:

```sql
-- Cek perusahaan baru
SELECT * FROM perusahaan ORDER BY id DESC LIMIT 1;

-- Cek COA perusahaan baru (ganti 999 dengan perusahaan_id)
SELECT COUNT(*) FROM coa WHERE perusahaan_id = 999;

-- Cek produk perusahaan baru
SELECT COUNT(*) FROM produk WHERE perusahaan_id = 999;
```

---

## ⚠️ PENTING - Hal Yang TIDAK BOLEH Dilakukan

### ❌ JANGAN:

1. **JANGAN edit file `master_data_latest.json` secara manual**
   - Selalu gunakan command `php artisan master:export`
   - Edit manual bisa merusak struktur data

2. **JANGAN hapus file `master_data_latest.json`**
   - Jika terhapus, owner baru tidak akan dapat data
   - Export ulang jika terhapus

3. **JANGAN ubah urutan tabel di `$tables` array**
   - Urutan penting untuk foreign key
   - Bisa menyebabkan error saat seeding

4. **JANGAN export saat ada transaksi berjalan**
   - Export saat sistem tidak sibuk
   - Hindari export saat jam kerja

### ✅ LAKUKAN:

1. **Backup database sebelum export pertama kali**
2. **Test dengan user dummy sebelum production**
3. **Export ulang secara berkala** (misal: setiap bulan)
4. **Monitor log setelah owner baru register**

---

## 🆘 Troubleshooting

### Problem: "Master data file not found"

**Solusi:**
```bash
php artisan master:export
```

### Problem: "Failed to parse master data JSON"

**Solusi:**
1. Cek file `database/seeders/master_data/master_data_latest.json`
2. Pastikan file valid JSON
3. Export ulang jika corrupt

### Problem: Owner baru tidak dapat data

**Solusi:**
1. Cek log: `tail -f storage/logs/laravel.log`
2. Cek apakah file master data ada
3. Cek apakah listener terdaftar di `EventServiceProvider`
4. Test manual: `php artisan test:user-registration`

### Problem: Error foreign key saat seeding

**Solusi:**
1. Cek urutan tabel di `$tables` array
2. Pastikan tabel parent di atas tabel child
3. Update `updateForeignKeys()` method di `MasterDataSeeder`

### Problem: Data tidak lengkap

**Solusi:**
1. Cek tabel mana yang kurang di `$tables` array
2. Tambahkan tabel yang kurang
3. Export ulang: `php artisan master:export`

---

## 📝 Catatan Teknis

### File-File Penting:

1. **`app/Console/Commands/ExportMasterData.php`**
   - Command untuk export data master
   - Konfigurasi tabel yang di-export

2. **`database/seeders/MasterDataSeeder.php`**
   - Seeder yang meng-copy data master ke perusahaan baru
   - Handle foreign key mapping

3. **`app/Listeners/SetupUserData.php`**
   - Listener yang dipanggil saat owner baru register
   - Memanggil MasterDataSeeder

4. **`database/seeders/master_data/master_data_latest.json`**
   - File JSON berisi data master yang terkunci
   - Di-generate oleh command export

### Database Schema:

Sistem ini bekerja dengan asumsi:
- Tabel yang punya `perusahaan_id` akan di-filter per perusahaan
- Tabel yang tidak punya `perusahaan_id` adalah data global
- Foreign key akan di-map otomatis ke ID baru

---

## 📞 Support

Jika ada masalah atau pertanyaan:

1. Cek log di `storage/logs/laravel.log`
2. Cek dokumentasi ini
3. Test dengan user dummy terlebih dahulu
4. Backup database sebelum melakukan perubahan

---

## ✅ Checklist Implementasi

- [ ] Export data master: `php artisan master:export`
- [ ] Verifikasi file `master_data_latest.json` ada
- [ ] Test dengan user dummy
- [ ] Verifikasi data lengkap di user dummy
- [ ] Monitor log saat owner baru register
- [ ] Backup database
- [ ] Dokumentasikan untuk tim

---

**Dibuat:** 17 April 2026
**Versi:** 1.0.0
**Status:** Production Ready ✅
