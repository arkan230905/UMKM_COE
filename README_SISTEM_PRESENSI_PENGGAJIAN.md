# Sistem Presensi Harian dan Penggajian Bulanan SIMCOST

## 📌 Overview

Sistem presensi dan penggajian SIMCOST adalah solusi lengkap untuk mengelola data presensi harian pegawai dan menghasilkan penggajian bulanan yang akurat berdasarkan **jam kerja aktual** (bukan patokan tetap 25 hari).

Sistem ini dirancang khusus untuk industri manufaktur dengan model pembayaran BTKL (Biaya Tenaga Kerja Langsung) berbasis jam kerja.

---

## 🎯 Fitur Utama

### 1. Presensi Harian
- ✅ Input jam masuk dan jam keluar
- ✅ Auto-calculate jumlah jam kerja
- ✅ Auto-set status (Hadir/Alpha/Masuk Saja)
- ✅ Auto-fill periode bulan dan tahun
- ✅ Edit dan hapus presensi
- ✅ Filter by pegawai, bulan, tahun, status

### 2. Rekap Presensi Bulanan
- ✅ Akumulasi presensi harian per pegawai per bulan
- ✅ Hitung total hari hadir, alpha, jam bulanan
- ✅ Hitung persentase kehadiran
- ✅ Estimasi gaji berdasarkan jam kerja

### 3. Penggajian Bulanan
- ✅ Generate penggajian otomatis untuk semua pegawai
- ✅ Hitung gaji: Total Jam × Tarif Per Jam
- ✅ Breakdown gaji: Gaji Pokok + Tunjangan + Bonus - Potongan
- ✅ Cegah duplikasi dengan unique constraint
- ✅ Update penggajian jika sudah ada untuk periode yang sama

### 4. Slip Gaji
- ✅ Print slip gaji per pegawai
- ✅ Format PDF-ready
- ✅ Informasi lengkap: Pegawai, Presensi, Breakdown Gaji
- ✅ Tanda tangan (Kepala Departemen & Pegawai)

### 5. Riwayat Penggajian
- ✅ Lihat semua penggajian yang sudah dibuat
- ✅ Filter by pegawai, bulan, tahun, status pembayaran
- ✅ Lihat detail penggajian
- ✅ Tandai sebagai lunas
- ✅ Print slip gaji

---

## 📊 Alur Sistem

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

## 🚀 Quick Start

### 1. Setup Database
```bash
php artisan migrate
```

### 2. Setup Kalender Kerja
```php
php artisan tinker
> for ($bulan = 1; $bulan <= 12; $bulan++) {
    KalenderKerja::firstOrCreate(
        ['bulan' => $bulan, 'tahun' => 2026],
        ['target_hari_kerja' => 26]
    );
  }
```

### 3. Input Presensi
- Buka `/transaksi/presensi/create`
- Isi form: Pegawai, Tanggal, Jam Masuk, Jam Keluar
- Klik "Simpan Presensi"

### 4. Generate Penggajian
- Buka `/transaksi/penggajian/generate`
- Pilih Bulan & Tahun
- Klik "Generate Penggajian"

### 5. Lihat Riwayat
- Buka `/transaksi/penggajian`
- Lihat tabel riwayat penggajian
- Klik "Detail" atau "Print Slip"

---

## 📁 File Structure

### Database
```
database/migrations/
  └── 2026_04_30_100000_enhance_presensi_penggajian_system.php
```

### Models
```
app/Models/
  ├── Presensi.php (updated)
  ├── Penggajian.php (updated)
  ├── KalenderKerja.php (new)
  └── RekapPresensiBulanan.php (new)
```

### Services
```
app/Services/
  └── PenggajianService.php
```

### Controllers
```
app/Http/Controllers/
  ├── PresensiController.php
  └── PenggajianController.php
```

### Views
```
resources/views/transaksi/
  ├── presensi/
  │   ├── index.blade.php
  │   ├── create.blade.php
  │   └── edit.blade.php
  └── penggajian/
      ├── index.blade.php
      ├── generate-form.blade.php
      ├── show.blade.php
      └── slip.blade.php
```

### Routes
```
ROUTES_PRESENSI_PENGGAJIAN.php
```

### Documentation
```
├── SISTEM_PRESENSI_PENGGAJIAN_BTKL.md (Dokumentasi lengkap)
├── SETUP_SISTEM_PRESENSI.md (Panduan setup)
├── RINGKASAN_IMPLEMENTASI_SISTEM_PRESENSI.md (Ringkasan)
├── CHECKLIST_IMPLEMENTASI_FINAL.md (Checklist)
└── README_SISTEM_PRESENSI_PENGGAJIAN.md (File ini)
```

---

## 📊 Database Schema

### Tabel: presensis
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
- created_at, updated_at

INDEX: pegawai_id + periode_bulan + periode_tahun
```

### Tabel: penggajians
```sql
- id (PK)
- pegawai_id (FK)
- periode_bulan (1-12)
- periode_tahun (year)
- total_hari_hadir (int)
- total_alpha (int)
- total_jam (decimal)
- tanggal_penggajian (date)
- gaji_pokok (decimal)
- tarif_per_jam (decimal)
- tunjangan (decimal)
- bonus (decimal)
- potongan (decimal)
- total_gaji (decimal)
- status_pembayaran (belum_lunas/lunas)
- tanggal_dibayar (date)
- metode_pembayaran (transfer_bank/tunai/dll)
- created_at, updated_at

UNIQUE: pegawai_id + periode_bulan + periode_tahun
```

### Tabel: kalender_kerja
```sql
- id (PK)
- bulan (1-12)
- tahun (year)
- target_hari_kerja (int, default 26)
- keterangan (text)
- created_at, updated_at

UNIQUE: bulan + tahun
```

### Tabel: rekap_presensi_bulanan
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

## 🔐 Keamanan & Validasi

### Unique Constraint
```sql
-- Cegah duplikasi penggajian per bulan
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
```

---

## 📈 Contoh Perhitungan

### Pegawai Hadir Penuh
```
Periode: April 2026
Pegawai: Andi Pratama
Tarif/Jam: Rp 50.000

Presensi: 26 hari × 9 jam = 234 jam

Penggajian:
- Gaji Pokok: 234 × 50.000 = Rp 11.700.000
- Tunjangan: Rp 1.500.000
- Bonus: Rp 500.000
- Potongan: Rp 0
- Total Gaji: Rp 13.700.000

Persentase Kehadiran: (26 / 26) × 100 = 100%
```

### Pegawai dengan Alpha
```
Periode: April 2026
Pegawai: Siti Aisyah
Tarif/Jam: Rp 40.000

Presensi: 24 hari × 9 jam = 216 jam

Penggajian:
- Gaji Pokok: 216 × 40.000 = Rp 8.640.000
- Tunjangan: Rp 1.200.000
- Bonus: Rp 0
- Potongan: Rp 300.000
- Total Gaji: Rp 9.540.000

Persentase Kehadiran: (24 / 26) × 100 = 92.31%
```

---

## 📚 Dokumentasi Lengkap

Untuk dokumentasi lebih lengkap, silakan baca:

1. **SISTEM_PRESENSI_PENGGAJIAN_BTKL.md** - Dokumentasi lengkap sistem
2. **SETUP_SISTEM_PRESENSI.md** - Panduan setup dan konfigurasi
3. **RINGKASAN_IMPLEMENTASI_SISTEM_PRESENSI.md** - Ringkasan implementasi
4. **CHECKLIST_IMPLEMENTASI_FINAL.md** - Checklist implementasi

---

## 🛠️ Troubleshooting

### Error: "SQLSTATE[42S02]: Table or view not found"
**Solusi**: Jalankan migration
```bash
php artisan migrate
```

### Error: "Unique constraint violation"
**Solusi**: Penggajian untuk periode ini sudah ada
```php
// Hapus penggajian lama atau update
Penggajian::where('pegawai_id', $pegawaiId)
    ->where('periode_bulan', $bulan)
    ->where('periode_tahun', $tahun)
    ->delete();
```

### Error: "Tarif per jam is null"
**Solusi**: Pastikan setiap jabatan memiliki tarif_btkl
```php
$pegawai->jabatan->update(['tarif_btkl' => 50000]);
```

---

## 🎯 Next Steps

1. ✅ Baca dokumentasi lengkap
2. ✅ Setup database dengan migration
3. ✅ Verifikasi tarif per jam di setiap jabatan
4. ✅ Setup kalender kerja
5. ✅ Test input presensi
6. ✅ Test generate penggajian
7. ✅ Test print slip gaji
8. ✅ Deploy ke production

---

## 📞 Support

Jika ada pertanyaan atau masalah:

1. Cek dokumentasi: `SISTEM_PRESENSI_PENGGAJIAN_BTKL.md`
2. Cek setup guide: `SETUP_SISTEM_PRESENSI.md`
3. Cek error logs: `storage/logs/laravel.log`
4. Test dengan Tinker: `php artisan tinker`

---

## 📝 Catatan Penting

1. **Jam Kerja Aktual**: Sistem menggunakan data presensi aktual, bukan patokan tetap
2. **Periode Otomatis**: Bulan dan tahun diisi otomatis berdasarkan tanggal presensi
3. **Unique Constraint**: Mencegah duplikasi penggajian untuk periode yang sama
4. **Rekap Otomatis**: Rekap presensi di-generate otomatis saat presensi disimpan
5. **Tarif Per Jam**: Diambil dari `jabatan->tarif_btkl`

---

## 📄 License

Sistem ini adalah bagian dari SIMCOST dan mengikuti lisensi yang sama.

---

**Dibuat**: 30 April 2026  
**Versi**: 1.0  
**Status**: Production Ready  
**Last Updated**: 30 April 2026

---

## 🎉 Selesai!

Sistem presensi dan penggajian BTKL sudah siap untuk diimplementasikan. Silakan ikuti checklist implementasi dan dokumentasi yang sudah disediakan.

Semoga sistem ini membantu mengelola presensi dan penggajian pegawai dengan lebih efisien dan akurat!
