# Simulasi Penggajian Lengkap - UMKM COE

## Overview
Dokumentasi lengkap simulasi penggajian dari master data hingga laporan akhir. Simulasi ini mencakup:
- Master data pegawai (BTKL & BTKTL)
- Data presensi otomatis
- Perhitungan penggajian
- Jurnal akuntansi
- Laporan penggajian

---

## 1. Master Data Pegawai

### Data yang Dibuat
Simulasi membuat 6 pegawai dengan komposisi:

#### BTKL (Biaya Tenaga Kerja Langsung) - 3 Pegawai
Pegawai produksi yang gajinya dihitung berdasarkan jam kerja.

| No | Nama | Jabatan | Tarif/Jam | Tunjangan | Asuransi | Bank |
|----|------|---------|-----------|-----------|----------|------|
| 1 | Budi Santoso | Operator Produksi | Rp 50.000 | Rp 500.000 | Rp 200.000 | BCA |
| 2 | Siti Nurhaliza | Operator Mesin | Rp 45.000 | Rp 400.000 | Rp 150.000 | BCA |
| 3 | Ahmad Wijaya | Helper Produksi | Rp 35.000 | Rp 300.000 | Rp 100.000 | Mandiri |

**Formula BTKL:**
```
Total Gaji = (Tarif per Jam × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan
```

#### BTKTL (Biaya Tenaga Kerja Tidak Langsung) - 3 Pegawai
Pegawai admin/support yang gajinya tetap per bulan.

| No | Nama | Jabatan | Gaji Pokok | Tunjangan | Asuransi | Bank |
|----|------|---------|-----------|-----------|----------|------|
| 1 | Ani Wijayanti | Staff Admin | Rp 5.000.000 | Rp 1.000.000 | Rp 300.000 | BCA |
| 2 | Rudi Hermawan | Kepala Gudang | Rp 6.000.000 | Rp 1.500.000 | Rp 400.000 | Mandiri |
| 3 | Eka Putri Lestari | Finance Officer | Rp 7.000.000 | Rp 2.000.000 | Rp 500.000 | BCA |

**Formula BTKTL:**
```
Total Gaji = Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan
```

---

## 2. Data Presensi

### Periode
- **Bulan**: Bulan berjalan (saat seeder dijalankan)
- **Cakupan**: Semua hari kerja (Senin-Jumat)
- **Pegawai**: Hanya BTKL yang memiliki presensi

### Distribusi Status Presensi
- **80%** Hadir (8 jam kerja)
- **10%** Sakit (0 jam)
- **10%** Izin (0 jam)

### Contoh Perhitungan Jam Kerja
Jika dalam bulan ada 22 hari kerja:
- Hadir 18 hari × 8 jam = 144 jam
- Sakit 2 hari × 0 jam = 0 jam
- Izin 2 hari × 0 jam = 0 jam
- **Total Jam Kerja = 144 jam**

---

## 3. Perhitungan Penggajian

### Contoh 1: BTKL (Budi Santoso)
```
Tarif per Jam        : Rp 50.000
Jam Kerja (bulan)    : 160 jam
Gaji Dasar           : 160 × Rp 50.000 = Rp 8.000.000

Tunjangan            : Rp 500.000
Asuransi             : Rp 200.000
Bonus (random)       : Rp 500.000 (50% chance)
Potongan (random)    : Rp 100.000 (50% chance)

Total Gaji           : Rp 8.000.000 + Rp 500.000 + Rp 200.000 + Rp 500.000 - Rp 100.000
                     = Rp 9.100.000
```

### Contoh 2: BTKTL (Ani Wijayanti)
```
Gaji Pokok           : Rp 5.000.000
Tunjangan            : Rp 1.000.000
Asuransi             : Rp 300.000
Bonus (random)       : Rp 1.000.000 (50% chance)
Potongan (random)    : Rp 200.000 (50% chance)

Total Gaji           : Rp 5.000.000 + Rp 1.000.000 + Rp 300.000 + Rp 1.000.000 - Rp 200.000
                     = Rp 7.100.000
```

---

## 4. Jurnal Akuntansi Otomatis

Setiap penggajian akan membuat jurnal entry otomatis:

### Jurnal Entry Penggajian
```
Tanggal: [Tanggal Penggajian]
Deskripsi: Penggajian - [Nama Pegawai]

Debit  : Beban Gaji (501)              Rp [Total Gaji]
Kredit : Kas/Bank (101)                Rp [Total Gaji]
```

### Contoh Jurnal untuk Budi Santoso
```
Debit  : Beban Gaji (501)              Rp 9.100.000
Kredit : Kas/Bank (101)                Rp 9.100.000
```

---

## 5. Alur Penggajian Lengkap

```
┌─────────────────────────────────────┐
│  1. Master Data Pegawai             │
│     - Nama, Jabatan, Gaji, Bank     │
│     - Jenis: BTKL atau BTKTL        │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  2. Input Presensi (BTKL)           │
│     - Tanggal, Jam Masuk/Keluar     │
│     - Status: Hadir/Sakit/Izin      │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  3. Hitung Total Jam Kerja          │
│     - Sum presensi per bulan        │
│     - Hanya untuk BTKL              │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  4. Proses Penggajian               │
│     - Hitung gaji berdasarkan jenis │
│     - Validasi saldo kas            │
│     - Input bonus & potongan        │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  5. Buat Jurnal Otomatis            │
│     - Debit: Beban Gaji (501)       │
│     - Kredit: Kas/Bank (101)        │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  6. Update Saldo COA                │
│     - Kas/Bank berkurang            │
│     - Beban Gaji bertambah          │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  7. Laporan Penggajian              │
│     - Slip gaji per pegawai         │
│     - Rekap penggajian bulanan      │
│     - Export PDF/Excel              │
└─────────────────────────────────────┘
```

---

## 6. Cara Menjalankan Simulasi

### Opsi 1: Jalankan Seeder Langsung
```bash
# Jalankan seeder simulasi penggajian
php artisan db:seed --class=SimulasiPenggajianSeeder

# Output:
# ✓ Simulasi penggajian lengkap berhasil dibuat!
```

### Opsi 2: Jalankan Semua Seeder
```bash
# Jalankan semua seeder termasuk simulasi
php artisan db:seed

# Output:
# Seeding: Database\Seeders\UserSeeder
# Seeded:  Database\Seeders\UserSeeder (XXXms)
# Seeding: Database\Seeders\SimulasiPenggajianSeeder
# ✓ Simulasi penggajian lengkap berhasil dibuat!
```

### Opsi 3: Fresh Database + Seeder
```bash
# Hapus semua data dan jalankan seeder dari awal
php artisan migrate:fresh --seed

# Atau hanya seeder tertentu
php artisan migrate:fresh --seed --seeder=SimulasiPenggajianSeeder
```

---

## 7. Verifikasi Data

### Cek Data Pegawai
```bash
# Via Laravel Tinker
php artisan tinker

# Lihat semua pegawai
>>> App\Models\Pegawai::all();

# Lihat pegawai BTKL
>>> App\Models\Pegawai::where('jenis_pegawai', 'btkl')->get();

# Lihat pegawai BTKTL
>>> App\Models\Pegawai::where('jenis_pegawai', 'btktl')->get();
```

### Cek Data Presensi
```bash
# Via Laravel Tinker
>>> App\Models\Presensi::whereMonth('tgl_presensi', now()->month)->count();

# Lihat presensi pegawai tertentu
>>> App\Models\Presensi::where('pegawai_id', 1)->get();

# Total jam kerja pegawai
>>> App\Models\Presensi::where('pegawai_id', 1)->sum('jumlah_jam');
```

### Cek Data Penggajian
```bash
# Via Laravel Tinker
>>> App\Models\Penggajian::all();

# Lihat penggajian pegawai tertentu
>>> App\Models\Penggajian::where('pegawai_id', 1)->get();

# Total penggajian bulan ini
>>> App\Models\Penggajian::sum('total_gaji');
```

---

## 8. Akses Melalui UI

### Menu Penggajian
1. **Master Data > Pegawai**
   - Lihat daftar semua pegawai
   - Edit data pegawai jika diperlukan
   - Lihat detail pegawai

2. **Master Data > Presensi**
   - Lihat daftar presensi
   - Filter berdasarkan pegawai & tanggal
   - Edit presensi jika diperlukan

3. **Transaksi > Penggajian**
   - Lihat daftar penggajian
   - Tambah penggajian baru
   - Edit penggajian
   - Lihat detail penggajian
   - Cetak slip gaji (PDF)

### Laporan
1. **Laporan > Jurnal Umum**
   - Lihat jurnal penggajian yang dibuat
   - Filter berdasarkan tanggal & akun

2. **Laporan > Buku Besar**
   - Lihat saldo akun Beban Gaji (501)
   - Lihat saldo akun Kas/Bank (101)

---

## 9. Rincian Data Simulasi

### Total Komponen Gaji (Per Bulan)

#### BTKL (Asumsi 160 jam kerja)
```
Budi Santoso:
  Gaji Dasar    : Rp 8.000.000 (160 jam × Rp 50.000)
  Tunjangan     : Rp 500.000
  Asuransi      : Rp 200.000
  Subtotal      : Rp 8.700.000
  Bonus (50%)   : Rp 500.000
  Potongan (50%): Rp 100.000
  Total         : Rp 9.100.000

Siti Nurhaliza:
  Gaji Dasar    : Rp 7.200.000 (160 jam × Rp 45.000)
  Tunjangan     : Rp 400.000
  Asuransi      : Rp 150.000
  Subtotal      : Rp 7.750.000
  Bonus (50%)   : Rp 500.000
  Potongan (50%): Rp 100.000
  Total         : Rp 8.150.000

Ahmad Wijaya:
  Gaji Dasar    : Rp 5.600.000 (160 jam × Rp 35.000)
  Tunjangan     : Rp 300.000
  Asuransi      : Rp 100.000
  Subtotal      : Rp 6.000.000
  Bonus (50%)   : Rp 500.000
  Potongan (50%): Rp 100.000
  Total         : Rp 6.400.000

Total BTKL    : Rp 23.650.000
```

#### BTKTL
```
Ani Wijayanti:
  Gaji Pokok    : Rp 5.000.000
  Tunjangan     : Rp 1.000.000
  Asuransi      : Rp 300.000
  Subtotal      : Rp 6.300.000
  Bonus (50%)   : Rp 1.000.000
  Potongan (50%): Rp 200.000
  Total         : Rp 7.100.000

Rudi Hermawan:
  Gaji Pokok    : Rp 6.000.000
  Tunjangan     : Rp 1.500.000
  Asuransi      : Rp 400.000
  Subtotal      : Rp 7.900.000
  Bonus (50%)   : Rp 1.000.000
  Potongan (50%): Rp 200.000
  Total         : Rp 8.700.000

Eka Putri Lestari:
  Gaji Pokok    : Rp 7.000.000
  Tunjangan     : Rp 2.000.000
  Asuransi      : Rp 500.000
  Subtotal      : Rp 9.500.000
  Bonus (50%)   : Rp 1.000.000
  Potongan (50%): Rp 200.000
  Total         : Rp 10.300.000

Total BTKTL   : Rp 26.100.000
```

#### Grand Total Penggajian
```
Total BTKL    : Rp 23.650.000
Total BTKTL   : Rp 26.100.000
─────────────────────────────
GRAND TOTAL   : Rp 49.750.000
```

---

## 10. Troubleshooting

### Error: "Akun kas (101) tidak ditemukan"
**Solusi:**
- Pastikan COA sudah di-seed terlebih dahulu
- Jalankan: `php artisan db:seed --class=CoaSeeder`
- Atau jalankan: `php artisan migrate:fresh --seed`

### Error: "Saldo kas tidak mencukupi"
**Solusi:**
- Pastikan saldo kas (101) cukup untuk penggajian
- Tambah saldo kas melalui transaksi penerimaan kas
- Atau kurangi nominal penggajian

### Data Presensi Kosong
**Solusi:**
- Pastikan seeder dijalankan dalam bulan yang sama
- Cek tanggal sistem
- Jalankan ulang seeder

### Jurnal Tidak Terbuat
**Solusi:**
- Pastikan JournalService sudah ada
- Cek log di `storage/logs/laravel.log`
- Jalankan: `php artisan queue:work` jika menggunakan queue

---

## 11. File-File Terkait

### Seeder
- `database/seeders/SimulasiPenggajianSeeder.php` - Seeder utama simulasi

### Model
- `app/Models/Pegawai.php` - Model pegawai
- `app/Models/Presensi.php` - Model presensi
- `app/Models/Penggajian.php` - Model penggajian
- `app/Models/Coa.php` - Model Chart of Accounts

### Controller
- `app/Http/Controllers/PegawaiController.php` - Controller pegawai
- `app/Http/Controllers/PresensiController.php` - Controller presensi
- `app/Http/Controllers/PenggajianController.php` - Controller penggajian

### View
- `resources/views/master-data/pegawai/` - View pegawai
- `resources/views/master-data/presensi/` - View presensi
- `resources/views/transaksi/penggajian/` - View penggajian

### Dokumentasi
- `DOKUMENTASI_SISTEM_PENGGAJIAN.md` - Dokumentasi sistem penggajian
- `DOKUMENTASI_SLIP_GAJI.md` - Dokumentasi slip gaji
- `SIMULASI_PENGGAJIAN_LENGKAP.md` - File ini

---

## 12. Catatan Penting

1. **Data Random**: Bonus dan potongan dibuat secara random (50% chance)
2. **Presensi Otomatis**: Presensi dibuat otomatis untuk setiap hari kerja
3. **Jam Kerja**: Hanya BTKL yang memiliki data jam kerja
4. **Jurnal Otomatis**: Jurnal dibuat otomatis saat penggajian disimpan
5. **Update Saldo**: Saldo COA diupdate otomatis setelah jurnal dibuat
6. **Bank Details**: Semua pegawai memiliki data bank untuk transfer gaji

---

## 13. Contoh Output Seeder

```
Membuat data master pegawai...
  ✓ 6 pegawai berhasil dibuat
Membuat data presensi...
  ✓ 440 record presensi berhasil dibuat
Membuat data penggajian simulasi...
  ✓ 6 record penggajian berhasil dibuat
  Tanggal penggajian: 30-12-2024
✓ Simulasi penggajian lengkap berhasil dibuat!
```

---

## 14. Next Steps

Setelah menjalankan simulasi:

1. **Verifikasi Data**
   - Cek master data pegawai
   - Cek data presensi
   - Cek data penggajian

2. **Lihat Laporan**
   - Buka menu Transaksi > Penggajian
   - Lihat detail penggajian
   - Cetak slip gaji

3. **Cek Jurnal**
   - Buka menu Laporan > Jurnal Umum
   - Lihat jurnal penggajian yang dibuat
   - Verifikasi saldo akun

4. **Export Data**
   - Export laporan penggajian ke Excel
   - Export slip gaji ke PDF
   - Simpan untuk arsip

---

**Dibuat**: 11 Desember 2024  
**Versi**: 1.0  
**Status**: Ready for Production
