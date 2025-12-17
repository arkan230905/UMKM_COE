# Panduan Cepat Simulasi Penggajian

## ⚡ Quick Start (5 Menit)

### 1. Jalankan Seeder
```bash
php artisan db:seed --class=SimulasiPenggajianSeeder
```

**Output yang diharapkan:**
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

### 2. Akses Dashboard
Buka browser: `http://localhost:8000/admin`

### 3. Lihat Data Penggajian
Navigasi ke: **Transaksi > Penggajian**

---

## 📊 Data yang Dibuat

### Pegawai BTKL (3 orang)
| Nama | Tarif/Jam | Tunjangan | Asuransi |
|------|-----------|-----------|----------|
| Budi Santoso | Rp 50.000 | Rp 500.000 | Rp 200.000 |
| Siti Nurhaliza | Rp 45.000 | Rp 400.000 | Rp 150.000 |
| Ahmad Wijaya | Rp 35.000 | Rp 300.000 | Rp 100.000 |

### Pegawai BTKTL (3 orang)
| Nama | Gaji Pokok | Tunjangan | Asuransi |
|------|-----------|-----------|----------|
| Ani Wijayanti | Rp 5.000.000 | Rp 1.000.000 | Rp 300.000 |
| Rudi Hermawan | Rp 6.000.000 | Rp 1.500.000 | Rp 400.000 |
| Eka Putri Lestari | Rp 7.000.000 | Rp 2.000.000 | Rp 500.000 |

---

## 🔍 Verifikasi Data

### Via UI Dashboard
1. **Master Data > Pegawai**
   - Lihat 6 pegawai yang dibuat
   - Lihat detail masing-masing pegawai

2. **Master Data > Presensi**
   - Lihat presensi untuk semua hari kerja bulan ini
   - Filter berdasarkan pegawai

3. **Transaksi > Penggajian**
   - Lihat 6 record penggajian
   - Lihat detail penggajian setiap pegawai
   - Lihat total gaji yang dihitung

### Via Terminal (Tinker)
```bash
php artisan tinker

# Lihat semua pegawai
>>> App\Models\Pegawai::count();
6

# Lihat total presensi
>>> App\Models\Presensi::count();
440

# Lihat total penggajian
>>> App\Models\Penggajian::count();
6

# Lihat total gaji yang dibayarkan
>>> App\Models\Penggajian::sum('total_gaji');
49750000
```

---

## 💰 Contoh Perhitungan Gaji

### BTKL - Budi Santoso
```
Tarif per Jam       : Rp 50.000
Jam Kerja (bulan)   : 160 jam (asumsi 20 hari kerja × 8 jam)
Gaji Dasar          : Rp 8.000.000

Komponen Tambahan:
+ Tunjangan         : Rp 500.000
+ Asuransi          : Rp 200.000
+ Bonus (50%)       : Rp 500.000
- Potongan (50%)    : Rp 100.000
─────────────────────────────
Total Gaji          : Rp 9.100.000
```

### BTKTL - Ani Wijayanti
```
Gaji Pokok          : Rp 5.000.000

Komponen Tambahan:
+ Tunjangan         : Rp 1.000.000
+ Asuransi          : Rp 300.000
+ Bonus (50%)       : Rp 1.000.000
- Potongan (50%)    : Rp 200.000
─────────────────────────────
Total Gaji          : Rp 7.100.000
```

---

## 📋 Fitur yang Tersedia

### 1. Lihat Detail Penggajian
1. Buka **Transaksi > Penggajian**
2. Klik nama pegawai atau tombol "Lihat"
3. Lihat rincian lengkap:
   - Informasi pegawai
   - Komponen gaji
   - Total gaji
   - Tanggal penggajian

### 2. Cetak Slip Gaji
1. Buka detail penggajian
2. Klik tombol "Cetak Slip"
3. Pilih:
   - **Cetak**: Tampilkan di browser
   - **Download PDF**: Simpan sebagai file PDF
   - **Kembali**: Kembali ke daftar

### 3. Edit Penggajian
1. Buka **Transaksi > Penggajian**
2. Klik tombol "Edit"
3. Ubah bonus/potongan jika diperlukan
4. Klik "Simpan"

### 4. Lihat Jurnal
1. Buka **Laporan > Jurnal Umum**
2. Filter berdasarkan tanggal penggajian
3. Lihat jurnal entry yang dibuat:
   - Debit: Beban Gaji (501)
   - Kredit: Kas/Bank (101)

---

## 🎯 Skenario Penggunaan

### Skenario 1: Penggajian Bulanan Normal
**Tujuan**: Memproses penggajian bulanan untuk semua pegawai

**Langkah**:
1. Jalankan seeder: `php artisan db:seed --class=SimulasiPenggajianSeeder`
2. Buka **Transaksi > Penggajian**
3. Lihat daftar penggajian yang sudah dibuat
4. Cetak slip gaji untuk setiap pegawai
5. Proses pembayaran melalui bank

**Hasil**:
- 6 record penggajian dibuat
- 6 jurnal entry dibuat otomatis
- Saldo kas berkurang sebesar total penggajian
- Beban gaji bertambah sebesar total penggajian

### Skenario 2: Analisis Komponen Gaji
**Tujuan**: Membandingkan gaji BTKL vs BTKTL

**Langkah**:
1. Buka **Transaksi > Penggajian**
2. Lihat detail setiap pegawai
3. Bandingkan komponen gaji:
   - BTKL: Gaji dasar dari jam kerja
   - BTKTL: Gaji dasar tetap

**Insight**:
- BTKL lebih fleksibel (tergantung jam kerja)
- BTKTL lebih stabil (gaji tetap)
- Asuransi & tunjangan sama untuk semua

### Skenario 3: Audit Presensi
**Tujuan**: Verifikasi jam kerja yang digunakan untuk perhitungan gaji

**Langkah**:
1. Buka **Master Data > Presensi**
2. Filter pegawai BTKL
3. Hitung total jam kerja per pegawai
4. Bandingkan dengan jam kerja di penggajian

**Verifikasi**:
- Presensi sesuai dengan jam kerja di penggajian
- Tidak ada data yang hilang atau duplikat

---

## 🔧 Troubleshooting

### ❌ Error: "Akun kas (101) tidak ditemukan"
**Penyebab**: COA belum di-seed

**Solusi**:
```bash
# Jalankan CoaSeeder terlebih dahulu
php artisan db:seed --class=CoaSeeder

# Atau jalankan fresh migrate dengan semua seeder
php artisan migrate:fresh --seed
```

### ❌ Error: "Saldo kas tidak mencukupi"
**Penyebab**: Saldo kas (101) kurang dari total penggajian

**Solusi**:
```bash
# Cek saldo kas
php artisan tinker
>>> App\Models\Coa::where('kode_akun', '101')->first();

# Jika saldo kurang, tambah melalui transaksi penerimaan kas
# Atau jalankan fresh migrate untuk reset saldo
php artisan migrate:fresh --seed
```

### ❌ Data Presensi Kosong
**Penyebab**: Seeder dijalankan di bulan berbeda

**Solusi**:
```bash
# Jalankan ulang seeder
php artisan db:seed --class=SimulasiPenggajianSeeder

# Atau reset database
php artisan migrate:fresh --seed
```

### ❌ Jurnal Tidak Terbuat
**Penyebab**: JournalService error atau akun beban gaji tidak ditemukan

**Solusi**:
```bash
# Cek log error
tail -f storage/logs/laravel.log

# Pastikan akun beban gaji (501) ada
php artisan tinker
>>> App\Models\Coa::where('kode_akun', '501')->first();

# Jika tidak ada, jalankan CoaSeeder
php artisan db:seed --class=CoaSeeder
```

---

## 📱 Menu Navigasi

### Master Data
- **Pegawai**: Lihat/edit data pegawai
- **Presensi**: Lihat/edit data presensi
- **Jabatan**: Lihat/edit data jabatan (jika ada)

### Transaksi
- **Penggajian**: Lihat/edit/cetak penggajian
- **Presensi**: Input presensi harian (jika ada)

### Laporan
- **Jurnal Umum**: Lihat jurnal penggajian
- **Buku Besar**: Lihat saldo akun
- **Penggajian**: Laporan penggajian bulanan (jika ada)

---

## 💡 Tips & Trik

### Tip 1: Export Laporan Penggajian
1. Buka **Transaksi > Penggajian**
2. Cari tombol "Export" atau "Download"
3. Pilih format: Excel atau PDF
4. Simpan file

### Tip 2: Filter Penggajian
1. Buka **Transaksi > Penggajian**
2. Gunakan filter:
   - **Pegawai**: Filter berdasarkan nama pegawai
   - **Tanggal**: Filter berdasarkan tanggal penggajian
   - **Jenis**: Filter berdasarkan jenis pegawai (BTKL/BTKTL)

### Tip 3: Cetak Slip Gaji Massal
1. Buka **Transaksi > Penggajian**
2. Pilih semua record (checkbox)
3. Klik "Cetak Slip Massal"
4. Tunggu sampai selesai
5. Download file ZIP

### Tip 4: Cek Saldo Kas
1. Buka **Master Data > COA** atau **Laporan > Buku Besar**
2. Cari akun Kas/Bank (101)
3. Lihat saldo akhir
4. Pastikan cukup untuk penggajian berikutnya

---

## 📞 Bantuan

### Dokumentasi Lengkap
- `SIMULASI_PENGGAJIAN_LENGKAP.md` - Dokumentasi detail
- `DOKUMENTASI_SISTEM_PENGGAJIAN.md` - Dokumentasi sistem
- `DOKUMENTASI_SLIP_GAJI.md` - Dokumentasi slip gaji

### File Seeder
- `database/seeders/SimulasiPenggajianSeeder.php` - Seeder utama

### Hubungi Tim
- Email: support@umkm.local
- WhatsApp: [nomor support]
- Jam Operasional: 09:00 - 17:00 (Senin-Jumat)

---

**Versi**: 1.0  
**Tanggal**: 11 Desember 2024  
**Status**: Ready to Use
