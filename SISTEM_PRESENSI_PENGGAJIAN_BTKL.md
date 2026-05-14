# Sistem Presensi Harian dan Penggajian Bulanan Berbasis Jam Kerja Aktual

## 📋 Ringkasan Sistem

Sistem presensi dan penggajian SIMCOST dirancang untuk mengelola data presensi harian pegawai dan menghasilkan penggajian bulanan yang akurat berdasarkan **jam kerja aktual** (bukan patokan tetap 25 hari).

---

## 🎯 Konsep Utama

### 1. Presensi Harian
- Setiap pegawai melakukan **absen masuk** dan **absen keluar**
- Sistem otomatis menghitung **jumlah jam kerja aktual** per hari
- Status presensi: **Hadir**, **Alpha**, atau **Masuk Saja**

### 2. Rekap Bulanan
- Sistem mengakumulasi presensi harian ke dalam **periode bulan-tahun**
- Menghitung: total hari hadir, total alpha, total jam bulanan
- Menghasilkan **persentase kehadiran** dan **estimasi gaji**

### 3. Penggajian Bulanan
- Gaji dihitung berdasarkan: **Total Jam Bulanan × Tarif Per Jam**
- Rumus lengkap: `Total Gaji = Gaji Pokok + Tunjangan + Bonus - Potongan`
- Setiap pegawai hanya bisa memiliki 1 penggajian per bulan (unique constraint)

---

## 📊 Struktur Database

### Tabel: `presensis`
```sql
- id (PK)
- pegawai_id (FK)
- tgl_presensi (date)
- periode_bulan (1-12) -- Auto-filled
- periode_tahun (year) -- Auto-filled
- jam_masuk (time)
- jam_keluar (time)
- jumlah_jam (decimal) -- Auto-calculated
- status (Hadir/Alpha/Masuk Saja) -- Auto-set
- keterangan (text)
- verifikasi_wajah (boolean)
- foto_wajah (string)
- waktu_verifikasi (datetime)
- latitude_masuk, longitude_masuk
- latitude_keluar, longitude_keluar
- created_at, updated_at

INDEX: pegawai_id + periode_bulan + periode_tahun
```

### Tabel: `penggajians`
```sql
- id (PK)
- pegawai_id (FK)
- periode_bulan (1-12)
- periode_tahun (year)
- total_hari_hadir (int)
- total_alpha (int)
- total_jam (decimal) -- Total jam bulanan
- tanggal_penggajian (date)
- coa_kasbank (string)
- gaji_pokok (decimal)
- tarif_per_jam (decimal)
- tunjangan (decimal)
- tunjangan_jabatan (decimal)
- tunjangan_transport (decimal)
- tunjangan_konsumsi (decimal)
- total_tunjangan (decimal)
- asuransi (decimal)
- bonus (decimal)
- potongan (decimal)
- total_jam_kerja (decimal)
- total_gaji (decimal)
- status_pembayaran (belum_lunas/lunas)
- tanggal_dibayar (date)
- metode_pembayaran (transfer_bank/tunai/dll)
- status_posting (boolean)
- tanggal_posting (date)
- created_at, updated_at

UNIQUE: pegawai_id + periode_bulan + periode_tahun
```

### Tabel: `kalender_kerja`
```sql
- id (PK)
- bulan (1-12)
- tahun (year)
- target_hari_kerja (int, default 26)
- keterangan (text)
- created_at, updated_at

UNIQUE: bulan + tahun
```

### Tabel: `rekap_presensi_bulanan`
```sql
- id (PK)
- pegawai_id (FK)
- periode_bulan (1-12)
- periode_tahun (year)
- total_hari_hadir (int)
- total_alpha (int)
- total_masuk_saja (int)
- total_jam_bulanan (decimal)
- target_hari_kerja (int)
- persentase_kehadiran (decimal)
- estimasi_gaji (decimal)
- created_at, updated_at

UNIQUE: pegawai_id + periode_bulan + periode_tahun
```

---

## 🔄 Alur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│ 1. INPUT PRESENSI HARIAN                                    │
│    - Pegawai input jam masuk & jam keluar                   │
│    - Sistem auto-calculate jumlah jam                       │
│    - Sistem auto-set status (Hadir/Alpha/Masuk Saja)        │
│    - Sistem auto-fill periode_bulan & periode_tahun         │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. REKAP BULANAN (Otomatis)                                 │
│    - Sistem mengakumulasi presensi per pegawai per bulan    │
│    - Hitung: total_hari_hadir, total_alpha, total_jam       │
│    - Hitung: persentase_kehadiran, estimasi_gaji            │
│    - Simpan ke tabel rekap_presensi_bulanan                 │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. GENERATE PENGGAJIAN BULANAN (Manual)                     │
│    - Owner klik "Generate Penggajian Bulanan"               │
│    - Pilih bulan & tahun                                    │
│    - Sistem ambil rekap presensi untuk semua pegawai        │
│    - Hitung gaji: total_jam × tarif_per_jam                 │
│    - Buat/update penggajian untuk setiap pegawai            │
│    - Cegah duplikasi dengan unique constraint               │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. SLIP GAJI & PEMBAYARAN                                   │
│    - Owner lihat riwayat penggajian                         │
│    - Print slip gaji per pegawai                            │
│    - Tandai sebagai "Lunas" saat dibayar                    │
│    - Post ke jurnal akuntansi (opsional)                    │
└─────────────────────────────────────────────────────────────┘
```

---

## 📱 Fitur Utama

### A. Presensi Harian (Role: Pegawai & Owner)

#### Input Presensi
- **URL**: `/transaksi/presensi/create`
- **Field**:
  - Pegawai (dropdown)
  - Tanggal Presensi (date picker)
  - Jam Masuk (time picker)
  - Jam Keluar (time picker)
  - Keterangan (textarea)

#### Logika Auto-Fill
```php
// Saat saving presensi:
- periode_bulan = tgl_presensi->month
- periode_tahun = tgl_presensi->year
- jumlah_jam = (jam_keluar - jam_masuk) / 60 (dalam jam)
- status = 'Hadir' jika ada jam_masuk & jam_keluar
- status = 'Masuk Saja' jika hanya jam_masuk
- status = 'Alpha' jika kosong keduanya
```

#### Daftar Presensi
- **URL**: `/transaksi/presensi`
- **Filter**: Pegawai, Bulan, Tahun, Status
- **Kolom**: No, Tanggal, Pegawai, Jam Masuk, Jam Keluar, Jumlah Jam, Status, Keterangan, Aksi
- **Aksi**: Edit, Hapus

---

### B. Rekap Presensi Bulanan (Otomatis)

#### Generate Rekap
```php
// Dipanggil otomatis saat:
// 1. Presensi baru disimpan
// 2. Presensi diupdate
// 3. Generate penggajian bulanan

RekapPresensiBulanan::generateRekap($pegawaiId, $bulan, $tahun);

// Hasil:
- total_hari_hadir = COUNT(status='Hadir')
- total_alpha = COUNT(status='Alpha')
- total_masuk_saja = COUNT(status='Masuk Saja')
- total_jam_bulanan = SUM(jumlah_jam)
- target_hari_kerja = KalenderKerja::getTargetHariKerja($bulan, $tahun)
- persentase_kehadiran = (total_hari_hadir / target_hari_kerja) × 100
- estimasi_gaji = total_jam_bulanan × tarif_per_jam
```

---

### C. Penggajian Bulanan (Role: Owner)

#### Generate Penggajian
- **URL**: `/transaksi/penggajian/generate`
- **Form**:
  - Bulan (dropdown 1-12)
  - Tahun (dropdown)
  - Tanggal Penggajian (date picker, default: akhir bulan)

#### Logika Generate
```php
// Untuk setiap pegawai aktif:
1. Cek apakah penggajian sudah ada untuk periode ini
   - Jika ada: UPDATE dengan data presensi terbaru
   - Jika tidak: CREATE penggajian baru

2. Ambil rekap presensi bulanan
   - total_hari_hadir, total_alpha, total_jam_bulanan

3. Hitung gaji:
   - gaji_pokok = total_jam_bulanan × tarif_per_jam
   - tunjangan = getTunjangan($pegawai) // default 0
   - bonus = 0 (bisa di-edit manual)
   - potongan = 0 (bisa di-edit manual)
   - total_gaji = gaji_pokok + tunjangan + bonus - potongan

4. Simpan ke tabel penggajians
   - Unique constraint: pegawai_id + periode_bulan + periode_tahun
   - Cegah duplikasi penggajian untuk bulan yang sama
```

#### Riwayat Penggajian
- **URL**: `/transaksi/penggajian`
- **Filter**: Pegawai, Bulan, Tahun, Status Pembayaran
- **Kolom**: No, Tanggal, Bulan, Karyawan, Metode Bayar, Status, Gaji Pokok, Tunjangan, Asuransi, Bonus, Potongan, Total Gaji, Aksi
- **Aksi**: Detail, Print Slip

#### Detail Penggajian
- **URL**: `/transaksi/penggajian/{id}`
- **Tampilan**:
  - Informasi Pegawai
  - Ringkasan Presensi (hari hadir, alpha, total jam, tarif/jam)
  - Detail Presensi Harian (tabel)
  - Breakdown Gaji (gaji pokok, tunjangan, bonus, potongan, total)
  - Status Pembayaran (belum lunas / lunas)

#### Slip Gaji
- **URL**: `/transaksi/penggajian/{id}/print-slip`
- **Format**: PDF-ready HTML
- **Konten**:
  - Header (Slip Gaji, Periode)
  - Informasi Pegawai
  - Ringkasan Kehadiran
  - Rincian Gaji
  - Tanda Tangan (Kepala Departemen & Pegawai)
  - Catatan Penting

---

### D. Dashboard Pegawai (Role: Pegawai)

#### Informasi Bulan Ini
- Hari Hadir Bulan Ini
- Alpha Bulan Ini
- Total Jam Aktual Bulan Ini
- Target Hari Kerja
- Persentase Kehadiran
- Estimasi Gaji Bulan Berjalannya

#### Rumus
```
Persentase Kehadiran = (total_hari_hadir / target_hari_kerja) × 100
Estimasi Gaji = total_jam_bulanan × tarif_per_jam
```

---

## 🔐 Validasi & Keamanan

### Unique Constraint
```sql
-- Cegah duplikasi penggajian per bulan
UNIQUE (pegawai_id, periode_bulan, periode_tahun)

-- Cegah duplikasi rekap presensi per bulan
UNIQUE (pegawai_id, periode_bulan, periode_tahun)
```

### Validasi Input
```php
// Presensi
- pegawai_id: required, exists:pegawais
- tgl_presensi: required, date
- jam_masuk: nullable, date_format:H:i
- jam_keluar: nullable, date_format:H:i

// Penggajian Generate
- bulan: required, integer, between:1,12
- tahun: required, integer, min:2020
- tanggal_penggajian: nullable, date
```

### Multi-Tenant
```php
// Semua query menggunakan user_id untuk isolasi data
- Presensi: filter by pegawai->user_id
- Penggajian: filter by pegawai->user_id
- KalenderKerja: global (shared)
```

---

## 📈 Contoh Perhitungan

### Contoh 1: Pegawai Hadir Penuh
```
Periode: April 2026
Pegawai: Andi Pratama
Tarif/Jam: Rp 50.000

Presensi Harian:
- 01 Apr: 08:00 - 17:00 = 9 jam
- 02 Apr: 08:00 - 17:00 = 9 jam
- 03 Apr: 08:00 - 17:00 = 9 jam
- ... (26 hari kerja)
- Total: 234 jam

Penggajian:
- Total Jam: 234 jam
- Tarif/Jam: Rp 50.000
- Gaji Pokok: 234 × 50.000 = Rp 11.700.000
- Tunjangan: Rp 1.500.000
- Bonus: Rp 500.000
- Potongan: Rp 0
- Total Gaji: Rp 13.700.000

Persentase Kehadiran: (26 / 26) × 100 = 100%
```

### Contoh 2: Pegawai dengan Alpha
```
Periode: April 2026
Pegawai: Siti Aisyah
Tarif/Jam: Rp 40.000

Presensi Harian:
- Hari Hadir: 24 hari
- Alpha: 2 hari
- Total Jam: 216 jam (24 × 9 jam)

Penggajian:
- Total Jam: 216 jam
- Tarif/Jam: Rp 40.000
- Gaji Pokok: 216 × 40.000 = Rp 8.640.000
- Tunjangan: Rp 1.200.000
- Bonus: Rp 0
- Potongan: Rp 300.000
- Total Gaji: Rp 9.540.000

Persentase Kehadiran: (24 / 26) × 100 = 92.31%
```

---

## 🛠️ Implementasi

### 1. Migration
```bash
php artisan migrate
```

### 2. Model
- `Presensi` - Model presensi harian
- `Penggajian` - Model penggajian bulanan
- `KalenderKerja` - Model kalender kerja
- `RekapPresensiBulanan` - Model rekap presensi

### 3. Service
- `PenggajianService` - Business logic penggajian

### 4. Controller
- `PresensiController` - CRUD presensi
- `PenggajianController` - CRUD penggajian

### 5. View
- `transaksi/presensi/index.blade.php` - Daftar presensi
- `transaksi/presensi/create.blade.php` - Input presensi
- `transaksi/penggajian/index.blade.php` - Riwayat penggajian
- `transaksi/penggajian/generate-form.blade.php` - Form generate
- `transaksi/penggajian/show.blade.php` - Detail penggajian
- `transaksi/penggajian/slip.blade.php` - Slip gaji

### 6. Routes
```php
// Presensi
Route::resource('presensi', PresensiController::class);
Route::get('presensi/rekap/{pegawaiId}/{bulan}/{tahun}', [PresensiController::class, 'getRekapBulanan']);

// Penggajian
Route::resource('penggajian', PenggajianController::class);
Route::get('penggajian/generate', [PenggajianController::class, 'generateForm'])->name('penggajian.generate-form');
Route::post('penggajian/generate', [PenggajianController::class, 'generate'])->name('penggajian.generate');
Route::post('penggajian/{id}/mark-as-paid', [PenggajianController::class, 'markAsPaid'])->name('penggajian.mark-as-paid');
Route::get('penggajian/{id}/print-slip', [PenggajianController::class, 'printSlip'])->name('penggajian.print-slip');
```

---

## 📝 Catatan Penting

1. **Jam Kerja Aktual**: Sistem menggunakan data presensi aktual, bukan patokan tetap
2. **Periode Otomatis**: Bulan dan tahun diisi otomatis berdasarkan tanggal presensi
3. **Unique Constraint**: Mencegah duplikasi penggajian untuk periode yang sama
4. **Rekap Otomatis**: Rekap presensi di-generate otomatis saat presensi disimpan
5. **Tarif Per Jam**: Diambil dari `jabatan->tarif_btkl`
6. **Tunjangan**: Default 0, bisa di-customize per kebijakan perusahaan
7. **Slip Gaji**: Bisa di-print langsung dari halaman detail penggajian

---

## 🚀 Pengembangan Lanjutan

1. **Integrasi Biometric**: Koneksi dengan mesin absen biometric
2. **Mobile App**: Aplikasi mobile untuk input presensi
3. **Laporan Analitik**: Dashboard dengan grafik kehadiran & gaji
4. **Integrasi Akuntansi**: Auto-post ke jurnal akuntansi
5. **Approval Workflow**: Persetujuan penggajian sebelum pembayaran
6. **Overtime Calculation**: Perhitungan lembur otomatis
7. **Tax Calculation**: Perhitungan pajak otomatis
8. **Export Excel**: Export penggajian ke Excel

---

**Dibuat**: 30 April 2026  
**Versi**: 1.0  
**Status**: Production Ready
