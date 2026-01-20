# Testing Checklist: Sistem Biaya Bahan

## 📋 Checklist Lengkap untuk Testing

Gunakan checklist ini untuk memastikan semua fitur berfungsi dengan baik.

---

## 🧪 Test 1: Hapus Baris di Halaman Edit

### Persiapan:
- [ ] Buka browser
- [ ] Login ke sistem
- [ ] Buka halaman: `http://localhost:8000/master-data/biaya-bahan`
- [ ] Pilih produk yang memiliki data biaya bahan
- [ ] Klik tombol "Edit" (icon pensil)

### Testing:
- [ ] Halaman edit terbuka dengan benar
- [ ] Tabel Bahan Baku menampilkan data yang ada
- [ ] Tabel Bahan Pendukung menampilkan data yang ada
- [ ] Setiap baris memiliki tombol 🗑️ di kolom Aksi

### Aksi:
1. [ ] Klik tombol 🗑️ pada baris pertama Bahan Baku
2. [ ] Baris langsung terhapus (tidak perlu konfirmasi)
3. [ ] Total Bahan Baku otomatis update
4. [ ] Total Biaya Bahan otomatis update
5. [ ] Tidak ada error di Browser Console (F12)

### Verifikasi:
- [ ] Baris yang dihapus tidak muncul lagi
- [ ] Nomor urut (#) otomatis update
- [ ] Total perhitungan benar
- [ ] Tombol "Simpan Perubahan" masih berfungsi

### Undo Test:
- [ ] Refresh halaman (Ctrl+F5)
- [ ] Baris yang dihapus muncul kembali (karena belum disimpan)
- [ ] Klik tombol 🗑️ lagi
- [ ] Klik "Simpan Perubahan"
- [ ] Muncul notifikasi sukses
- [ ] Refresh halaman
- [ ] Baris yang dihapus tidak muncul lagi (sudah tersimpan)

**Status Test 1**: [ ] PASS / [ ] FAIL

---

## 🧪 Test 2: Hapus Semua Data di Halaman Index

### Persiapan:
- [ ] Buka halaman: `http://localhost:8000/master-data/biaya-bahan`
- [ ] Cari produk yang memiliki data biaya bahan (Total Biaya Bahan > Rp 0)
- [ ] Catat nama produk dan harga BOM saat ini

### Testing:
- [ ] Tabel menampilkan data produk dengan benar
- [ ] Kolom "Total Biaya Bahan" menampilkan nilai yang benar
- [ ] Setiap baris memiliki tombol 🗑️ di kolom Aksi

### Aksi:
1. [ ] Klik tombol 🗑️ pada produk yang dipilih
2. [ ] Muncul konfirmasi dengan pesan:
   ```
   PERHATIAN!
   
   Anda akan menghapus SEMUA data biaya bahan untuk produk:
   [Nama Produk]
   
   Ini akan menghapus:
   - Semua Bahan Baku
   - Semua Bahan Pendukung
   - Reset harga BOM menjadi Rp 0
   
   Yakin ingin melanjutkan?
   ```
3. [ ] Klik "OK" untuk konfirmasi
4. [ ] Halaman redirect ke index
5. [ ] Muncul notifikasi sukses: "Semua data biaya bahan untuk produk '[Nama Produk]' berhasil dihapus."

### Verifikasi:
- [ ] Total Biaya Bahan untuk produk tersebut menjadi Rp 0
- [ ] Qty Bahan Baku menjadi 0 item
- [ ] Qty Bahan Pendukung menjadi 0 item
- [ ] Produk masih ada di daftar (tidak terhapus)

### Verifikasi Database:
- [ ] Buka halaman edit produk tersebut
- [ ] Tabel Bahan Baku kosong (tidak ada data)
- [ ] Tabel Bahan Pendukung kosong (tidak ada data)
- [ ] Total Biaya Bahan Saat Ini: Rp 0

### Verifikasi Log:
- [ ] Buka file: `storage/logs/laravel.log`
- [ ] Cari log dengan text: "Deleting biaya bahan for product"
- [ ] Cek log: "Deleted BomDetail"
- [ ] Cek log: "Deleted BomJobBahanPendukung"
- [ ] Cek log: "Deleted BomJobCosting"
- [ ] Cek log: "Reset harga_bom to 0"
- [ ] Tidak ada error log

**Status Test 2**: [ ] PASS / [ ] FAIL

---

## 🧪 Test 3: Auto-Fill Harga Satuan

### Persiapan:
- [ ] Buka halaman edit biaya bahan
- [ ] Klik "Tambah Bahan Baku" untuk menambah baris baru

### Testing:
1. [ ] Dropdown Bahan Baku menampilkan format: "Nama Bahan (Rp Harga/Satuan)"
   - Contoh: "Ayam Kampung (Rp 19.000/Kilogram)"
2. [ ] Pilih salah satu bahan dari dropdown
3. [ ] Dropdown Satuan otomatis terpilih sesuai satuan bahan
4. [ ] Harga muncul di text dropdown (bukan di kolom terpisah)

### Verifikasi:
- [ ] Harga yang ditampilkan sesuai dengan data di master bahan baku
- [ ] Satuan yang terpilih sesuai dengan satuan bahan
- [ ] Format tampilan jelas dan mudah dibaca

### Test dengan Bahan Pendukung:
- [ ] Klik "Tambah Bahan Pendukung"
- [ ] Dropdown menampilkan format yang sama
- [ ] Pilih bahan pendukung
- [ ] Satuan otomatis terpilih
- [ ] Harga sesuai dengan master data

**Status Test 3**: [ ] PASS / [ ] FAIL

---

## 🧪 Test 4: Auto-Calculate Subtotal

### Persiapan:
- [ ] Buka halaman edit biaya bahan
- [ ] Pilih bahan: Ayam Kampung (Rp 19.000/Kilogram)

### Test Tanpa Konversi:
1. [ ] Satuan otomatis terpilih: Kilogram
2. [ ] Input qty: 2
3. [ ] Subtotal otomatis: Rp 38.000 (2 × Rp 19.000)
4. [ ] Total Bahan Baku otomatis update
5. [ ] Total Biaya Bahan otomatis update

### Test Dengan Konversi (kg → g):
1. [ ] Ubah satuan ke: Gram
2. [ ] Qty tetap: 2
3. [ ] Subtotal otomatis: Rp 38 (2g = 0.002kg × Rp 19.000)
4. [ ] Total otomatis update

### Test Dengan Konversi (g → kg):
1. [ ] Ubah satuan ke: Gram
2. [ ] Input qty: 500
3. [ ] Subtotal otomatis: Rp 9.500 (500g = 0.5kg × Rp 19.000)
4. [ ] Ubah satuan ke: Kilogram
5. [ ] Qty tetap: 500
6. [ ] Subtotal otomatis: Rp 9.500.000 (500kg × Rp 19.000)

### Test Konversi Liter ↔ ML:
1. [ ] Pilih bahan dengan satuan Liter (misal: Minyak Goreng)
2. [ ] Input qty: 1, Satuan: Liter
3. [ ] Catat subtotal
4. [ ] Ubah satuan ke: Mililiter
5. [ ] Qty tetap: 1
6. [ ] Subtotal harus berubah (1ml = 0.001 liter)

### Verifikasi:
- [ ] Semua konversi bekerja dengan benar
- [ ] Subtotal selalu dalam format Rupiah
- [ ] Total keseluruhan selalu update otomatis
- [ ] Tidak ada error di console

**Status Test 4**: [ ] PASS / [ ] FAIL

---

## 🧪 Test 5: Simpan Perubahan

### Persiapan:
- [ ] Buka halaman edit biaya bahan
- [ ] Lakukan perubahan (tambah/hapus/edit bahan)

### Testing:
1. [ ] Klik tombol "Simpan Perubahan"
2. [ ] Halaman redirect ke index
3. [ ] Muncul notifikasi: "Biaya bahan berhasil diperbarui."
4. [ ] Data di halaman index sudah update
5. [ ] Harga BOM sudah update

### Verifikasi:
- [ ] Buka halaman edit lagi
- [ ] Data yang disimpan muncul dengan benar
- [ ] Total perhitungan sesuai
- [ ] Tidak ada data yang hilang

**Status Test 5**: [ ] PASS / [ ] FAIL

---

## 🧪 Test 6: Browser Compatibility

### Test di Chrome:
- [ ] Semua fitur berfungsi
- [ ] Tidak ada error di console
- [ ] UI tampil dengan benar

### Test di Firefox:
- [ ] Semua fitur berfungsi
- [ ] Tidak ada error di console
- [ ] UI tampil dengan benar

### Test di Edge:
- [ ] Semua fitur berfungsi
- [ ] Tidak ada error di console
- [ ] UI tampil dengan benar

**Status Test 6**: [ ] PASS / [ ] FAIL

---

## 🧪 Test 7: Performance

### Test dengan Data Banyak:
- [ ] Tambah 10+ bahan baku
- [ ] Tambah 10+ bahan pendukung
- [ ] Semua subtotal otomatis dihitung
- [ ] Total otomatis update
- [ ] Tidak ada lag atau freeze
- [ ] Simpan perubahan berhasil

### Test Delete Banyak Data:
- [ ] Hapus semua data biaya bahan untuk produk dengan banyak bahan
- [ ] Proses delete cepat (< 5 detik)
- [ ] Tidak ada timeout
- [ ] Semua data terhapus

**Status Test 7**: [ ] PASS / [ ] FAIL

---

## 📊 Summary Testing

| Test | Status | Catatan |
|------|--------|---------|
| Test 1: Hapus Baris Edit | [ ] PASS / [ ] FAIL | |
| Test 2: Hapus Semua Index | [ ] PASS / [ ] FAIL | |
| Test 3: Auto-Fill Harga | [ ] PASS / [ ] FAIL | |
| Test 4: Auto-Calculate | [ ] PASS / [ ] FAIL | |
| Test 5: Simpan Perubahan | [ ] PASS / [ ] FAIL | |
| Test 6: Browser Compatibility | [ ] PASS / [ ] FAIL | |
| Test 7: Performance | [ ] PASS / [ ] FAIL | |

---

## ✅ Final Checklist

Sebelum deploy ke production:

- [ ] Semua test PASS
- [ ] Tidak ada error di Laravel log
- [ ] Tidak ada error di Browser console
- [ ] UI tampil dengan baik di semua browser
- [ ] Performance baik dengan data banyak
- [ ] Dokumentasi lengkap
- [ ] Backup database sudah dibuat

---

## 🐛 Jika Ada Test yang FAIL

1. **Catat error yang muncul**:
   - Screenshot error
   - Copy error message dari console/log
   - Catat langkah yang menyebabkan error

2. **Cek log**:
   - Browser Console (F12)
   - Laravel log: `storage/logs/laravel.log`

3. **Coba troubleshooting**:
   - Hard refresh: `Ctrl + F5`
   - Clear cache browser
   - Restart Laravel server

4. **Jika masih error**:
   - Cek file yang diubah
   - Cek apakah ada typo
   - Cek apakah semua perubahan sudah disimpan

---

## 📝 Catatan Testing

Gunakan space di bawah untuk mencatat hasil testing:

```
Tanggal Testing: _______________
Tester: _______________

Test 1 - Hapus Baris Edit:
- Status: _______________
- Catatan: _______________

Test 2 - Hapus Semua Index:
- Status: _______________
- Catatan: _______________

Test 3 - Auto-Fill:
- Status: _______________
- Catatan: _______________

Test 4 - Auto-Calculate:
- Status: _______________
- Catatan: _______________

Test 5 - Simpan:
- Status: _______________
- Catatan: _______________

Test 6 - Browser:
- Status: _______________
- Catatan: _______________

Test 7 - Performance:
- Status: _______________
- Catatan: _______________

Kesimpulan:
_______________________________________________
_______________________________________________
_______________________________________________
```

---

**Selamat Testing!** 🧪
