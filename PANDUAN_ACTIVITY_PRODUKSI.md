# 📊 Activity Diagram - Pengelolaan Produksi

## 🎯 File yang Tersedia

**ACTIVITY_DIAGRAM_PRODUKSI.drawio** - Activity Diagram lengkap proses produksi dengan:
- ✅ 3 Swimlane (Manager Produksi, Sistem, Laporan & Akuntansi)
- ✅ 15 Activity lengkap
- ✅ Decision point (cek stok bahan baku)
- ✅ Alur lengkap dari BOM sampai produk jadi
- ✅ Integrasi dengan akuntansi (jurnal produksi)
- ✅ Note/keterangan teknis
- ✅ Sesuai dengan kode sistem

---

## 📋 Alur Proses Produksi

### 1. **Persiapan (Manager Produksi)**
   - Buka menu Bill of Materials (BOM)
   - Pilih produk yang akan diproduksi
   - Lihat detail BOM (bahan baku, BTKL, BOP)

### 2. **Validasi Stok (Sistem)**
   - Tampilkan data BOM dan kebutuhan bahan
   - Cek stok bahan baku di database
   - **Decision**: Stok cukup?
     - **Tidak**: Manager harus beli bahan baku dulu
     - **Ya**: Lanjut ke perhitungan biaya

### 3. **Perhitungan & Eksekusi (Sistem)**
   - Hitung total biaya produksi (Bahan + BTKL + BOP)
   - Manager input jumlah yang akan diproduksi
   - Kurangi stok bahan baku sesuai BOM
   - Manager submit proses produksi

### 4. **Pencatatan Akuntansi (Sistem & Laporan)**
   - Catat jurnal produksi:
     - **Debit**: Persediaan Produk Jadi
     - **Kredit**: Bahan Baku, BTKL, BOP
   - Generate laporan biaya produksi
   - Tambah stok produk jadi
   - Update HPP produk di master data

### 5. **Penyelesaian (Manager Produksi)**
   - Terima produk jadi dan cek kualitas
   - Proses selesai

---

## 🎨 Swimlane & Warna

| Swimlane | Warna | Actor | Aktivitas |
|----------|-------|-------|-----------|
| **Manager Produksi** | 🟡 Kuning (#fff2cc) | Pengguna | Buka menu, pilih produk, input jumlah, terima produk |
| **Sistem** | 🔵 Biru (#dae8fc) | Aplikasi | Tampilkan data, cek stok, hitung biaya, kurangi stok, catat jurnal |
| **Laporan & Akuntansi** | 🟢 Hijau (#d5e8d4) | Sistem | Generate laporan, update HPP |

---

## 🚀 Cara Menggunakan

### Import ke Draw.io:

1. **Buka Draw.io:**
   ```
   https://app.diagrams.net/
   ```

2. **Import File:**
   - File → Open from → Device
   - Pilih: `ACTIVITY_DIAGRAM_PRODUKSI.drawio`

3. **Selesai!**
   - Diagram dengan 3 swimlane akan muncul

---

## 📝 Detail Activity

### Manager Produksi (Kuning):

1. **Buka Menu BOM**
   - Route: `/master-data/bom`
   - Controller: `BomController@index`

2. **Pilih Produk**
   - Pilih dari dropdown produk
   - Filter BOM berdasarkan produk

3. **Lihat Detail BOM**
   - Lihat bahan baku yang dibutuhkan
   - Lihat BTKL dan BOP
   - Lihat total HPP

4. **Input Jumlah Produksi**
   - Masukkan jumlah yang akan diproduksi
   - Sistem akan kalikan dengan BOM

5. **Submit Proses**
   - Klik tombol submit
   - Proses produksi dimulai

6. **Terima Produk Jadi**
   - Cek kualitas produk
   - Konfirmasi penerimaan

### Sistem (Biru):

1. **Tampilkan Data BOM**
   - Query dari tabel `boms` dan `bom_details`
   - Tampilkan dengan relasi

2. **Cek Stok Bahan Baku**
   - Query: `SELECT stok FROM bahan_bakus WHERE id IN (...)`
   - Bandingkan dengan kebutuhan

3. **Hitung Total Biaya**
   - Bahan Baku = Σ (harga × jumlah)
   - BTKL = 60% × Bahan Baku
   - BOP = 40% × Bahan Baku
   - HPP = Bahan Baku + BTKL + BOP

4. **Kurangi Stok Bahan**
   - `UPDATE bahan_bakus SET stok = stok - jumlah_digunakan`
   - Sesuai dengan BOM × jumlah produksi

5. **Catat Jurnal Produksi**
   - Debit: Persediaan Produk Jadi (HPP × jumlah)
   - Kredit: Bahan Baku (total bahan)
   - Kredit: BTKL (60% bahan)
   - Kredit: BOP (40% bahan)

6. **Tambah Stok Produk**
   - `UPDATE produks SET stok = stok + jumlah_produksi`

### Laporan & Akuntansi (Hijau):

1. **Generate Laporan**
   - Laporan biaya produksi
   - Detail bahan baku yang digunakan
   - Breakdown BTKL dan BOP

2. **Update HPP Produk**
   - `UPDATE produks SET harga_bom = HPP`
   - HPP akan digunakan untuk harga jual

---

## 🔄 Decision Point

### Stok Bahan Baku Cukup?

**Kondisi:**
```php
if ($stok_tersedia >= $stok_dibutuhkan) {
    // Ya - Lanjut produksi
} else {
    // Tidak - Harus beli dulu
}
```

**Jika Tidak Cukup:**
- Manager harus melakukan pembelian bahan baku
- Setelah pembelian, stok akan bertambah
- Kembali ke proses cek stok

**Jika Cukup:**
- Lanjut ke perhitungan biaya produksi
- Proses produksi dapat dilanjutkan

---

## 💾 Database yang Terlibat

### Tabel yang Digunakan:

1. **boms** - Data BOM
   - produk_id
   - total_biaya (HPP)
   - total_btkl
   - total_bop

2. **bom_details** - Detail bahan baku
   - bom_id
   - bahan_baku_id
   - jumlah
   - satuan
   - total_harga

3. **bahan_bakus** - Stok bahan baku
   - nama_bahan
   - stok (akan dikurangi)
   - harga_satuan

4. **produks** - Produk jadi
   - nama_produk
   - stok (akan ditambah)
   - harga_bom (HPP)

5. **jurnal_umum** - Pencatatan akuntansi
   - tanggal
   - kode_akun
   - debit/kredit
   - keterangan

---

## 📤 Export untuk Skripsi

### Untuk Word/PPT:

```
File → Export as → PNG
Settings:
- Zoom: 100%
- Border: 10px
- Transparent: ✓
→ Export → Save
```

### Untuk PDF:

```
File → Export as → PDF
Settings:
- Page View: Fit to Page
- Include diagram: ✓
→ Export → Save
```

---

## ✏️ Customisasi

### Tambah Activity Baru:

1. Klik swimlane yang sesuai
2. Drag rectangle dari panel kiri
3. Drop di posisi yang diinginkan
4. Double click untuk isi teks
5. Connect dengan arrow

### Ubah Warna Swimlane:

1. Klik header swimlane
2. Panel kanan → Fill
3. Pilih warna baru

### Tambah Note:

1. Drag shape "Note" dari panel kiri
2. Drop di samping activity
3. Isi teks keterangan
4. Connect dengan dashed line

---

## 🎓 Untuk Skripsi

### BAB 3 - Analisis dan Perancangan

**3.4.2 Activity Diagram Pengelolaan Produksi**

Activity Diagram pengelolaan produksi menggambarkan alur lengkap proses produksi dari persiapan hingga produk jadi, melibatkan tiga actor utama: Manager Produksi, Sistem, dan Laporan & Akuntansi.

[Insert Gambar: ACTIVITY_DIAGRAM_PRODUKSI.png]

**Gambar 3.X Activity Diagram Pengelolaan Produksi**

Penjelasan alur:

1. **Persiapan**: Manager produksi membuka menu BOM dan memilih produk yang akan diproduksi
2. **Validasi**: Sistem mengecek ketersediaan stok bahan baku
3. **Perhitungan**: Sistem menghitung total biaya produksi menggunakan metode Process Costing
4. **Eksekusi**: Sistem mengurangi stok bahan baku dan mencatat jurnal produksi
5. **Penyelesaian**: Sistem menambah stok produk jadi dan mengupdate HPP

Proses ini terintegrasi dengan sistem akuntansi untuk pencatatan jurnal produksi secara otomatis.

---

## 🔧 Integrasi dengan Sistem

### File Terkait:

```
app/Http/Controllers/BomController.php
app/Models/Bom.php
app/Models/BomDetail.php
app/Models/BahanBaku.php
app/Models/Produk.php
resources/views/master-data/bom/index.blade.php
resources/views/master-data/bom/show.blade.php
```

### Route:

```php
Route::get('/master-data/bom', [BomController::class, 'index']);
Route::get('/master-data/bom/{id}', [BomController::class, 'show']);
Route::post('/master-data/bom', [BomController::class, 'store']);
```

---

## ✅ Checklist Penggunaan

- [ ] File sudah di-download
- [ ] Draw.io sudah dibuka
- [ ] File berhasil di-import
- [ ] Diagram dengan 3 swimlane muncul
- [ ] Sudah diedit sesuai kebutuhan (judul, logo, dll)
- [ ] Sudah di-export sebagai PNG/PDF
- [ ] Sudah dimasukkan ke dokumen skripsi
- [ ] Caption dan nomor gambar sudah ditambahkan

---

## 💡 Tips

1. **Swimlane memudahkan pemahaman** - Jelas siapa yang melakukan apa
2. **Warna konsisten** - Kuning untuk user, Biru untuk sistem, Hijau untuk laporan
3. **Note penting** - Tambahkan note untuk penjelasan teknis
4. **Decision jelas** - Kondisi Ya/Tidak harus jelas
5. **Alur lengkap** - Dari start sampai end tanpa putus

---

## 🎉 Kesimpulan

Activity Diagram Pengelolaan Produksi ini:
- ✅ Lengkap dengan 3 swimlane
- ✅ Mencakup 15 activity
- ✅ Terintegrasi dengan akuntansi
- ✅ Sesuai dengan kode sistem
- ✅ Siap untuk skripsi

**File sudah siap digunakan!** 🎯
