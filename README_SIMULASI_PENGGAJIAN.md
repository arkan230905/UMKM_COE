# README - Simulasi Penggajian Lengkap

## 📌 Ringkasan

Simulasi penggajian lengkap untuk UMKM COE mencakup master data pegawai, presensi otomatis, perhitungan gaji, dan jurnal akuntansi. Seeder ini membuat data realistis untuk testing dan demo sistem penggajian.

---

## 🚀 Quick Start

### 1. Jalankan Seeder
```bash
php artisan db:seed --class=SimulasiPenggajianSeeder
```

### 2. Verifikasi Data
```bash
php artisan tinker
>>> App\Models\Pegawai::count();  # Harus 6
>>> App\Models\Presensi::count(); # Harus 440+
>>> App\Models\Penggajian::count(); # Harus 6
```

### 3. Akses UI
Buka: `http://localhost:8000/admin`  
Menu: **Transaksi > Penggajian**

---

## 📊 Data yang Dibuat

### Master Pegawai (6 orang)
- **3 BTKL** (Biaya Tenaga Kerja Langsung): Operator Produksi
- **3 BTKTL** (Biaya Tenaga Kerja Tidak Langsung): Admin & Support

### Presensi (440+ records)
- Semua hari kerja bulan berjalan
- Status: 80% Hadir, 10% Sakit, 10% Izin
- Hanya untuk pegawai BTKL

### Penggajian (6 records)
- Satu record per pegawai
- Tanggal: Akhir bulan berjalan
- Gaji dihitung otomatis dari master data

---

## 📁 File-File Terkait

### Seeder
```
database/seeders/SimulasiPenggajianSeeder.php
```

### Dokumentasi
```
SIMULASI_PENGGAJIAN_LENGKAP.md      - Dokumentasi detail lengkap
PANDUAN_CEPAT_SIMULASI_PENGGAJIAN.md - Panduan cepat 5 menit
CONTOH_KASUS_SIMULASI_PENGGAJIAN.md - 6 contoh kasus nyata
README_SIMULASI_PENGGAJIAN.md        - File ini
```

### Model & Controller
```
app/Models/Pegawai.php
app/Models/Presensi.php
app/Models/Penggajian.php
app/Http/Controllers/PenggajianController.php
```

---

## 🎯 Fitur Utama

### ✅ Master Data Pegawai
- 6 pegawai dengan data lengkap
- Jenis: BTKL & BTKTL
- Data bank untuk transfer gaji

### ✅ Presensi Otomatis
- Presensi untuk semua hari kerja
- Status random (Hadir/Sakit/Izin)
- Total jam kerja otomatis

### ✅ Perhitungan Gaji
- BTKL: (Tarif × Jam Kerja) + Tunjangan + Asuransi + Bonus - Potongan
- BTKTL: Gaji Pokok + Tunjangan + Asuransi + Bonus - Potongan

### ✅ Jurnal Otomatis
- Debit: Beban Gaji (501)
- Kredit: Kas/Bank (101)
- Saldo COA diupdate otomatis

---

## 💡 Contoh Perhitungan

### BTKL (Budi Santoso)
```
Tarif per Jam       : Rp 50.000
Jam Kerja           : 144 jam
Gaji Dasar          : Rp 7.200.000
Tunjangan           : Rp 500.000
Asuransi            : Rp 200.000
Bonus               : Rp 500.000 (random)
Potongan            : Rp 100.000 (random)
─────────────────────────────
Total Gaji          : Rp 8.300.000
```

### BTKTL (Ani Wijayanti)
```
Gaji Pokok          : Rp 5.000.000
Tunjangan           : Rp 1.000.000
Asuransi            : Rp 300.000
Bonus               : Rp 1.000.000 (random)
Potongan            : Rp 200.000 (random)
─────────────────────────────
Total Gaji          : Rp 7.100.000
```

---

## 🔧 Troubleshooting

### Error: "Akun kas (101) tidak ditemukan"
```bash
php artisan db:seed --class=CoaSeeder
```

### Error: "Saldo kas tidak mencukupi"
```bash
php artisan migrate:fresh --seed
```

### Data Presensi Kosong
Pastikan seeder dijalankan di bulan yang sama dengan sistem.

---

## 📖 Dokumentasi Lengkap

| Dokumen | Isi |
|---------|-----|
| **SIMULASI_PENGGAJIAN_LENGKAP.md** | Dokumentasi detail: alur, formula, jurnal, verifikasi |
| **PANDUAN_CEPAT_SIMULASI_PENGGAJIAN.md** | Panduan cepat 5 menit, menu navigasi, tips & trik |
| **CONTOH_KASUS_SIMULASI_PENGGAJIAN.md** | 6 contoh kasus nyata dengan perhitungan detail |
| **DOKUMENTASI_SISTEM_PENGGAJIAN.md** | Dokumentasi sistem penggajian umum |
| **DOKUMENTASI_SLIP_GAJI.md** | Dokumentasi slip gaji (cetak & PDF) |

---

## ✨ Fitur Tambahan

### Cetak Slip Gaji
1. Buka **Transaksi > Penggajian**
2. Klik tombol "Cetak Slip"
3. Pilih: Cetak HTML, Download PDF, atau Kembali

### Export Laporan
1. Buka **Transaksi > Penggajian**
2. Klik tombol "Export"
3. Pilih format: Excel atau PDF

### Filter & Search
1. Buka **Transaksi > Penggajian**
2. Gunakan filter berdasarkan:
   - Pegawai
   - Tanggal
   - Jenis pegawai (BTKL/BTKTL)

---

## 📊 Statistik Data

```
Total Pegawai           : 6 orang
├─ BTKL                 : 3 orang
└─ BTKTL                : 3 orang

Total Presensi          : 440+ records
├─ Hadir                : 352 hari (80%)
├─ Sakit                : 44 hari (10%)
└─ Izin                 : 44 hari (10%)

Total Penggajian        : 6 records
├─ BTKL                 : 3 records
└─ BTKTL                : 3 records

Total Gaji Bulanan      : Rp 46.820.000
├─ BTKL                 : Rp 21.620.000
└─ BTKTL                : Rp 25.200.000
```

---

## 🔍 Verifikasi Checklist

- [ ] Seeder berhasil dijalankan
- [ ] 6 pegawai terdaftar di database
- [ ] 440+ record presensi dibuat
- [ ] 6 record penggajian dibuat
- [ ] Jurnal penggajian terbuat otomatis
- [ ] Saldo COA terupdate
- [ ] Slip gaji bisa dicetak
- [ ] Laporan bisa diakses

---

## 📞 Dukungan

Untuk pertanyaan atau masalah:
1. Baca dokumentasi lengkap di `SIMULASI_PENGGAJIAN_LENGKAP.md`
2. Lihat contoh kasus di `CONTOH_KASUS_SIMULASI_PENGGAJIAN.md`
3. Ikuti panduan cepat di `PANDUAN_CEPAT_SIMULASI_PENGGAJIAN.md`

---

## 📝 Catatan Penting

1. **Data Random**: Bonus dan potongan dibuat secara random (50% chance)
2. **Presensi Otomatis**: Presensi dibuat untuk setiap hari kerja bulan berjalan
3. **Jam Kerja**: Hanya BTKL yang memiliki data presensi
4. **Jurnal Otomatis**: Jurnal dibuat saat seeder dijalankan
5. **Saldo Kas**: Pastikan saldo kas (101) cukup untuk penggajian

---

**Versi**: 1.0  
**Tanggal**: 11 Desember 2024  
**Status**: ✅ Ready to Use
