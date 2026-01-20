# ✅ Testing Checklist - Tambah Biaya Bahan

## 🎯 Tujuan Testing
Memastikan semua fitur **Tambah Biaya Bahan** berfungsi dengan baik sesuai spesifikasi.

---

## 📋 Pre-Testing Checklist

- [ ] Server Laravel berjalan (`php artisan serve`)
- [ ] Database terkoneksi
- [ ] Ada produk dengan `harga_bom = 0`
- [ ] Ada data bahan baku di master data
- [ ] Ada data bahan pendukung di master data
- [ ] Browser sudah hard refresh (Ctrl + F5)

---

## 🧪 Test Cases

### 1. Akses Halaman Create ✅

**Langkah:**
1. Buka `/master-data/biaya-bahan`
2. Cari produk dengan Total Biaya = Rp 0
3. Klik tombol "Tambah" (hijau)

**Expected Result:**
- [ ] Redirect ke halaman create
- [ ] URL: `/master-data/biaya-bahan/create/{id}`
- [ ] Tampil card Informasi Produk
- [ ] Tampil card Bahan Baku (BBB) - warna ungu
- [ ] Tampil card Bahan Pendukung - warna cyan
- [ ] Tampil tombol "Tambah Bahan Baku" (biru)
- [ ] Tampil tombol "Tambah Bahan Pendukung" (cyan)
- [ ] Tabel masih kosong (tidak ada baris data)

---

### 2. Tambah Bahan Baku ✅

#### Test 2.1: Klik Tombol Tambah Bahan Baku
**Langkah:**
1. Klik tombol "Tambah Bahan Baku"

**Expected Result:**
- [ ] Baris baru muncul di tabel Bahan Baku
- [ ] Dropdown bahan baku terisi dengan data
- [ ] Input jumlah kosong dengan placeholder "0"
- [ ] Dropdown satuan terisi dengan data
- [ ] Kolom Harga Satuan menampilkan "-"
- [ ] Kolom Subtotal menampilkan "-"
- [ ] Tombol hapus (merah) muncul

#### Test 2.2: Pilih Bahan Baku
**Langkah:**
1. Pilih bahan dari dropdown (contoh: Ayam Kampung)

**Expected Result:**
- [ ] Harga satuan otomatis muncul di kolom "Harga Satuan"
- [ ] Format: Rp 15.000 (dengan separator ribuan)
- [ ] Satuan otomatis terisi di dropdown satuan
- [ ] Contoh: jika bahan satuannya "Kilogram", dropdown otomatis pilih "Kilogram"

#### Test 2.3: Input Jumlah
**Langkah:**
1. Input jumlah (contoh: 10)

**Expected Result:**
- [ ] Subtotal otomatis terhitung
- [ ] Format: Rp 150.000 (dengan separator ribuan)
- [ ] Total BBB di footer otomatis update
- [ ] Total Biaya Bahan di summary otomatis update

#### Test 2.4: Ubah Satuan (Konversi)
**Langkah:**
1. Ubah satuan dari "Kilogram" ke "Gram"
2. Jumlah tetap 10

**Expected Result:**
- [ ] Subtotal otomatis recalculate dengan konversi
- [ ] Contoh: 10 g × Rp 15.000/kg = Rp 150 (10/1000 × 15000)
- [ ] Total BBB otomatis update
- [ ] Total Biaya Bahan otomatis update

#### Test 2.5: Input Desimal
**Langkah:**
1. Input jumlah dengan desimal (contoh: 2.5)

**Expected Result:**
- [ ] Sistem menerima input desimal
- [ ] Subtotal terhitung dengan benar
- [ ] Contoh: 2.5 kg × Rp 15.000 = Rp 37.500

#### Test 2.6: Tambah Multiple Bahan Baku
**Langkah:**
1. Klik "Tambah Bahan Baku" lagi
2. Pilih bahan berbeda
3. Input jumlah

**Expected Result:**
- [ ] Baris baru muncul
- [ ] Baris pertama tetap ada (tidak hilang)
- [ ] Setiap baris punya event listener sendiri
- [ ] Total BBB = jumlah semua subtotal

---

### 3. Tambah Bahan Pendukung ✅

#### Test 3.1: Klik Tombol Tambah Bahan Pendukung
**Langkah:**
1. Klik tombol "Tambah Bahan Pendukung"

**Expected Result:**
- [ ] Baris baru muncul di tabel Bahan Pendukung
- [ ] Dropdown bahan pendukung terisi dengan data
- [ ] Input jumlah kosong
- [ ] Dropdown satuan terisi
- [ ] Harga Satuan dan Subtotal menampilkan "-"

#### Test 3.2: Pilih Bahan Pendukung
**Langkah:**
1. Pilih bahan (contoh: Garam)

**Expected Result:**
- [ ] Harga satuan otomatis muncul
- [ ] Satuan otomatis terisi
- [ ] Sama seperti bahan baku

#### Test 3.3: Input Jumlah
**Langkah:**
1. Input jumlah (contoh: 7)

**Expected Result:**
- [ ] Subtotal otomatis terhitung
- [ ] Total Bahan Pendukung di footer update
- [ ] Total Biaya Bahan di summary update

#### Test 3.4: Tambah Multiple Bahan Pendukung
**Langkah:**
1. Tambah beberapa bahan pendukung

**Expected Result:**
- [ ] Semua baris berfungsi dengan baik
- [ ] Total Bahan Pendukung = jumlah semua subtotal

---

### 4. Hapus Bahan ✅

#### Test 4.1: Hapus Bahan Baku
**Langkah:**
1. Klik tombol hapus (merah) di baris bahan baku

**Expected Result:**
- [ ] Baris terhapus dari tabel
- [ ] Total BBB otomatis update (berkurang)
- [ ] Total Biaya Bahan otomatis update
- [ ] Baris lain tidak terpengaruh

#### Test 4.2: Hapus Bahan Pendukung
**Langkah:**
1. Klik tombol hapus di baris bahan pendukung

**Expected Result:**
- [ ] Baris terhapus
- [ ] Total Bahan Pendukung update
- [ ] Total Biaya Bahan update

#### Test 4.3: Hapus Semua Bahan
**Langkah:**
1. Hapus semua baris bahan baku dan pendukung

**Expected Result:**
- [ ] Semua baris terhapus
- [ ] Total BBB = Rp 0
- [ ] Total Bahan Pendukung = Rp 0
- [ ] Total Biaya Bahan = Rp 0

---

### 5. Perhitungan Total ✅

#### Test 5.1: Total BBB
**Langkah:**
1. Tambah 2 bahan baku:
   - Ayam Kampung: 10 kg × Rp 15.000 = Rp 150.000
   - Bumbu Balado: 10 g × Rp 300 = Rp 3.000

**Expected Result:**
- [ ] Total BBB di footer = Rp 153.000
- [ ] Format dengan separator ribuan

#### Test 5.2: Total Bahan Pendukung
**Langkah:**
1. Tambah 2 bahan pendukung:
   - Garam: 7 g × Rp 200 = Rp 1.400
   - Minyak Goreng: 30 ml × Rp 30 = Rp 900

**Expected Result:**
- [ ] Total Bahan Pendukung di footer = Rp 2.300

#### Test 5.3: Total Biaya Bahan
**Expected Result:**
- [ ] Total Biaya Bahan = Rp 153.000 + Rp 2.300 = Rp 155.300
- [ ] Tampil di summary card
- [ ] Warna hijau (success)

---

### 6. Konversi Satuan ✅

#### Test 6.1: Konversi Berat (kg ↔ g)
**Test Data:**
- Bahan: Ayam Kampung (Rp 15.000/kg)
- Jumlah: 500
- Satuan: Gram

**Expected Result:**
- [ ] Subtotal = Rp 7.500 (500/1000 × 15.000)
- [ ] Perhitungan benar

#### Test 6.2: Konversi Volume (liter ↔ ml)
**Test Data:**
- Bahan: Minyak Goreng (Rp 30.000/liter)
- Jumlah: 250
- Satuan: ml

**Expected Result:**
- [ ] Subtotal = Rp 7.500 (250/1000 × 30.000)

#### Test 6.3: Satuan Sama (tidak perlu konversi)
**Test Data:**
- Bahan: Ayam Kampung (Rp 15.000/kg)
- Jumlah: 10
- Satuan: Kilogram

**Expected Result:**
- [ ] Subtotal = Rp 150.000 (10 × 15.000)
- [ ] Tidak ada konversi

---

### 7. Simpan Data ✅

#### Test 7.1: Simpan dengan Data Lengkap
**Langkah:**
1. Tambah minimal 1 bahan baku
2. Tambah minimal 1 bahan pendukung
3. Klik tombol "Simpan Biaya Bahan"

**Expected Result:**
- [ ] Redirect ke `/master-data/biaya-bahan`
- [ ] Muncul pesan sukses: "Biaya bahan berhasil ditambahkan untuk produk..."
- [ ] Produk di index sekarang punya Total Biaya > Rp 0
- [ ] Tombol berubah dari "Tambah" menjadi "View, Edit, Delete"

#### Test 7.2: Cek Database
**Langkah:**
1. Cek tabel `bom_details`
2. Cek tabel `bom_job_bahan_pendukungs`
3. Cek tabel `produks`

**Expected Result:**
- [ ] Data bahan baku tersimpan di `bom_details`
- [ ] Data bahan pendukung tersimpan di `bom_job_bahan_pendukungs`
- [ ] `produks.harga_bom` terupdate dengan total biaya bahan
- [ ] Semua data sesuai dengan input

#### Test 7.3: Cek Log
**Langkah:**
1. Buka `storage/logs/laravel.log`

**Expected Result:**
- [ ] Ada log "Biaya Bahan Store Request"
- [ ] Ada log "Created Bahan Baku" untuk setiap bahan
- [ ] Ada log "Created Bahan Pendukung" untuk setiap bahan
- [ ] Ada log "Biaya Bahan Created Successfully"
- [ ] Tidak ada error

---

### 8. Validasi ✅

#### Test 8.1: Simpan Tanpa Bahan
**Langkah:**
1. Jangan tambah bahan apapun
2. Klik "Simpan Biaya Bahan"

**Expected Result:**
- [ ] Sistem tetap simpan (karena nullable)
- [ ] `harga_bom` tetap 0
- [ ] Redirect dengan pesan sukses

#### Test 8.2: Baris Tidak Lengkap
**Langkah:**
1. Tambah bahan baku
2. Pilih bahan tapi tidak input jumlah
3. Klik "Simpan"

**Expected Result:**
- [ ] Validasi error muncul
- [ ] Pesan: "The bahan_baku.0.jumlah field is required"
- [ ] Data tidak tersimpan

#### Test 8.3: Jumlah Negatif
**Langkah:**
1. Input jumlah = -5

**Expected Result:**
- [ ] HTML5 validation mencegah input negatif (min="0")
- [ ] Atau validasi backend menolak

---

### 9. UI/UX ✅

#### Test 9.1: Warna Card
**Expected Result:**
- [ ] Card Informasi Produk: header dark
- [ ] Card Bahan Baku: header gradient ungu, body light purple
- [ ] Card Bahan Pendukung: header gradient cyan, body light cyan
- [ ] Footer BBB: background kuning
- [ ] Footer Bahan Pendukung: background cyan

#### Test 9.2: Responsive
**Langkah:**
1. Resize browser window
2. Test di mobile view (F12 → Toggle device toolbar)

**Expected Result:**
- [ ] Tabel responsive (horizontal scroll jika perlu)
- [ ] Tombol tetap accessible
- [ ] Layout tidak rusak

#### Test 9.3: Icon & Button
**Expected Result:**
- [ ] Icon muncul di semua tombol
- [ ] Tombol punya warna yang sesuai
- [ ] Hover effect berfungsi

---

### 10. Edge Cases ✅

#### Test 10.1: Jumlah Sangat Besar
**Test Data:**
- Jumlah: 999999

**Expected Result:**
- [ ] Sistem tetap hitung dengan benar
- [ ] Tidak ada overflow
- [ ] Format ribuan tetap benar

#### Test 10.2: Jumlah Sangat Kecil
**Test Data:**
- Jumlah: 0.001

**Expected Result:**
- [ ] Sistem terima input desimal kecil
- [ ] Perhitungan tetap akurat

#### Test 10.3: Bahan dengan Harga 0
**Test Data:**
- Bahan dengan harga_satuan = 0

**Expected Result:**
- [ ] Subtotal = 0
- [ ] Tidak error

---

## 📊 Summary Testing

### Hasil Testing:
- **Total Test Cases**: 40+
- **Passed**: ___
- **Failed**: ___
- **Skipped**: ___

### Critical Issues:
1. ___
2. ___
3. ___

### Minor Issues:
1. ___
2. ___

### Recommendations:
1. ___
2. ___

---

## ✅ Sign-off

**Tested by**: _______________
**Date**: _______________
**Status**: [ ] PASS / [ ] FAIL
**Notes**: _______________

---

**Testing Checklist - Tambah Biaya Bahan**
*UMKM COE - Januari 2026*
