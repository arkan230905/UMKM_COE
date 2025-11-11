# Quick Guide - Sistem Penggajian

## Langkah Cepat Proses Penggajian

### 1️⃣ Persiapan Data Pegawai
**Menu: Master Data > Pegawai**

#### Untuk Pegawai BTKL (Operator Produksi):
- ✅ Isi **Tarif per Jam**: Rp 50.000
- ✅ Isi **Tunjangan**: Rp 500.000
- ✅ Isi **Asuransi**: Rp 200.000
- ✅ Pilih **Jenis Pegawai**: BTKL

#### Untuk Pegawai BTKTL (Staff Admin/Kantor):
- ✅ Isi **Gaji Pokok**: Rp 5.000.000
- ✅ Isi **Tunjangan**: Rp 1.000.000
- ✅ Isi **Asuransi**: Rp 300.000
- ✅ Pilih **Jenis Pegawai**: BTKTL

---

### 2️⃣ Input Presensi (Khusus BTKL)
**Menu: Master Data > Presensi**

- Pilih pegawai BTKL
- Input jumlah jam kerja setiap hari
- Contoh: 8 jam/hari × 26 hari = 208 jam/bulan

---

### 3️⃣ Proses Penggajian
**Menu: Transaksi > Penggajian > Tambah Penggajian**

#### Langkah-langkah:
1. **Pilih Pegawai** dari dropdown
   - Sistem akan otomatis mengisi semua data dari master pegawai

2. **Pilih Tanggal Penggajian**
   - Sistem akan otomatis menghitung jam kerja bulan tersebut (untuk BTKL)

3. **Data Otomatis Terisi:**
   - ✅ Gaji Pokok / Tarif per Jam
   - ✅ Tunjangan
   - ✅ Asuransi
   - ✅ Jam Kerja (untuk BTKL)

4. **Input Manual:**
   - 📝 **Bonus**: Masukkan bonus jika ada (kinerja, lembur, dll)
   - 📝 **Potongan**: Masukkan potongan jika ada (keterlambatan, pinjaman, dll)

5. **Total Gaji** akan dihitung otomatis

6. Klik **Simpan Penggajian**

---

## Formula Perhitungan

### BTKL (Biaya Tenaga Kerja Langsung)
```
Total Gaji = (Tarif × Jam Kerja) + Asuransi + Tunjangan + Bonus - Potongan
```

**Contoh:**
```
Tarif: Rp 50.000/jam
Jam Kerja: 208 jam
Asuransi: Rp 200.000
Tunjangan: Rp 500.000
Bonus: Rp 300.000
Potongan: Rp 100.000

= (50.000 × 208) + 200.000 + 500.000 + 300.000 - 100.000
= Rp 11.300.000
```

### BTKTL (Biaya Tenaga Kerja Tidak Langsung)
```
Total Gaji = Gaji Pokok + Asuransi + Tunjangan + Bonus - Potongan
```

**Contoh:**
```
Gaji Pokok: Rp 5.000.000
Asuransi: Rp 300.000
Tunjangan: Rp 1.000.000
Bonus: Rp 500.000
Potongan: Rp 200.000

= 5.000.000 + 300.000 + 1.000.000 + 500.000 - 200.000
= Rp 6.600.000
```

---

## Fitur Otomatis

### ✨ Yang Dilakukan Sistem Secara Otomatis:

1. **Mengambil Data Pegawai**
   - Gaji pokok / Tarif per jam
   - Tunjangan
   - Asuransi

2. **Menghitung Jam Kerja** (untuk BTKL)
   - Dari data presensi bulan berjalan

3. **Menghitung Total Gaji**
   - Sesuai formula BTKL atau BTKTL

4. **Membuat Jurnal Entry**
   - Debit: Beban Gaji (501)
   - Credit: Kas (101)

5. **Update BOP**
   - Untuk perhitungan harga pokok produksi

6. **Validasi Saldo Kas**
   - Cek apakah kas cukup untuk bayar gaji

---

## Laporan Penggajian

**Menu: Transaksi > Penggajian**

### Informasi yang Ditampilkan:
- ✅ Nama Pegawai
- ✅ Jenis Pegawai (BTKL/BTKTL)
- ✅ Tanggal Penggajian
- ✅ Komponen Gaji (Gaji Pokok, Tunjangan, Asuransi, Bonus, Potongan)
- ✅ Total Gaji
- ✅ Total Keseluruhan

### Aksi yang Tersedia:
- 👁️ **Detail**: Lihat rincian lengkap
- 🖨️ **Cetak**: Cetak slip gaji
- 🗑️ **Hapus**: Hapus data penggajian

---

## Tips & Trik

### 💡 Tips 1: Update Data Pegawai Terlebih Dahulu
Pastikan data pegawai (gaji pokok, tarif, tunjangan, asuransi) sudah lengkap sebelum proses penggajian.

### 💡 Tips 2: Input Presensi Rutin (untuk BTKL)
Input data presensi setiap hari agar jam kerja akurat saat proses penggajian.

### 💡 Tips 3: Cek Saldo Kas
Pastikan saldo kas cukup sebelum proses penggajian untuk menghindari error.

### 💡 Tips 4: Bonus dan Potongan
Siapkan data bonus dan potongan sebelum input penggajian untuk mempercepat proses.

### 💡 Tips 5: Cetak Slip Gaji
Cetak slip gaji untuk arsip dan dokumentasi pembayaran.

---

## Troubleshooting Cepat

### ❌ Jam Kerja tidak muncul
**Solusi:** Pastikan data presensi sudah diinput untuk bulan tersebut

### ❌ Error "Kas tidak cukup"
**Solusi:** Tambah saldo kas atau kurangi nominal penggajian

### ❌ Total Gaji tidak sesuai
**Solusi:** Cek jenis pegawai dan pastikan semua komponen terisi

### ❌ Data tidak otomatis terisi
**Solusi:** Refresh halaman atau pilih ulang pegawai

---

## Checklist Proses Penggajian

- [ ] Data pegawai sudah lengkap (gaji pokok/tarif, tunjangan, asuransi)
- [ ] Data presensi sudah diinput (untuk BTKL)
- [ ] Saldo kas mencukupi
- [ ] Data bonus dan potongan sudah disiapkan
- [ ] Pilih pegawai dan tanggal penggajian
- [ ] Input bonus dan potongan
- [ ] Cek total gaji
- [ ] Simpan penggajian
- [ ] Cetak slip gaji (opsional)
- [ ] Verifikasi jurnal entry

---

## Kontak Support

Jika ada pertanyaan atau kendala, hubungi:
- 📧 Email: support@umkm.com
- 📱 WhatsApp: 0812-3456-7890
- 🌐 Website: www.umkm.com/support

---

**Terakhir diupdate: 11 November 2025**
