# Dokumentasi Sistem Penggajian

## Overview
Sistem penggajian yang terintegrasi dengan data pegawai, presensi, dan laporan keuangan. Sistem ini mendukung dua jenis pegawai: BTKL (Biaya Tenaga Kerja Langsung) dan BTKTL (Biaya Tenaga Kerja Tidak Langsung).

## Alur Perhitungan Gaji

### 1. BTKL (Biaya Tenaga Kerja Langsung)
**Formula:**
```
Total Gaji = (Tarif per Jam × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan
```

**Komponen:**
- **Tarif per Jam**: Diambil dari data pegawai (`tarif_per_jam`)
- **Jam Kerja**: Dihitung otomatis dari data presensi bulan berjalan
- **Asuransi**: Diambil dari data pegawai (`asuransi`)
- **Tunjangan**: Diambil dari data pegawai (`tunjangan`)
- **Bonus**: Input manual (bonus kinerja, lembur, dll)
- **Potongan**: Input manual (keterlambatan, pinjaman, dll)

### 2. BTKTL (Biaya Tenaga Kerja Tidak Langsung)
**Formula:**
```
Total Gaji = Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan
```

**Komponen:**
- **Gaji Pokok**: Diambil dari data pegawai (`gaji_pokok`)
- **Asuransi**: Diambil dari data pegawai (`asuransi`)
- **Tunjangan**: Diambil dari data pegawai (`tunjangan`)
- **Bonus**: Input manual (bonus kinerja, dll)
- **Potongan**: Input manual (keterlambatan, pinjaman, dll)

## Struktur Database

### Tabel Pegawai (pegawais)
```sql
- id
- kode_pegawai
- nama
- jabatan
- jenis_pegawai (enum: 'btkl', 'btktl')
- gaji_pokok (untuk BTKTL)
- tarif_per_jam (untuk BTKL)
- tunjangan
- asuransi
- bank
- nomor_rekening
- nama_rekening
```

### Tabel Penggajian (penggajians)
```sql
- id
- pegawai_id
- tanggal_penggajian
- gaji_pokok
- tarif_per_jam
- tunjangan
- asuransi
- bonus (input manual)
- potongan (input manual)
- total_jam_kerja (dari presensi)
- total_gaji (hasil perhitungan)
```

### Tabel Presensi (presensis)
```sql
- id
- pegawai_id
- tgl_presensi
- jumlah_jam
- keterangan
```

## Alur Proses

### 1. Input Data Pegawai
1. Masuk ke menu **Master Data > Pegawai**
2. Tambah/Edit data pegawai
3. Isi data sesuai jenis pegawai:
   - **BTKL**: Isi `tarif_per_jam`, `tunjangan`, `asuransi`
   - **BTKTL**: Isi `gaji_pokok`, `tunjangan`, `asuransi`

### 2. Input Presensi (untuk BTKL)
1. Masuk ke menu **Master Data > Presensi**
2. Input data kehadiran pegawai BTKL
3. Isi `jumlah_jam` untuk setiap hari kerja
4. Sistem akan menghitung total jam kerja per bulan

### 3. Proses Penggajian
1. Masuk ke menu **Transaksi > Penggajian**
2. Klik **Tambah Penggajian**
3. Pilih pegawai dari dropdown
4. Sistem akan otomatis mengisi:
   - Gaji pokok / Tarif per jam
   - Tunjangan
   - Asuransi
   - Total jam kerja (untuk BTKL, dari presensi)
5. Input manual:
   - **Bonus**: Bonus kinerja, lembur, dll
   - **Potongan**: Keterlambatan, pinjaman, dll
6. Sistem akan menghitung **Total Gaji** secara otomatis
7. Klik **Simpan Penggajian**

### 4. Jurnal Otomatis
Saat penggajian disimpan, sistem akan membuat jurnal entry:
```
Debit: Beban Gaji (501)     Rp xxx.xxx
Credit: Kas (101)           Rp xxx.xxx
```

### 5. Update BOP (Biaya Overhead Pabrik)
Sistem akan otomatis update BOP untuk beban gaji dengan perkiraan:
- **BTKL**: (Tarif × 8 jam × 26 hari) + Asuransi + Tunjangan
- **BTKTL**: Gaji Pokok + Asuransi + Tunjangan

## Laporan Penggajian

### Tampilan Index
Menampilkan daftar penggajian dengan informasi:
- Nama Pegawai
- Jenis Pegawai (BTKL/BTKTL)
- Tanggal Penggajian
- Gaji Pokok / Tarif per Jam
- Jam Kerja (untuk BTKL)
- Tunjangan
- Asuransi
- Bonus
- Potongan
- **Total Gaji**

### Detail Penggajian
Menampilkan rincian lengkap:
- Informasi pegawai
- Komponen gaji
- Perhitungan detail
- Total gaji bersih
- Tombol cetak slip gaji

## Validasi

### 1. Validasi Saldo Kas
Sistem akan mengecek saldo kas sebelum menyimpan penggajian:
```
Jika Saldo Kas < Total Gaji:
  Tampilkan error: "Nominal kas tidak cukup"
```

### 2. Validasi Data Pegawai
- Pegawai harus memiliki data lengkap sesuai jenis
- BTKL harus memiliki `tarif_per_jam`
- BTKTL harus memiliki `gaji_pokok`

### 3. Validasi Presensi (untuk BTKL)
- Sistem akan mengambil data presensi bulan berjalan
- Jika tidak ada data presensi, jam kerja = 0

## API Endpoint

### Get Jam Kerja
```
GET /api/presensi/jam-kerja
Parameters:
  - pegawai_id: ID pegawai
  - month: Bulan (1-12)
  - year: Tahun (YYYY)

Response:
{
  "total_jam": 208
}
```

## Contoh Perhitungan

### Contoh 1: Pegawai BTKL
```
Nama: Budi (Operator Produksi)
Jenis: BTKL
Tarif per Jam: Rp 50.000
Jam Kerja (bulan ini): 208 jam
Tunjangan: Rp 500.000
Asuransi: Rp 200.000
Bonus: Rp 300.000
Potongan: Rp 100.000

Perhitungan:
= (50.000 × 208) + 200.000 + 500.000 + 300.000 - 100.000
= 10.400.000 + 200.000 + 500.000 + 300.000 - 100.000
= Rp 11.300.000
```

### Contoh 2: Pegawai BTKTL
```
Nama: Ani (Staff Admin)
Jenis: BTKTL
Gaji Pokok: Rp 5.000.000
Tunjangan: Rp 1.000.000
Asuransi: Rp 300.000
Bonus: Rp 500.000
Potongan: Rp 200.000

Perhitungan:
= 5.000.000 + 300.000 + 1.000.000 + 500.000 - 200.000
= Rp 6.600.000
```

## Troubleshooting

### 1. Jam Kerja tidak muncul
**Solusi:**
- Pastikan data presensi sudah diinput
- Cek tanggal penggajian sesuai dengan bulan presensi
- Refresh halaman setelah memilih pegawai

### 2. Total Gaji tidak sesuai
**Solusi:**
- Cek jenis pegawai (BTKL/BTKTL)
- Pastikan semua komponen gaji sudah terisi
- Cek formula perhitungan sesuai jenis pegawai

### 3. Error "Kas tidak cukup"
**Solusi:**
- Cek saldo kas di menu COA
- Lakukan transaksi penerimaan kas jika diperlukan
- Atau kurangi nominal penggajian

## Integrasi dengan Modul Lain

### 1. Master Data Pegawai
- Data pegawai digunakan untuk mengisi komponen gaji otomatis
- Update data pegawai akan mempengaruhi penggajian berikutnya

### 2. Presensi
- Data presensi digunakan untuk menghitung jam kerja BTKL
- Total jam kerja dihitung per bulan

### 3. COA (Chart of Accounts)
- Penggajian akan mengurangi saldo kas (101)
- Penggajian akan menambah beban gaji (501)

### 4. BOP (Biaya Overhead Pabrik)
- Sistem akan update BOP untuk beban gaji
- Digunakan untuk perhitungan harga pokok produksi

### 5. Jurnal Umum
- Setiap penggajian akan membuat jurnal entry otomatis
- Jurnal dapat dilihat di menu Laporan > Jurnal Umum

## Fitur Tambahan

### 1. Cetak Slip Gaji
- Klik tombol "Cetak" di detail penggajian
- Slip gaji akan menampilkan rincian lengkap
- Format siap cetak

### 2. Laporan Penggajian
- Total penggajian per bulan
- Breakdown per jenis pegawai
- Export ke Excel/PDF

### 3. History Penggajian
- Riwayat penggajian per pegawai
- Grafik tren gaji
- Perbandingan antar periode

## Catatan Penting

1. **Data Otomatis**: Semua data dari pegawai (gaji pokok, tarif, tunjangan, asuransi) diambil otomatis. Tidak perlu input manual.

2. **Input Manual**: Hanya bonus dan potongan yang perlu diinput manual setiap bulan.

3. **Jam Kerja**: Untuk pegawai BTKL, jam kerja dihitung otomatis dari presensi. Pastikan data presensi sudah lengkap.

4. **Validasi Kas**: Sistem akan mengecek saldo kas sebelum menyimpan. Pastikan saldo kas cukup.

5. **Jurnal Otomatis**: Jurnal akan dibuat otomatis saat penggajian disimpan. Tidak perlu input manual.

6. **Update BOP**: BOP akan diupdate otomatis untuk perhitungan harga pokok produksi.

## Update Log

### Version 1.0 (11 November 2025)
- Implementasi sistem penggajian lengkap
- Support BTKL dan BTKTL
- Integrasi dengan presensi
- Jurnal otomatis
- Update BOP otomatis
- Validasi saldo kas
- Laporan penggajian
- Cetak slip gaji
