# Checklist Hosting - UMKM COE

## ✅ Persiapan Sebelum Hosting

### 1. Database Migration
```bash
# Di server hosting (MobaXterm)
php artisan migrate --force
```

**PENTING:** Migration sudah diverifikasi dan aman untuk dijalankan!

### 2. Seeder yang Harus Dijalankan

#### A. Seeder Otomatis (Dipanggil saat user register)
- `DefaultCoaSeeder` - Membuat 51 COA default untuk user baru
- Dipanggil otomatis oleh listener `CreateDefaultUserData`

#### B. Seeder Manual (Untuk user yang sudah ada)
Jika ada user yang sudah terdaftar sebelum seeder dibuat, jalankan:

```bash
# Buat COA untuk user existing
php artisan tinker
```

Kemudian jalankan:
```php
$user = User::where('email', 'admin@example.com')->first();
$seeder = new \Database\Seeders\DefaultCoaSeeder();
$seeder->run($user->id);
```

Atau gunakan script helper:
```bash
php seed_coa_for_existing_user.php
php seed_satuan_for_user.php
```

### 3. Struktur Tabel yang Benar

#### Tabel `jabatans`
Kolom yang ADA:
- `id`
- `nama`
- `kategori` (btkl/btktl)
- `kode_jabatan`
- `gaji_pokok`
- `tarif` ← **PENTING: Ini tarif per PRODUK, bukan per jam**
- `tunjangan`
- `tunjangan_transport`
- `tunjangan_konsumsi`
- `asuransi`
- `user_id`

Kolom yang TIDAK ADA:
- ❌ `tarif_per_jam` (TIDAK DIGUNAKAN)
- ❌ `tarif_produk` (TIDAK DIGUNAKAN)

#### Tabel `coas`
Total: **51 COA** per user
- Aset: 22 akun
- Kewajiban: 4 akun
- Modal: 3 akun
- Pendapatan: 3 akun
- Biaya: 19 akun

COA untuk penggajian yang HARUS ada:
- 52 - BTKL
- 54 - Beban Sewa
- 513 - Beban Tunjangan
- 514 - Beban Asuransi
- 515 - Beban Bonus
- 516 - Potongan Gaji
- 111 - Kas Bank
- 112 - Kas

#### Tabel `satuans`
Total: **16 Satuan** per user
- ONS, KG, ML, G, LTR, PTG, EKOR, SDT, SDM, PCS, BNGKS, CUP, GL, TBG, SNG, KLG

#### Tabel `kategori_bahan_pendukung`
**PENTING:** Harus memiliki kolom `user_id`
- Script `setup_hosting.php` akan otomatis menambahkan jika belum ada
- Default kategori: Gas, Bumbu, Minyak, Air, Listrik, Kemasan, Lainnya

### 4. File .env untuk Hosting

```env
APP_NAME="UMKM COE"
APP_ENV=production
APP_KEY=base64:... # Generate dengan: php artisan key:generate
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=database

# Mail (opsional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Permissions untuk Hosting

```bash
# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Atau jika menggunakan user lain
chown -R your_user:your_user storage bootstrap/cache
```

### 6. Optimize untuk Production

```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## 🔍 Verifikasi Setelah Deploy

### 1. Test Login
- Email: admin@example.com
- Password: password
- Role: owner

### 2. Test Fitur Utama
- ✅ COA: Harus ada 51 akun
- ✅ Satuan: Harus ada 16 satuan
- ✅ Tambah Jabatan: Tidak ada error tarif_per_jam
- ✅ Bahan Baku: Stock update berfungsi
- ✅ Laporan Posisi Keuangan: Laba/Rugi Berjalan benar

### 3. Check Database
```sql
-- Cek jumlah COA
SELECT COUNT(*) FROM coas WHERE user_id = 1;
-- Harus: 51

-- Cek jumlah Satuan
SELECT COUNT(*) FROM satuans WHERE user_id = 1;
-- Harus: 16

-- Cek struktur tabel jabatans
DESCRIBE jabatans;
-- Harus ada: tarif (bukan tarif_per_jam)
```

## ⚠️ Troubleshooting

### Error: Column 'tarif_per_jam' not found
**Solusi:** Migration sudah diperbaiki. Jalankan ulang:
```bash
php artisan migrate:fresh --force
```

### COA Kosong
**Solusi:** Jalankan seeder manual untuk user existing

### Satuan Kosong
**Solusi:** Jalankan seeder manual untuk user existing

### Stock Tidak Update
**Solusi:** Sudah diperbaiki di controller. Pastikan menggunakan versi terbaru.

### Error: Column 'user_id' not found in kategori_bahan_pendukung
**Solusi:** Jalankan `php setup_hosting.php` yang akan otomatis menambahkan kolom user_id

## 📝 Catatan Penting

1. **Tarif System**: Aplikasi menggunakan **tarif per PRODUK**, bukan per jam
2. **Multi-Tenant**: Semua data di-filter berdasarkan `user_id`
3. **Stock System**: Menggunakan `StockMovement` untuk tracking real-time
4. **COA System**: Setiap user memiliki COA sendiri (51 akun default)
5. **Satuan System**: Setiap user memiliki satuan sendiri (16 satuan default)

## 🎯 Checklist Final

- [ ] Migration berhasil tanpa error
- [ ] Seeder COA dijalankan untuk user existing
- [ ] Seeder Satuan dijalankan untuk user existing
- [ ] Test login berhasil
- [ ] Test tambah jabatan berhasil (tidak ada error tarif_per_jam)
- [ ] Test tambah bahan baku berhasil
- [ ] Test stock update berhasil
- [ ] Laporan keuangan tampil dengan benar
- [ ] Permissions sudah di-set
- [ ] Cache sudah di-optimize
- [ ] .env sudah di-configure untuk production

---

**Versi:** 1.0
**Tanggal:** 13 Mei 2026
**Status:** ✅ SIAP HOSTING
