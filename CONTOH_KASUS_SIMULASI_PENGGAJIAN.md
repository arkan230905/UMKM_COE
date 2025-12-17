# Contoh Kasus Simulasi Penggajian

## Kasus 1: Penggajian Pegawai BTKL (Budi Santoso)

### Data Master Pegawai
```
Nama              : Budi Santoso
Kode Pegawai      : PGW001
Jabatan           : Operator Produksi
Jenis Pegawai     : BTKL (Biaya Tenaga Kerja Langsung)
Tarif per Jam     : Rp 50.000
Tunjangan         : Rp 500.000
Asuransi          : Rp 200.000
Bank              : BCA
Nomor Rekening    : 1234567890
```

### Data Presensi (Desember 2024)
```
Total Hari Kerja  : 22 hari (Senin-Jumat, exclude weekend & hari libur)
Hadir             : 18 hari × 8 jam = 144 jam
Sakit             : 2 hari × 0 jam = 0 jam
Izin              : 2 hari × 0 jam = 0 jam
─────────────────────────────
Total Jam Kerja   : 144 jam
```

### Perhitungan Gaji
```
Formula BTKL:
Total Gaji = (Tarif × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan

Perhitungan:
Gaji Dasar        = 144 jam × Rp 50.000 = Rp 7.200.000
Tunjangan         = Rp 500.000
Asuransi          = Rp 200.000
Bonus             = Rp 500.000 (diberikan karena kinerja baik)
Potongan          = Rp 100.000 (keterlambatan 2 jam)
─────────────────────────────────────────────
Total Gaji        = Rp 7.200.000 + Rp 500.000 + Rp 200.000 + Rp 500.000 - Rp 100.000
                  = Rp 8.300.000
```

### Slip Gaji
```
═══════════════════════════════════════════════════════════════
                    SLIP GAJI BULANAN
═══════════════════════════════════════════════════════════════

Nama Pegawai      : Budi Santoso
Kode Pegawai      : PGW001
Jabatan           : Operator Produksi
Periode           : Desember 2024

───────────────────────────────────────────────────────────────
PENDAPATAN
───────────────────────────────────────────────────────────────
Gaji Dasar (144 jam × Rp 50.000)      Rp 7.200.000
Tunjangan                              Rp   500.000
Bonus Kinerja                          Rp   500.000
                                       ─────────────
Jumlah Pendapatan                      Rp 8.200.000

───────────────────────────────────────────────────────────────
POTONGAN
───────────────────────────────────────────────────────────────
Asuransi Kesehatan                     Rp   200.000
Potongan Keterlambatan                 Rp   100.000
                                       ─────────────
Jumlah Potongan                        Rp   300.000

───────────────────────────────────────────────────────────────
RINGKASAN
───────────────────────────────────────────────────────────────
Total Pendapatan                       Rp 8.200.000
Total Potongan                         Rp   300.000
                                       ─────────────
GAJI BERSIH                            Rp 7.900.000

Terbilang: Tujuh juta Sembilan ratus ribu rupiah

───────────────────────────────────────────────────────────────
Disetujui oleh:                    Diterima oleh:
_____________________              _____________________
(Kepala HRD)                       (Budi Santoso)

Tanggal: 31 Desember 2024
═══════════════════════════════════════════════════════════════
```

### Jurnal Akuntansi
```
Tanggal: 31 Desember 2024
Deskripsi: Penggajian - Budi Santoso

Akun                          Debit           Kredit
─────────────────────────────────────────────────────
501 Beban Gaji              Rp 8.300.000
101 Kas/Bank BCA                            Rp 8.300.000
─────────────────────────────────────────────────────
Total                       Rp 8.300.000    Rp 8.300.000
```

### Dampak pada Saldo COA
```
Sebelum Penggajian:
101 Kas/Bank BCA            Rp 100.000.000
501 Beban Gaji              Rp 0

Setelah Penggajian:
101 Kas/Bank BCA            Rp 91.700.000  (berkurang Rp 8.300.000)
501 Beban Gaji              Rp 8.300.000   (bertambah Rp 8.300.000)
```

---

## Kasus 2: Penggajian Pegawai BTKTL (Ani Wijayanti)

### Data Master Pegawai
```
Nama              : Ani Wijayanti
Kode Pegawai      : PGW004
Jabatan           : Staff Admin
Jenis Pegawai     : BTKTL (Biaya Tenaga Kerja Tidak Langsung)
Gaji Pokok        : Rp 5.000.000
Tunjangan         : Rp 1.000.000
Asuransi          : Rp 300.000
Bank              : BCA
Nomor Rekening    : 1234567893
```

### Perhitungan Gaji
```
Formula BTKTL:
Total Gaji = Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan

Perhitungan:
Gaji Pokok        = Rp 5.000.000
Tunjangan         = Rp 1.000.000
Asuransi          = Rp 300.000
Bonus             = Rp 1.000.000 (bonus akhir tahun)
Potongan          = Rp 200.000 (cicilan pinjaman)
─────────────────────────────────────────────────
Total Gaji        = Rp 5.000.000 + Rp 1.000.000 + Rp 300.000 + Rp 1.000.000 - Rp 200.000
                  = Rp 7.100.000
```

### Slip Gaji
```
═══════════════════════════════════════════════════════════════
                    SLIP GAJI BULANAN
═══════════════════════════════════════════════════════════════

Nama Pegawai      : Ani Wijayanti
Kode Pegawai      : PGW004
Jabatan           : Staff Admin
Periode           : Desember 2024

───────────────────────────────────────────────────────────────
PENDAPATAN
───────────────────────────────────────────────────────────────
Gaji Pokok                             Rp 5.000.000
Tunjangan                              Rp 1.000.000
Bonus Akhir Tahun                      Rp 1.000.000
                                       ─────────────
Jumlah Pendapatan                      Rp 7.000.000

───────────────────────────────────────────────────────────────
POTONGAN
───────────────────────────────────────────────────────────────
Asuransi Kesehatan                     Rp   300.000
Cicilan Pinjaman                       Rp   200.000
                                       ─────────────
Jumlah Potongan                        Rp   500.000

───────────────────────────────────────────────────────────────
RINGKASAN
───────────────────────────────────────────────────────────────
Total Pendapatan                       Rp 7.000.000
Total Potongan                         Rp   500.000
                                       ─────────────
GAJI BERSIH                            Rp 6.500.000

Terbilang: Enam juta lima ratus ribu rupiah

───────────────────────────────────────────────────────────────
Disetujui oleh:                    Diterima oleh:
_____________________              _____________________
(Kepala HRD)                       (Ani Wijayanti)

Tanggal: 31 Desember 2024
═══════════════════════════════════════════════════════════════
```

### Jurnal Akuntansi
```
Tanggal: 31 Desember 2024
Deskripsi: Penggajian - Ani Wijayanti

Akun                          Debit           Kredit
─────────────────────────────────────────────────────
501 Beban Gaji              Rp 7.100.000
101 Kas/Bank BCA                            Rp 7.100.000
─────────────────────────────────────────────────────
Total                       Rp 7.100.000    Rp 7.100.000
```

---

## Kasus 3: Perbandingan BTKL vs BTKTL

### Analisis Komparatif
```
┌─────────────────────────────────────────────────────────────┐
│ PERBANDINGAN GAJI BTKL vs BTKTL                             │
└─────────────────────────────────────────────────────────────┘

BTKL (Budi Santoso - Operator Produksi)
─────────────────────────────────────────
Gaji Dasar (144 jam × Rp 50.000)  : Rp 7.200.000
Tunjangan                         : Rp   500.000
Asuransi                          : Rp   200.000
Bonus                             : Rp   500.000
Potongan                          : Rp  (100.000)
Total Gaji                        : Rp 8.300.000

BTKTL (Ani Wijayanti - Staff Admin)
────────────────────────────────────
Gaji Pokok (tetap)                : Rp 5.000.000
Tunjangan                         : Rp 1.000.000
Asuransi                          : Rp   300.000
Bonus                             : Rp 1.000.000
Potongan                          : Rp  (200.000)
Total Gaji                        : Rp 7.100.000

PERBANDINGAN
────────────────────────────────────
Gaji BTKL lebih tinggi Rp 1.200.000 (17% lebih besar)

ALASAN:
1. BTKL: Gaji dasar dari jam kerja (144 jam × Rp 50.000)
2. BTKTL: Gaji pokok tetap (Rp 5.000.000)
3. Dalam kasus ini, jam kerja BTKL menghasilkan gaji dasar lebih tinggi

FLEKSIBILITAS:
- BTKL: Gaji berfluktuasi tergantung jam kerja
- BTKTL: Gaji stabil setiap bulan
```

---

## Kasus 4: Simulasi Penggajian Massal (Semua Pegawai)

### Ringkasan Penggajian Bulanan
```
═══════════════════════════════════════════════════════════════
              RINGKASAN PENGGAJIAN DESEMBER 2024
═══════════════════════════════════════════════════════════════

PEGAWAI BTKL
─────────────────────────────────────────────────────────────
No  Nama                    Jam Kerja  Gaji Dasar   Total Gaji
─────────────────────────────────────────────────────────────
1   Budi Santoso            144 jam    Rp 7.200.000 Rp 8.300.000
2   Siti Nurhaliza          144 jam    Rp 6.480.000 Rp 7.480.000
3   Ahmad Wijaya            144 jam    Rp 5.040.000 Rp 5.840.000
                                                    ─────────────
Subtotal BTKL                                       Rp 21.620.000

PEGAWAI BTKTL
─────────────────────────────────────────────────────────────
No  Nama                    Gaji Pokok             Total Gaji
─────────────────────────────────────────────────────────────
4   Ani Wijayanti           Rp 5.000.000           Rp 7.100.000
5   Rudi Hermawan           Rp 6.000.000           Rp 8.300.000
6   Eka Putri Lestari       Rp 7.000.000           Rp 9.800.000
                                                    ─────────────
Subtotal BTKTL                                      Rp 25.200.000

═══════════════════════════════════════════════════════════════
TOTAL PENGGAJIAN DESEMBER 2024                      Rp 46.820.000
═══════════════════════════════════════════════════════════════

BREAKDOWN KOMPONEN
─────────────────────────────────────────────────────────────
Gaji Dasar (BTKL)                                   Rp 18.720.000
Gaji Pokok (BTKTL)                                  Rp 18.000.000
Tunjangan                                           Rp 6.200.000
Asuransi                                            Rp 1.850.000
Bonus                                              Rp 4.000.000
Potongan                                            Rp (1.150.000)
─────────────────────────────────────────────────────────────
TOTAL                                               Rp 46.820.000

ANALISIS
─────────────────────────────────────────────────────────────
Rata-rata Gaji per Pegawai                          Rp 7.803.333
Gaji Tertinggi                                      Rp 9.800.000
Gaji Terendah                                       Rp 5.840.000
Selisih                                             Rp 3.960.000

Persentase Bonus                                    8,5% dari total
Persentase Potongan                                 2,5% dari total
```

### Jurnal Penggajian Massal
```
Tanggal: 31 Desember 2024
Deskripsi: Penggajian Bulanan Desember 2024

Akun                          Debit            Kredit
─────────────────────────────────────────────────────
501 Beban Gaji              Rp 46.820.000
101 Kas/Bank BCA                             Rp 46.820.000
─────────────────────────────────────────────────────
Total                       Rp 46.820.000    Rp 46.820.000
```

---

## Kasus 5: Audit & Verifikasi Data

### Checklist Verifikasi
```
✓ Data Master Pegawai
  [✓] Semua 6 pegawai terdaftar
  [✓] Jenis pegawai (BTKL/BTKTL) benar
  [✓] Tarif/Gaji pokok sesuai
  [✓] Tunjangan & asuransi terisi
  [✓] Data bank lengkap

✓ Data Presensi
  [✓] Presensi hanya untuk pegawai BTKL (3 orang)
  [✓] Presensi mencakup semua hari kerja bulan ini
  [✓] Total jam kerja sesuai dengan perhitungan
  [✓] Status presensi valid (Hadir/Sakit/Izin)

✓ Data Penggajian
  [✓] Semua 6 pegawai memiliki record penggajian
  [✓] Total gaji dihitung dengan benar
  [✓] Bonus & potongan terinput
  [✓] Tanggal penggajian sesuai

✓ Jurnal Akuntansi
  [✓] Jurnal dibuat untuk setiap penggajian
  [✓] Debit & kredit seimbang
  [✓] Akun beban gaji (501) bertambah
  [✓] Akun kas/bank (101) berkurang

✓ Saldo COA
  [✓] Kas/Bank berkurang sebesar total penggajian
  [✓] Beban Gaji bertambah sebesar total penggajian
  [✓] Saldo seimbang (Debit = Kredit)
```

### Query Verifikasi (Tinker)
```bash
php artisan tinker

# 1. Cek total pegawai
>>> App\Models\Pegawai::count();
6

# 2. Cek pegawai BTKL
>>> App\Models\Pegawai::where('jenis_pegawai', 'btkl')->count();
3

# 3. Cek pegawai BTKTL
>>> App\Models\Pegawai::where('jenis_pegawai', 'btktl')->count();
3

# 4. Cek total presensi
>>> App\Models\Presensi::count();
440

# 5. Cek total jam kerja Budi Santoso
>>> App\Models\Presensi::where('pegawai_id', 1)->sum('jumlah_jam');
144

# 6. Cek total penggajian
>>> App\Models\Penggajian::count();
6

# 7. Cek total gaji yang dibayarkan
>>> App\Models\Penggajian::sum('total_gaji');
46820000

# 8. Cek gaji tertinggi
>>> App\Models\Penggajian::max('total_gaji');
9800000

# 9. Cek gaji terendah
>>> App\Models\Penggajian::min('total_gaji');
5840000

# 10. Cek rata-rata gaji
>>> App\Models\Penggajian::avg('total_gaji');
7803333.33
```

---

## Kasus 6: Skenario Perubahan Data

### Skenario A: Tambah Bonus untuk Pegawai Tertentu
```
Situasi: Pegawai berprestasi mendapat bonus tambahan

Langkah:
1. Buka Transaksi > Penggajian
2. Klik Edit untuk Budi Santoso
3. Ubah Bonus: Rp 500.000 → Rp 1.000.000
4. Klik Simpan

Hasil:
- Total Gaji: Rp 8.300.000 → Rp 8.800.000
- Jurnal otomatis diupdate
- Saldo COA diupdate
```

### Skenario B: Pegawai Sakit Sebulan Penuh
```
Situasi: Pegawai BTKL sakit dan tidak masuk sebulan penuh

Data Presensi:
- Semua hari: Sakit (0 jam)
- Total Jam Kerja: 0 jam

Perhitungan Gaji:
Gaji Dasar        = 0 jam × Rp 50.000 = Rp 0
Tunjangan         = Rp 500.000 (tetap)
Asuransi          = Rp 200.000 (tetap)
Total Gaji        = Rp 700.000

Catatan: Hanya tunjangan & asuransi yang dibayarkan
```

### Skenario C: Perubahan Tarif Pegawai
```
Situasi: Pegawai BTKL naik pangkat dengan tarif baru

Langkah:
1. Buka Master Data > Pegawai
2. Edit Budi Santoso
3. Ubah Tarif per Jam: Rp 50.000 → Rp 60.000
4. Klik Simpan

Penggajian Berikutnya:
Gaji Dasar        = 144 jam × Rp 60.000 = Rp 8.640.000
(Lebih tinggi dari sebelumnya)

Catatan: Perubahan berlaku untuk penggajian berikutnya
```

---

**Versi**: 1.0  
**Tanggal**: 11 Desember 2024  
**Status**: Ready for Reference
