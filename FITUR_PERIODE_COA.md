# Fitur Periode Bulanan COA

## Deskripsi
Fitur ini menambahkan sistem periode bulanan untuk Chart of Accounts (COA) yang memungkinkan:
- Pemilihan periode bulanan di Neraca Saldo
- Saldo awal setiap periode berasal dari saldo akhir periode sebelumnya
- Posting saldo akhir periode untuk menutup periode dan memindahkan ke periode berikutnya
- Membuka kembali periode yang sudah ditutup (jika periode berikutnya belum ditutup)

## File yang Dibuat/Dimodifikasi

### File Baru:
1. **database/migrations/2024_01_15_000001_create_coa_periods_table.php**
   - Tabel untuk menyimpan periode bulanan

2. **database/migrations/2024_01_15_000002_create_coa_period_balances_table.php**
   - Tabel untuk menyimpan saldo awal dan akhir per COA per periode

3. **app/Models/CoaPeriod.php**
   - Model untuk periode bulanan
   - Method untuk get/create periode
   - Method untuk navigasi periode (previous/next)

4. **app/Models/CoaPeriodBalance.php**
   - Model untuk saldo periode per COA

5. **app/Http/Controllers/CoaPeriodController.php**
   - Controller untuk posting dan membuka periode
   - Method untuk menghitung saldo akhir

6. **database/seeders/CoaPeriodSeeder.php**
   - Seeder untuk inisialisasi periode (6 bulan ke belakang, 6 bulan ke depan)

### File yang Dimodifikasi:
1. **app/Http/Controllers/AkuntansiController.php**
   - Update method `neracaSaldo()` untuk mendukung periode
   - Tambah method `getSaldoAwalPeriode()` untuk get saldo awal

2. **app/Models/Coa.php**
   - Tambah relasi `periodBalances()`
   - Tambah method `getSaldoPeriode()`

3. **resources/views/akuntansi/neraca-saldo.blade.php**
   - Tambah dropdown pemilihan periode
   - Tambah tombol "Post Saldo Akhir" dan "Buka Periode"
   - Tambah kolom saldo awal dan saldo akhir
   - Tambah informasi status periode (Ditutup/Aktif)

4. **routes/web.php**
   - Tambah route untuk posting periode
   - Tambah route untuk membuka periode

## Cara Penggunaan

### 1. Melihat Neraca Saldo per Periode
- Buka menu **Akuntansi > Neraca Saldo**
- Pilih periode dari dropdown
- Sistem akan menampilkan:
  - Saldo awal (dari periode sebelumnya atau saldo awal COA)
  - Mutasi debit dan kredit dalam periode
  - Saldo akhir

### 2. Menutup Periode (Post Saldo Akhir)
- Pilih periode yang ingin ditutup
- Klik tombol **"Post Saldo Akhir"**
- Sistem akan:
  - Menghitung saldo akhir semua akun
  - Menyimpan saldo akhir ke database
  - Memindahkan saldo akhir menjadi saldo awal periode berikutnya
  - Menandai periode sebagai ditutup

### 3. Membuka Kembali Periode
- Pilih periode yang sudah ditutup
- Klik tombol **"Buka Periode"**
- Sistem akan membuka periode (hanya jika periode berikutnya belum ditutup)

## Struktur Database

### Tabel: coa_periods
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| periode | varchar(7) | Format: YYYY-MM |
| tanggal_mulai | date | Tanggal mulai periode |
| tanggal_selesai | date | Tanggal selesai periode |
| is_closed | boolean | Status periode ditutup |
| closed_at | timestamp | Waktu penutupan |
| closed_by | bigint | User yang menutup |

### Tabel: coa_period_balances
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| kode_akun | varchar(50) | Foreign key ke coas |
| period_id | bigint | Foreign key ke coa_periods |
| saldo_awal | decimal(15,2) | Saldo awal periode |
| saldo_akhir | decimal(15,2) | Saldo akhir periode |
| is_posted | boolean | Status sudah diposting |

## Alur Kerja

1. **Periode Pertama**
   - Saldo awal diambil dari kolom `saldo_awal` di tabel `coas`

2. **Periode Berikutnya**
   - Saldo awal = Saldo akhir periode sebelumnya
   - Saldo akhir = Saldo awal + Mutasi (debit - kredit atau kredit - debit tergantung saldo normal)

3. **Posting Periode**
   - Hitung saldo akhir semua akun
   - Simpan ke `coa_period_balances`
   - Buat saldo awal untuk periode berikutnya
   - Tandai periode sebagai ditutup

## Catatan Penting

⚠️ **Data Existing Tidak Terpengaruh**
- Semua data COA, jurnal, dan transaksi existing tetap aman
- Fitur ini hanya menambahkan layer periode di atas data yang ada
- Tidak ada perubahan pada tabel `coas` atau `jurnal_umum`

⚠️ **Periode Harus Ditutup Berurutan**
- Periode hanya bisa dibuka jika periode berikutnya belum ditutup
- Ini untuk menjaga integritas saldo antar periode

⚠️ **Inisialisasi Periode**
- Jalankan seeder untuk membuat periode awal: `php artisan db:seed --class=CoaPeriodSeeder`
- Seeder akan membuat 12 periode (6 bulan ke belakang, 6 bulan ke depan)

## Artisan Commands

### 1. Membuat Periode Baru
```bash
# Buat periode untuk bulan depan
php artisan coa:create-period

# Buat periode untuk 3 bulan ke depan
php artisan coa:create-period --months=3

# Buat periode spesifik
php artisan coa:create-period 2025-12
```

### 2. Posting Periode
```bash
# Post periode bulan lalu
php artisan coa:post-period

# Post periode spesifik
php artisan coa:post-period 2025-11
```

### 3. Inisialisasi Periode (Seeder)
```bash
php artisan db:seed --class=CoaPeriodSeeder
```

### 4. Test Fitur
```bash
php test_periode_coa.php
```

## Testing

Untuk test fitur ini:
1. Akses halaman Neraca Saldo
2. Pilih periode dari dropdown
3. Verifikasi saldo awal, mutasi, dan saldo akhir
4. Test posting periode
5. Verifikasi saldo awal periode berikutnya sama dengan saldo akhir periode sebelumnya
